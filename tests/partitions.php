<?php

declare(strict_types=1);

use Burki24\OpenHomeAlarm\AlarmPartitionRegistry;

require_once dirname(__DIR__) . '/libs/AlarmPartitionRegistry.php';

function assertPartition(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$partitions = AlarmPartitionRegistry::partitions(json_encode([
    ['Enabled' => true, 'ID' => ' Main ', 'Name' => ' Home ', 'Default' => false],
    ['Enabled' => true, 'ID' => 'garage', 'Name' => 'Garage', 'Default' => false],
    ['Enabled' => false, 'ID' => 'shed', 'Name' => '', 'Default' => false]
], JSON_THROW_ON_ERROR));
assertPartition($partitions[0]['ID'] === 'main', 'Partition IDs must be normalized to lowercase.');
assertPartition($partitions[0]['Name'] === 'Home', 'Partition names must be trimmed.');
assertPartition($partitions[2]['Name'] === 'Partition 3', 'Empty partition names need a stable fallback.');
assertPartition(
    AlarmPartitionRegistry::defaultPartition($partitions)['ID'] === 'main',
    'The fixed main partition must be resolved.'
);
assertPartition(
    AlarmPartitionRegistry::assignedPartitionID('', $partitions, 'Sensor partition') === 'main',
    'An empty assignment must resolve to main.'
);
assertPartition(
    AlarmPartitionRegistry::assignedPartitionID(' GARAGE ', $partitions, 'Sensor partition') === 'garage',
    'Assignments must resolve enabled partition IDs case-insensitively.'
);
assertPartition(
    AlarmPartitionRegistry::assignedPartitionIDs(['garage', 'main', 'garage'], $partitions, 'Sensor partition')
        === ['garage', 'main'],
    'Multiple sensor assignments must resolve enabled partitions and remove duplicates.'
);
assertPartition(
    AlarmPartitionRegistry::assignedPartitionIDs([], $partitions, 'Sensor partition') === ['main'],
    'An empty multi-area assignment must resolve to main.'
);
foreach (['shed', 'unknown'] as $invalidAssignment) {
    try {
        AlarmPartitionRegistry::assignedPartitionID($invalidAssignment, $partitions, 'Sensor partition');
        throw new RuntimeException('Invalid partition assignments must be rejected.');
    } catch (UnexpectedValueException) {
    }
}

foreach ([
    '[]',
    '[{"Enabled":true,"ID":"invalid id","Name":"Home","Default":true}]',
    '[{"Enabled":true,"ID":"home","Name":"Home","Default":true},{"Enabled":true,"ID":"HOME","Name":"Other","Default":false}]',
    '[{"Enabled":true,"ID":"home","Name":"Home","Default":false}]',
    '[{"Enabled":true,"ID":"main","Name":"Main","Default":false},{"Enabled":true,"ID":"MAIN","Name":"Other","Default":true}]',
    '[{"Enabled":false,"ID":"main","Name":"Main","Default":true}]'
] as $invalidConfiguration) {
    try {
        AlarmPartitionRegistry::partitions($invalidConfiguration);
        throw new RuntimeException('Invalid partition configuration must be rejected.');
    } catch (UnexpectedValueException) {
    }
}

$form = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$partitionList = null;
foreach ($form['elements'] ?? [] as $element) {
    foreach ($element['items'] ?? [] as $item) {
        if (($item['name'] ?? null) === 'Partitions') {
            $partitionList = $item;
        }
    }
}
assertPartition(
    is_array($partitionList) && ($partitionList['type'] ?? null) === 'List',
    'Alarm partitions must be configurable as a list.'
);
assertPartition(
    array_column($partitionList['columns'] ?? [], 'name') === ['Enabled', 'ID', 'Name'],
    'The partition form must expose stable identity without a selectable default area.'
);
assertPartition(
    ($partitionList['add'] ?? false) === true
        && ($partitionList['delete'] ?? false) === true
        && array_column($partitionList['form'] ?? [], 'name') === ['Enabled', 'ID', 'Name'],
    'Alarm partitions must provide explicit add, edit and delete controls.'
);
$formJSON = json_encode($form, JSON_THROW_ON_ERROR);
assertPartition(
    str_contains($formJSON, 'The required area main is the complete alarm system')
        && str_contains($formJSON, 'The area main is the fixed complete alarm system')
        && str_contains($formJSON, 'garage or area_1'),
    'The partition form must explain activation semantics, the fixed main area and valid ID examples.'
);
assertPartition(
    ($form['status'][0]['code'] ?? null) === 201
        && ($form['status'][0]['icon'] ?? null) === 'error'
        && str_contains($form['status'][0]['caption'] ?? '', 'The main area must exist and be enabled'),
    'Invalid partition configurations must have a user-facing Symcon status instead of producing an uncaught exception.'
);

$mainPartitions = AlarmPartitionRegistry::partitions(json_encode([
    ['Enabled' => true, 'ID' => 'main', 'Name' => 'Main area', 'Default' => false],
    ['Enabled' => true, 'ID' => 'garage', 'Name' => 'Garage', 'Default' => true]
], JSON_THROW_ON_ERROR));
assertPartition(
    AlarmPartitionRegistry::defaultPartition($mainPartitions)['ID'] === 'main'
        && $mainPartitions[0]['Default'] === true
        && $mainPartitions[1]['Default'] === false,
    'The fixed main area must override legacy default selections.'
);
try {
    AlarmPartitionRegistry::partitions(json_encode([
        ['Enabled' => false, 'ID' => 'main', 'Name' => 'Main area', 'Default' => true]
    ], JSON_THROW_ON_ERROR));
    throw new RuntimeException('A disabled main area must be rejected.');
} catch (UnexpectedValueException) {
}

$moduleReadme = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/README.md');
$rootReadme = (string) file_get_contents(dirname(__DIR__) . '/README.md');
foreach ([$moduleReadme, $rootReadme] as $readme) {
    assertPartition(
        str_contains($readme, "OHA_ArmPartition(12345, 'garage', 'away')")
            && str_contains($readme, "OHA_DisarmPartition(12345, 'garage')")
            && str_contains($readme, "['Partitions']['garage']")
            && str_contains($readme, "['State']['Name']")
            && str_contains($readme, "'exit_delay', 'armed', 'entry_delay', 'alarm'")
            && str_contains($readme, '1 bis 32')
            && str_contains($readme, '`garage`')
            && str_contains($readme, 'Gesamtanlage'),
        'Both READMEs must document partition IDs, the fixed main area, independent operation and partition status queries.'
    );
}

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertPartition(
    str_contains($moduleSource, 'private const CONTROL_API_VERSION = 2;'),
    'Partition-aware control state must use API version 2.'
);
assertPartition(
    str_contains($moduleSource, 'public function GetPartitions(): string'),
    'The public partition metadata method is missing.'
);
assertPartition(
    str_contains($moduleSource, 'private function GuardSecurityConfigurationChanges(): void')
        && str_contains($moduleSource, 'private function LockSecurityConfigurationFields(array &$elements): void'),
    'Active alarm partitions must protect security configuration in the form and during ApplyChanges().'
);
assertPartition(
    str_contains($moduleSource, "!in_array(\$type, ['Label', 'ExpansionPanel', 'RowLayout'], true)"),
    'The armed-state configuration lock must disable every editable alarm form field.'
);

fwrite(STDOUT, "OpenHomeAlarm partition registry checks passed.\n");
