<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/AlarmDiagnostics.php';

use Burki24\OpenHomeAlarm\AlarmDiagnostics;

function assertDiagnostics(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$items = [
    ['Kind' => 'sensor', 'Name' => 'Door', 'VariableID' => 10, 'PartitionID' => 'house', 'Enabled' => true, 'Monitored' => true, 'Status' => 'ready', 'Active' => false, 'LastChanged' => 100, 'LastUpdated' => 110],
    ['Kind' => 'sensor', 'Name' => 'Window', 'VariableID' => 11, 'PartitionID' => 'house', 'Enabled' => true, 'Monitored' => true, 'Status' => 'triggered', 'Active' => true, 'LastChanged' => 120, 'LastUpdated' => 120],
    ['Kind' => 'fault', 'Name' => 'Radio', 'VariableID' => 12, 'PartitionID' => 'house', 'Enabled' => true, 'Monitored' => true, 'Status' => 'missing', 'Active' => null, 'LastChanged' => 0, 'LastUpdated' => 0],
    ['Kind' => 'fault', 'Name' => 'Battery', 'VariableID' => 13, 'PartitionID' => 'garage', 'Enabled' => true, 'Monitored' => true, 'Status' => 'unreadable', 'Active' => null, 'LastChanged' => 130, 'LastUpdated' => 140],
    ['Kind' => 'sensor', 'Name' => 'Unused', 'VariableID' => 14, 'PartitionID' => 'garage', 'Enabled' => false, 'Monitored' => false, 'Status' => 'disabled', 'Active' => null, 'LastChanged' => 0, 'LastUpdated' => 0]
];

$diagnostics = AlarmDiagnostics::build($items, 200);
assertDiagnostics($diagnostics['ApiVersion'] === 1, 'Diagnostics must expose schema version 1.');
assertDiagnostics($diagnostics['GeneratedAt'] === 200, 'Diagnostics must expose their generation time.');
assertDiagnostics($diagnostics['Items'] === $items, 'Diagnostics must preserve normalized item order and values.');
assertDiagnostics(
    $diagnostics['Summary'] === [
        'Total'      => 5,
        'Ready'      => 1,
        'Triggered'  => 1,
        'Missing'    => 1,
        'Unreadable' => 1,
        'Disabled'   => 1,
        'Problems'   => 2,
        'Healthy'    => false
    ],
    'Diagnostics summary must count every stable status and report problems.'
);

$healthy = AlarmDiagnostics::build([$items[0], $items[1], $items[4]], -1);
assertDiagnostics($healthy['GeneratedAt'] === 0, 'Negative generation times must be normalized.');
assertDiagnostics(
    $healthy['Summary']['Healthy'] === true && $healthy['Summary']['Problems'] === 0,
    'Ready, triggered sensor and disabled items must be considered technically healthy.'
);

$decoded = json_decode(AlarmDiagnostics::encode($diagnostics), true, 512, JSON_THROW_ON_ERROR);
assertDiagnostics($decoded === $diagnostics, 'Diagnostics JSON encoding must preserve the public payload.');

fwrite(STDOUT, "OpenHomeAlarm diagnostics model checks passed.\n");
