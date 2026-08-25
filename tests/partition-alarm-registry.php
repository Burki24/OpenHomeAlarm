<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/AlarmPartitionAlarmRegistry.php';

use Burki24\OpenHomeAlarm\AlarmPartitionAlarmRegistry;

function assertPartitionAlarm(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$partitions = [
    ['Enabled' => true, 'ID' => 'house', 'Name' => 'House', 'Default' => true],
    ['Enabled' => true, 'ID' => 'garage', 'Name' => 'Garage', 'Default' => false],
    ['Enabled' => false, 'ID' => 'shed', 'Name' => 'Shed', 'Default' => false]
];
$states = AlarmPartitionAlarmRegistry::synchronize($partitions, []);
assertPartitionAlarm(array_keys($states) === ['house', 'garage'], 'Only enabled partitions need alarm state.');

$states['house'] = AlarmPartitionAlarmRegistry::alarm($states['house'], 'Front door', 100);
$states['garage'] = AlarmPartitionAlarmRegistry::alarm($states['garage'], 'Garage door', 110);
$aggregate = AlarmPartitionAlarmRegistry::aggregate($states);
assertPartitionAlarm($aggregate['ActivePartitionIDs'] === ['house', 'garage'], 'Every active alarm output must be retained.');
assertPartitionAlarm($aggregate['MemoryPartitionIDs'] === ['house', 'garage'], 'Every partition alarm memory must be retained.');
assertPartitionAlarm(
    $aggregate['LastPartitionID'] === 'garage' && $aggregate['LastSource'] === 'Garage door',
    'The newest alarm source must define the instance summary.'
);

$states['house'] = AlarmPartitionAlarmRegistry::resetOutput($states['house']);
$aggregate = AlarmPartitionAlarmRegistry::aggregate($states);
assertPartitionAlarm(
    $aggregate['OutputActive'] && $aggregate['ActivePartitionIDs'] === ['garage'],
    'Resetting one output must not reset another partition output.'
);
assertPartitionAlarm(
    AlarmPartitionAlarmRegistry::clearMemory($states['garage'])['MemoryActive'],
    'Alarm memory must remain protected while its output is active.'
);
$states['house'] = AlarmPartitionAlarmRegistry::clearMemory($states['house']);
assertPartitionAlarm(!$states['house']['MemoryActive'] && $states['garage']['MemoryActive'], 'Memory clearing must be partition-local.');

fwrite(STDOUT, "OpenHomeAlarm partition alarm registry checks passed.\n");
