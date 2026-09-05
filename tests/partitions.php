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
    ['Enabled' => true, 'ID' => ' House ', 'Name' => ' Home ', 'Default' => true],
    ['Enabled' => true, 'ID' => 'garage', 'Name' => 'Garage', 'Default' => false],
    ['Enabled' => false, 'ID' => 'shed', 'Name' => '', 'Default' => false]
], JSON_THROW_ON_ERROR));
assertPartition($partitions[0]['ID'] === 'house', 'Partition IDs must be normalized to lowercase.');
assertPartition($partitions[0]['Name'] === 'Home', 'Partition names must be trimmed.');
assertPartition($partitions[2]['Name'] === 'Partition 3', 'Empty partition names need a stable fallback.');
assertPartition(
    AlarmPartitionRegistry::defaultPartition($partitions)['ID'] === 'house',
    'The configured default partition must be resolved.'
);
assertPartition(
    AlarmPartitionRegistry::assignedPartitionID('', $partitions, 'Sensor partition') === 'house',
    'An empty assignment must resolve to the default partition.'
);
assertPartition(
    AlarmPartitionRegistry::assignedPartitionID(' GARAGE ', $partitions, 'Sensor partition') === 'garage',
    'Assignments must resolve enabled partition IDs case-insensitively.'
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
    '[{"Enabled":true,"ID":"home","Name":"Home","Default":true},{"Enabled":true,"ID":"garage","Name":"Garage","Default":true}]',
    '[{"Enabled":false,"ID":"home","Name":"Home","Default":true}]'
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
    array_column($partitionList['columns'] ?? [], 'name') === ['Enabled', 'ID', 'Name', 'Default'],
    'The partition form must expose stable identity and default selection fields.'
);
$formJSON = json_encode($form, JSON_THROW_ON_ERROR);
assertPartition(
    str_contains($formJSON, 'Enabled makes a partition available but does not arm it.')
        && str_contains($formJSON, 'Partition IDs must start with a lowercase letter')
        && str_contains($formJSON, 'main, garage or area_1'),
    'The partition form must explain activation semantics, the ID format and valid examples.'
);

$moduleReadme = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/README.md');
$rootReadme = (string) file_get_contents(dirname(__DIR__) . '/README.md');
foreach ([$moduleReadme, $rootReadme] as $readme) {
    assertPartition(
        str_contains($readme, "OHA_ArmPartition(12345, 'garage', 'away')")
            && str_contains($readme, "OHA_DisarmPartition(12345, 'garage')")
            && str_contains($readme, '1 bis 32 Kleinbuchstaben')
            && str_contains($readme, 'Standardbereich'),
        'Both READMEs must document partition IDs, the default partition and independent operation.'
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
    str_contains($moduleSource, '[self::PROPERTY_PARTITIONS, self::PROPERTY_SENSORS, self::PROPERTY_FAULT_INPUTS]'),
    'The armed-state configuration lock must cover partitions, sensors and fault inputs.'
);

fwrite(STDOUT, "OpenHomeAlarm partition registry checks passed.\n");
