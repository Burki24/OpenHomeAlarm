<?php

declare(strict_types=1);

use Burki24\OpenHomeAlarm\AlarmEventHistoryExporter;

require_once dirname(__DIR__) . '/libs/AlarmEventHistoryExporter.php';

function assertEventHistoryExport(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$history = [
    ['Time' => 300, 'Event' => 'disarmed', 'Mode' => 0, 'State' => 0, 'Source' => 'Alice', 'PartitionID' => 'house'],
    ['Time' => 200, 'Event' => 'alarm', 'Mode' => 2, 'State' => 4, 'Source' => 'Front "door", hall', 'PartitionID' => 'house'],
    ['Time' => 100, 'Event' => 'alarm', 'Mode' => 2, 'State' => 4, 'Source' => 'Garage', 'PartitionID' => 'garage'],
    ['Time' => 50, 'Event' => 'alarm', 'Mode' => 2, 'State' => 4, 'Source' => '=1+1', 'PartitionID' => 'garage']
];

$json = AlarmEventHistoryExporter::export($history, ' JSON ', 150, 300, 'alarm');
assertEventHistoryExport(
    json_decode($json, true, 512, JSON_THROW_ON_ERROR) === [$history[1]],
    'JSON export must preserve fields and order while applying inclusive filters.'
);
assertEventHistoryExport(
    json_decode(AlarmEventHistoryExporter::export($history, 'json', 200, 200, ''), true, 512, JSON_THROW_ON_ERROR)
        === [$history[1]],
    'Equal time boundaries must include events at that exact timestamp.'
);

$csv = AlarmEventHistoryExporter::export($history, 'csv', 0, 0, 'alarm');
$expectedCsv = "Time,Event,Mode,State,Source,PartitionID\r\n"
    . "200,alarm,2,4,\"Front \"\"door\"\", hall\",house\r\n"
    . "100,alarm,2,4,Garage,garage\r\n"
    . "50,alarm,2,4,'=1+1,garage\r\n";
assertEventHistoryExport($csv === $expectedCsv, 'CSV export must use a stable header, CRLF rows and RFC-compatible quoting.');
assertEventHistoryExport(
    !str_contains($csv, ',=1+1,') && str_contains($csv, ",'=1+1,"),
    'CSV fields must neutralize spreadsheet formulas without adding whitespace.'
);
assertEventHistoryExport(
    AlarmEventHistoryExporter::export([], 'csv', 0, 0, '') === "Time,Event,Mode,State,Source,PartitionID\r\n",
    'An empty CSV export must still expose its stable schema.'
);

foreach ([
    ['xml', 0, 0, '', 'Event history export format must be json or csv.'],
    ['json', -1, 0, '', 'Event history export timestamps must not be negative.'],
    ['json', 300, 200, '', 'Event history export start must not be after its end.']
] as [$format, $from, $to, $event, $message]) {
    try {
        AlarmEventHistoryExporter::export($history, $format, $from, $to, $event);
        throw new RuntimeException('Invalid export arguments must be rejected.');
    } catch (InvalidArgumentException $exception) {
        assertEventHistoryExport($exception->getMessage() === $message, 'Unexpected export validation message.');
    }
}

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertEventHistoryExport(
    str_contains($moduleSource, 'public function ExportEventHistory('),
    'The event history export must be available through a generated public OHA_ wrapper.'
);

fwrite(STDOUT, "OpenHomeAlarm event history export checks passed.\n");
