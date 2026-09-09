<?php

declare(strict_types=1);

// Diese drei IDs an die eigene Symcon-Installation anpassen.
const OHA_INSTANCE_ID = 12345;
const TILE_VISUALIZATION_ID = 23456;
const TARGET_OBJECT_ID = OHA_INSTANCE_ID;

try {
    $state = json_decode(
        OHA_GetControlState(OHA_INSTANCE_ID),
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (Throwable $exception) {
    IPS_LogMessage('OpenHomeAlarm Push', 'Alarmstatus konnte nicht gelesen werden: ' . $exception->getMessage());

    return;
}

$latestAlarmArea = null;
$latestAlarmTimestamp = -1;
foreach (($state['Partitions'] ?? []) as $partition) {
    if (!is_array($partition)
        || ($partition['State']['Name'] ?? '') !== 'alarm'
        || ($partition['Alarm']['OutputActive'] ?? false) !== true) {
        continue;
    }

    $alarmTime = DateTimeImmutable::createFromFormat(
        'd.m.Y H:i:s',
        (string) ($partition['Alarm']['LastTime'] ?? '')
    );
    $alarmTimestamp = $alarmTime === false ? 0 : $alarmTime->getTimestamp();
    if ($latestAlarmArea === null || $alarmTimestamp >= $latestAlarmTimestamp) {
        $latestAlarmArea = $partition;
        $latestAlarmTimestamp = $alarmTimestamp;
    }
}

$areaName = trim((string) ($latestAlarmArea['Name'] ?? 'Alarmbereich'));
$sensorName = trim((string) (
    $latestAlarmArea['Alarm']['LastSource']
    ?? $state['Alarm']['LastSource']
    ?? 'Unbekannter Sensor'
));
$areaName = $areaName !== '' ? $areaName : 'Alarmbereich';
$sensorName = $sensorName !== '' ? $sensorName : 'Unbekannter Sensor';

$shorten = static function (string $text, int $maximumLength): string {
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $maximumLength, '…', 'UTF-8');
    }

    return strlen($text) <= $maximumLength
        ? $text
        : substr($text, 0, max(0, $maximumLength - 3)) . '...';
};

$title = $shorten(sprintf('Einbruchalarm %s!', $areaName), 32);
$message = $shorten(sprintf('Der Sensor %s hat ausgelöst.', $sensorName), 256);
$notificationID = VISU_PostNotificationEx(
    TILE_VISUALIZATION_ID,
    $title,
    $message,
    'Alert',
    'siren',
    TARGET_OBJECT_ID
);

if ($notificationID === false) {
    IPS_LogMessage('OpenHomeAlarm Push', 'Die Push-Nachricht konnte nicht versendet werden.');
}
