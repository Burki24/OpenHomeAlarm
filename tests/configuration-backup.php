<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/AlarmConfigurationBackup.php';

use Burki24\OpenHomeAlarm\AlarmConfigurationBackup;

function assertConfigurationBackup(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$configuration = [
    'Sensors'      => '[{"Name":"Tür"}]',
    'DisarmCode'  => '2468',
    'ExitDelay'   => 30,
    'EnableView'  => true,
    'FontScale'   => 1.25
];
$backup = AlarmConfigurationBackup::create($configuration, 1000);
assertConfigurationBackup($backup['Format'] === AlarmConfigurationBackup::FORMAT, 'Backup format must be stable.');
assertConfigurationBackup($backup['Version'] === 1, 'Backup version must start at 1.');
assertConfigurationBackup($backup['ModuleID'] === AlarmConfigurationBackup::MODULE_ID, 'Backup must identify OpenHomeAlarm.');
assertConfigurationBackup($backup['ContainsSecrets'] === true, 'Backup must explicitly warn that codes can be included.');
assertConfigurationBackup(array_keys($backup['Configuration']) === ['DisarmCode', 'EnableView', 'ExitDelay', 'FontScale', 'Sensors'], 'Backup properties must be sorted deterministically.');

$encoded = AlarmConfigurationBackup::encode($backup);
assertConfigurationBackup(str_contains($encoded, "\n"), 'Backup JSON must be human-readable.');
assertConfigurationBackup(AlarmConfigurationBackup::decode($encoded) === $backup, 'Encoded backups must validate and round-trip.');

foreach ([
    ['invalid', 'Configuration backup must be valid JSON.'],
    ['[]', 'Configuration backup format is not supported.'],
    [str_replace(AlarmConfigurationBackup::FORMAT, 'Other', $encoded), 'Configuration backup format is not supported.'],
    [str_replace('"Version": 1', '"Version": 2', $encoded), 'Configuration backup version is not supported.'],
    [str_replace(AlarmConfigurationBackup::MODULE_ID, '{00000000-0000-0000-0000-000000000000}', $encoded), 'Configuration backup belongs to another module.']
] as [$invalid, $message]) {
    try {
        AlarmConfigurationBackup::decode($invalid);
        throw new RuntimeException('Invalid configuration backups must be rejected.');
    } catch (InvalidArgumentException $exception) {
        assertConfigurationBackup($exception->getMessage() === $message, 'Unexpected backup validation message.');
    }
}

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertConfigurationBackup(
    str_contains($moduleSource, 'public function ExportConfigurationBackup(): string'),
    'Configuration backup export must be available through a generated public OHA_ wrapper.'
);

fwrite(STDOUT, "OpenHomeAlarm configuration backup checks passed.\n");
