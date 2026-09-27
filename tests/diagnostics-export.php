<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/AlarmDiagnostics.php';
require_once __DIR__ . '/../libs/AlarmDiagnosticsExporter.php';

use Burki24\OpenHomeAlarm\AlarmDiagnostics;
use Burki24\OpenHomeAlarm\AlarmDiagnosticsExporter;

function assertDiagnosticsExport(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$items = [
    ['Kind' => 'sensor', 'Name' => 'Front, "door"', 'VariableID' => 10, 'PartitionID' => 'house', 'Enabled' => true, 'Monitored' => true, 'Status' => 'ready', 'Active' => false, 'LastChanged' => 100, 'LastUpdated' => 110],
    ['Kind' => 'fault', 'Name' => '=1+1', 'VariableID' => 11, 'PartitionID' => 'garage', 'Enabled' => true, 'Monitored' => true, 'Status' => 'missing', 'Active' => null, 'LastChanged' => 0, 'LastUpdated' => 0]
];
$diagnostics = AlarmDiagnostics::build($items, 200);

$json = json_decode(AlarmDiagnosticsExporter::export($diagnostics, ' JSON '), true, 512, JSON_THROW_ON_ERROR);
assertDiagnosticsExport($json === $diagnostics, 'JSON export must preserve the complete diagnostics snapshot.');

$expectedCsv = "Kind,Name,VariableID,PartitionID,Enabled,Monitored,Status,Active,LastChanged,LastUpdated\r\n"
    . "sensor,\"Front, \"\"door\"\"\",10,house,true,true,ready,false,100,110\r\n"
    . "fault,'=1+1,11,garage,true,true,missing,,0,0\r\n";
assertDiagnosticsExport(
    AlarmDiagnosticsExporter::export($diagnostics, 'csv') === $expectedCsv,
    'CSV export must use stable columns, preserve nulls and escape formulas and delimiters.'
);

try {
    AlarmDiagnosticsExporter::export($diagnostics, 'xml');
    throw new RuntimeException('Unsupported diagnostics export formats must be rejected.');
} catch (InvalidArgumentException $exception) {
    assertDiagnosticsExport(
        $exception->getMessage() === 'Diagnostics export format must be json or csv.',
        'Diagnostics export validation must remain stable.'
    );
}

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertDiagnosticsExport(
    str_contains($moduleSource, 'public function ExportDiagnostics('),
    'The diagnostics export must be available through a generated public OHA_ wrapper.'
);

fwrite(STDOUT, "OpenHomeAlarm diagnostics export checks passed.\n");
