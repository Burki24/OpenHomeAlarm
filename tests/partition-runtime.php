<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/AlarmStateMachine.php';
require_once __DIR__ . '/../libs/AlarmPartitionRuntime.php';

use Burki24\OpenHomeAlarm\AlarmPartitionRuntime;
use Burki24\OpenHomeAlarm\AlarmStateMachine;

function assertPartitionRuntime(bool $condition, string $message): void
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
$states = AlarmPartitionRuntime::synchronize($partitions, []);
assertPartitionRuntime(array_keys($states) === ['house', 'garage'], 'Only enabled partitions need runtime state.');
$states['house'] = AlarmPartitionRuntime::arm($states['house'], AlarmStateMachine::MODE_HOME, 100, 10);
$states['garage'] = AlarmPartitionRuntime::arm($states['garage'], AlarmStateMachine::MODE_AWAY, 100, 0);
assertPartitionRuntime($states['house']['State'] === AlarmStateMachine::STATE_EXIT_DELAY, 'House needs its own exit delay.');
assertPartitionRuntime($states['garage']['State'] === AlarmStateMachine::STATE_ARMED, 'Garage must arm independently.');
$states['house'] = AlarmPartitionRuntime::advance($states['house'], 110);
assertPartitionRuntime($states['house']['State'] === AlarmStateMachine::STATE_ARMED, 'Expired exit delay must arm its partition.');
$states['garage'] = AlarmPartitionRuntime::startEntryDelay($states['garage'], 120, 5, 'Garage door', 42);
assertPartitionRuntime($states['garage']['Deadline'] === 125 && $states['house']['State'] === AlarmStateMachine::STATE_ARMED, 'Entry delay must not change another partition.');
$states['garage'] = AlarmPartitionRuntime::advance($states['garage'], 125);
assertPartitionRuntime($states['garage']['State'] === AlarmStateMachine::STATE_ALARM, 'Expired entry delay must alarm its partition.');
$states['house'] = AlarmPartitionRuntime::disarm($states['house']);
assertPartitionRuntime($states['house']['State'] === AlarmStateMachine::STATE_DISARMED && $states['garage']['State'] === AlarmStateMachine::STATE_ALARM, 'Disarming must be partition-local.');

fwrite(STDOUT, "OpenHomeAlarm partition runtime checks passed.\n");
