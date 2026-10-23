<?php

declare(strict_types=1);

/** @var array<int,mixed> */
$cameraExampleValues = [
    34567 => 0,
    45678 => 0,
    56789 => false
];
/** @var array<int,mixed> */
$cameraNotification = [];
/** @var list<array{0:string,1:string}> */
$cameraLogs = [];
/** @var array<int,int> */
$cameraScriptTimers = [];
/** @var array<string,bool> */
$cameraSemaphores = [];

function IPS_VariableExists(int $variableID): bool
{
    return in_array($variableID, [34567, 45678, 56789, 67890, 78901], true);
}

function IPS_GetName(int $objectID): string
{
    return match ($objectID) {
        67890   => str_repeat('Kamera Südflügel mit Umlauten äöü ', 20),
        78901   => 'Kamera Haustür',
        default => 'Hilfsvariable'
    };
}

function VISU_PostNotificationEx(
    int $instanceID,
    string $title,
    string $text,
    string $icon,
    string $sound,
    int $targetID
): int|false {
    global $cameraNotification;

    if (strlen($title) > 32 || strlen($text) > 256) {
        return false;
    }

    $cameraNotification = func_get_args();

    return 1;
}

function IPS_LogMessage(string $sender, string $message): void
{
    global $cameraLogs;
    $cameraLogs[] = [$sender, $message];
}

function IPS_SemaphoreEnter(string $name, int $timeout): bool
{
    global $cameraSemaphores;
    if (($cameraSemaphores[$name] ?? false) === true) {
        return false;
    }
    $cameraSemaphores[$name] = true;

    return true;
}

function IPS_SemaphoreLeave(string $name): bool
{
    global $cameraSemaphores;
    $cameraSemaphores[$name] = false;

    return true;
}

function GetValueInteger(int $variableID): int
{
    global $cameraExampleValues;

    return (int) ($cameraExampleValues[$variableID] ?? 0);
}

function SetValueInteger(int $variableID, int $value): bool
{
    global $cameraExampleValues;
    $cameraExampleValues[$variableID] = $value;

    return true;
}

function SetValueBoolean(int $variableID, bool $value): bool
{
    global $cameraExampleValues;
    $cameraExampleValues[$variableID] = $value;

    return true;
}

function IPS_SetScriptTimer(int $scriptID, int $seconds): bool
{
    global $cameraScriptTimers;
    $cameraScriptTimers[$scriptID] = $seconds;

    return true;
}

/** @param array<string,mixed> $context */
function runCameraExample(string $file, array $context): void
{
    $_IPS = $context;
    require dirname(__DIR__) . '/docs/examples/' . $file;
}

function assertCameraExample(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

runCameraExample('camera-prealarm-notification.php', [
    'SENDER'   => 'Variable',
    'VARIABLE' => 67890,
    'VALUE'    => true
]);
assertCameraExample(
    ($cameraNotification[0] ?? null) === 12345
    && ($cameraNotification[1] ?? null) === 'Kamera-Voralarm'
    && str_contains((string) ($cameraNotification[2] ?? ''), 'Kamera Südflügel')
    && strlen((string) ($cameraNotification[2] ?? '')) <= 256
    && preg_match('//u', (string) ($cameraNotification[2] ?? '')) === 1
    && ($cameraNotification[3] ?? null) === 'Alert'
    && ($cameraNotification[4] ?? null) === 'bell'
    && ($cameraNotification[5] ?? null) === 23456,
    'The pre-alarm example must send one valid native Symcon notification.'
);

$cameraNotification = [];
runCameraExample('camera-prealarm-notification.php', [
    'SENDER'   => 'Variable',
    'VARIABLE' => 67890,
    'VALUE'    => false
]);
assertCameraExample($cameraNotification === [], 'The falling camera edge must not send a pre-alarm notification.');

runCameraExample('camera-confirmed-detection.php', [
    'SENDER'   => 'Variable',
    'SELF'     => 89012,
    'VARIABLE' => 67890,
    'VALUE'    => true
]);
assertCameraExample(
    ($cameraExampleValues[34567] ?? 0) > 0
    && ($cameraExampleValues[45678] ?? 0) === 67890
    && ($cameraExampleValues[56789] ?? true) === false,
    'The first camera edge must start the correlation window without confirming an alarm.'
);

runCameraExample('camera-confirmed-detection.php', [
    'SENDER'   => 'Variable',
    'SELF'     => 89012,
    'VARIABLE' => 67890,
    'VALUE'    => true
]);
assertCameraExample(
    ($cameraExampleValues[45678] ?? 0) === 67890
    && ($cameraExampleValues[56789] ?? true) === false,
    'The different-camera rule must not accept the same camera twice.'
);

runCameraExample('camera-confirmed-detection.php', [
    'SENDER'   => 'Variable',
    'SELF'     => 89012,
    'VARIABLE' => 78901,
    'VALUE'    => true
]);
assertCameraExample(
    ($cameraExampleValues[34567] ?? -1) === 0
    && ($cameraExampleValues[45678] ?? -1) === 0
    && ($cameraExampleValues[56789] ?? false) === true
    && ($cameraScriptTimers[89012] ?? 0) === 5,
    'Two different cameras inside the window must produce the documented five-second confirmation pulse.'
);

runCameraExample('camera-confirmed-detection.php', [
    'SENDER' => 'TimerEvent',
    'SELF'   => 89012
]);
assertCameraExample(
    ($cameraExampleValues[56789] ?? true) === false
    && ($cameraScriptTimers[89012] ?? -1) === 0,
    'The script timer must end the confirmation pulse without leaving a recurring timer.'
);

fwrite(STDOUT, "Camera pre-alarm examples checks passed.\n");
