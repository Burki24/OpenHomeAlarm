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
    'DisarmCode'   => '2468',
    'ExitDelay'    => 30,
    'EnableView'   => true,
    'FontScale'    => 1.25,
    'BorderRadius' => 8.0
];
$backup = AlarmConfigurationBackup::create($configuration, 1000);
assertConfigurationBackup($backup['Format'] === AlarmConfigurationBackup::FORMAT, 'Backup format must be stable.');
assertConfigurationBackup($backup['Version'] === 1, 'Backup version must start at 1.');
assertConfigurationBackup($backup['ModuleID'] === AlarmConfigurationBackup::MODULE_ID, 'Backup must identify OpenHomeAlarm.');
assertConfigurationBackup($backup['ContainsSecrets'] === true, 'Backup must explicitly warn that codes can be included.');
assertConfigurationBackup(array_keys($backup['Configuration']) === ['BorderRadius', 'DisarmCode', 'EnableView', 'ExitDelay', 'FontScale', 'Sensors'], 'Backup properties must be sorted deterministically.');

$encoded = AlarmConfigurationBackup::encode($backup);
assertConfigurationBackup(str_contains($encoded, "\n"), 'Backup JSON must be human-readable.');
assertConfigurationBackup(str_contains($encoded, '"BorderRadius": 8.0'), 'Whole-number floats must retain their JSON type.');
assertConfigurationBackup(AlarmConfigurationBackup::decode($encoded) === $backup, 'Encoded backups must validate and round-trip.');
$current = $configuration + ['NewProperty' => 'default'];
assertConfigurationBackup(
    AlarmConfigurationBackup::configurationForRestore($backup, $current) === $current,
    'Restore preparation must retain properties introduced after the backup was created.'
);

$unknownBackup = $backup;
$unknownBackup['Configuration']['Unknown'] = true;
try {
    AlarmConfigurationBackup::configurationForRestore($unknownBackup, $current);
    throw new RuntimeException('Unknown backup properties must be rejected.');
} catch (InvalidArgumentException $exception) {
    assertConfigurationBackup(
        $exception->getMessage() === 'Configuration backup contains an unknown property: Unknown.',
        'Unknown properties must produce a stable restore diagnostic.'
    );
}

$wrongTypeBackup = $backup;
$wrongTypeBackup['Configuration']['ExitDelay'] = '30';
try {
    AlarmConfigurationBackup::configurationForRestore($wrongTypeBackup, $current);
    throw new RuntimeException('Backup property type mismatches must be rejected.');
} catch (InvalidArgumentException $exception) {
    assertConfigurationBackup(
        $exception->getMessage() === 'Configuration backup property type does not match: ExitDelay.',
        'Type mismatches must produce a stable restore diagnostic.'
    );
}

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
assertConfigurationBackup(
    str_contains($moduleSource, 'public function RestoreConfigurationBackup(string $json): bool')
        && str_contains($moduleSource, 'IPS_SetConfiguration($this->InstanceID, $configurationJSON);')
        && str_contains($moduleSource, 'IPS_ApplyChanges($this->InstanceID);'),
    'Configuration restore must be available publicly and apply through the native Symcon configuration boundary.'
);

fwrite(STDOUT, "OpenHomeAlarm configuration backup checks passed.\n");
