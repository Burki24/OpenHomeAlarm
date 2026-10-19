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
                'Name'  => 'Überwachungsbereich mit einem sehr langen Namen',
                'State' => ['Name' => 'alarm'],
                'Alarm' => [
                    'OutputActive' => true,
                    'LastSource'   => str_repeat('Fenster Südflügel ', 20),
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

    if (strlen($title) > 32 || strlen($text) > 256) {
        return false;
    }

    $sentNotification = func_get_args();

    return 1;
}

function IPS_LogMessage(string $sender, string $message): void
{
    throw new RuntimeException(sprintf('%s: %s', $sender, $message));
}

require dirname(__DIR__) . '/docs/examples/push-alarm-notification.php';

if (($sentNotification[0] ?? null) !== 23456
    || strlen((string) ($sentNotification[1] ?? '')) > 32
    || strlen((string) ($sentNotification[2] ?? '')) > 256
    || preg_match('//u', (string) ($sentNotification[1] ?? '')) !== 1
    || preg_match('//u', (string) ($sentNotification[2] ?? '')) !== 1
    || !str_ends_with((string) ($sentNotification[1] ?? ''), '…')
    || !str_ends_with((string) ($sentNotification[2] ?? ''), '…')
    || ($sentNotification[3] ?? null) !== 'Alert'
    || ($sentNotification[4] ?? null) !== 'siren'
    || ($sentNotification[5] ?? null) !== 12345) {
    throw new RuntimeException('The documented alarm push example must produce valid UTF-8 within Symcon byte limits.');
}

fwrite(STDOUT, "OpenHomeAlarm push-notification example checks passed.\n");
