<?php

declare(strict_types=1);

/** @var array<int,mixed> */
$sentNotification = [];

function OHA_GetControlState(int $instanceID): string
{
    if ($instanceID !== 12345) {
        throw new RuntimeException('Unexpected OpenHomeAlarm instance ID.');
    }

    return json_encode([
        'Alarm'      => ['LastSource' => 'Globaler Sensor'],
        'Partitions' => [
            'main' => [
                'Name'  => 'Main area',
                'State' => ['Name' => 'alarm'],
                'Alarm' => [
                    'OutputActive' => true,
                    'LastSource'   => 'Haustür',
                    'LastTime'     => '09.09.2026 08:29:50'
                ]
            ],
            'cellar' => [
                'Name'  => 'Keller',
                'State' => ['Name' => 'alarm'],
                'Alarm' => [
                    'OutputActive' => true,
                    'LastSource'   => 'Fensterkontakt HAR',
                    'LastTime'     => '09.09.2026 08:29:51'
                ]
            ]
        ]
    ], JSON_THROW_ON_ERROR);
}

function VISU_PostNotificationEx(
    int $instanceID,
    string $title,
    string $text,
    string $icon,
    string $sound,
    int $targetID
): int|false {
    global $sentNotification;

    $sentNotification = func_get_args();

    return 1;
}

function IPS_LogMessage(string $sender, string $message): void
{
    throw new RuntimeException(sprintf('%s: %s', $sender, $message));
}

require dirname(__DIR__) . '/docs/examples/push-alarm-notification.php';

if ($sentNotification !== [
    23456,
    'Einbruchalarm Keller!',
    'Der Sensor Fensterkontakt HAR hat ausgelöst.',
    'Alert',
    'siren',
    12345
]) {
    throw new RuntimeException('The documented alarm push example produced an unexpected notification.');
}

fwrite(STDOUT, "OpenHomeAlarm push-notification example checks passed.\n");
