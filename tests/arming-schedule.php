<?php

declare(strict_types=1);

use Burki24\OpenHomeAlarm\AlarmArmingSchedule;

require_once dirname(__DIR__) . '/libs/AlarmArmingSchedule.php';

function assertArmingSchedule(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$schedules = AlarmArmingSchedule::schedules(json_encode([
    [
        'Enabled' => true, 'Name' => ' Workday away ',
        'Monday'  => true, 'Tuesday' => true, 'Wednesday' => true, 'Thursday' => true,
        'Friday'  => true, 'Saturday' => false, 'Sunday' => false,
        'Time'    => ' 08:15 ', 'Mode' => 'AWAY'
    ],
    [
        'Enabled' => false, 'Name' => '',
        'Monday'  => true, 'Tuesday' => false, 'Wednesday' => false, 'Thursday' => false,
        'Friday'  => false, 'Saturday' => false, 'Sunday' => false,
        'Time'    => '22:00', 'Mode' => 'night'
    ]
], JSON_THROW_ON_ERROR));

assertArmingSchedule($schedules[0]['Name'] === 'Workday away', 'Schedule names must be trimmed.');
assertArmingSchedule($schedules[0]['Mode'] === 'away', 'Schedule modes must be normalized.');
assertArmingSchedule($schedules[1]['Name'] === 'Schedule 2', 'Empty schedule names need a stable fallback.');
assertArmingSchedule(count(AlarmArmingSchedule::due($schedules, 1, '08:15')) === 1, 'Configured schedules must become due.');
assertArmingSchedule(AlarmArmingSchedule::due($schedules, 6, '08:15') === [], 'Unconfigured weekdays must not become due.');
assertArmingSchedule(AlarmArmingSchedule::due($schedules, 1, '22:00') === [], 'Disabled schedules must never become due.');

$firstKey = AlarmArmingSchedule::executionKey($schedules[0], '2026-08-24 08:15');
$secondKey = AlarmArmingSchedule::executionKey($schedules[0], '2026-08-24 08:15');
$nextMinuteKey = AlarmArmingSchedule::executionKey($schedules[0], '2026-08-24 08:16');
assertArmingSchedule($firstKey === $secondKey, 'Execution keys must be stable for the same local minute.');
assertArmingSchedule($firstKey !== $nextMinuteKey, 'Execution keys must change for another minute.');

foreach ([
    '[{"Enabled":true,"Monday":true,"Time":"24:00","Mode":"away"}]',
    '[{"Enabled":true,"Monday":true,"Time":"08:00","Mode":"invalid"}]',
    '[{"Enabled":true,"Time":"08:00","Mode":"away"}]'
] as $invalidConfiguration) {
    try {
        AlarmArmingSchedule::schedules($invalidConfiguration);
        throw new RuntimeException('Invalid schedule configuration must be rejected.');
    } catch (UnexpectedValueException) {
    }
}

$form = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$scheduleList = null;
foreach ($form['elements'] ?? [] as $element) {
    foreach ($element['items'] ?? [] as $item) {
        if (($item['name'] ?? null) === 'AutomaticArmingSchedules') {
            $scheduleList = $item;
        }
    }
}
assertArmingSchedule(
    is_array($scheduleList) && ($scheduleList['type'] ?? null) === 'List',
    'Automatic arming schedules must be configurable as a list.'
);
$columnNames = array_column($scheduleList['columns'] ?? [], 'name');
foreach (['Enabled', 'Name', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Time', 'Mode'] as $columnName) {
    assertArmingSchedule(in_array($columnName, $columnNames, true), sprintf('Schedule column %s is missing.', $columnName));
}

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertArmingSchedule(
    str_contains($moduleSource, "'OHA_CheckAutomaticArming(\$_IPS[\\'TARGET\\']);'"),
    'The automatic arming timer must use the generated public Symcon wrapper.'
);
assertArmingSchedule(
    str_contains($moduleSource, 'public function CheckAutomaticArming(): void'),
    'The automatic arming timer callback must remain public.'
);

fwrite(STDOUT, "OpenHomeAlarm automatic arming schedule checks passed.\n");
