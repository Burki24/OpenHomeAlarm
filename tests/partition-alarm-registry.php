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
$restored = AlarmPartitionAlarmRegistry::synchronize($partitions, [
    'garage' => [
        'OutputActive'   => true,
        'OutputDeadline' => 150,
        'MemoryActive'   => true,
        'LastSource'     => ' Garage door ',
        'LastTimestamp'  => 110
    ],
    'shed' => [
        'OutputActive'  => true,
        'MemoryActive'  => true,
        'LastSource'    => 'Shed',
        'LastTimestamp' => 90
    ]
]);
assertPartitionAlarm(
    $restored['garage']['OutputActive']
        && $restored['garage']['OutputDeadline'] === 150
        && $restored['garage']['LastSource'] === 'Garage door'
        && !array_key_exists('shed', $restored),
    'Enabled partition alarm state must survive synchronization while disabled partitions are removed.'
);

$states['house'] = AlarmPartitionAlarmRegistry::alarm($states['house'], 'Front door', 100, 30);
$states['garage'] = AlarmPartitionAlarmRegistry::alarm($states['garage'], 'Garage door', 110, 10);
$aggregate = AlarmPartitionAlarmRegistry::aggregate($states);
assertPartitionAlarm($aggregate['ActivePartitionIDs'] === ['house', 'garage'], 'Every active alarm output must be retained.');
assertPartitionAlarm($aggregate['MemoryPartitionIDs'] === ['house', 'garage'], 'Every partition alarm memory must be retained.');
assertPartitionAlarm(
    $aggregate['LastPartitionID'] === 'garage' && $aggregate['LastSource'] === 'Garage door',
    'The newest alarm source must define the instance summary.'
);
assertPartitionAlarm(
    AlarmPartitionAlarmRegistry::expiredOutputPartitionIDs($states, 120) === ['garage'],
    'Only alarm outputs whose individual deadline elapsed may be reset.'
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
