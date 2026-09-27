<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use InvalidArgumentException;

/** Filters and serializes the bounded security event history without changing it. */
final class AlarmEventHistoryExporter
{
    private const FIELDS = ['Time', 'Event', 'Mode', 'State', 'Source', 'PartitionID'];

    /**
     * @param list<array{Time:int,Event:string,Mode:int,State:int,Source:string,PartitionID:string}> $history
     */
    public static function export(
        array $history,
        string $format,
        int $fromTimestamp = 0,
        int $toTimestamp = 0,
        string $eventType = ''
    ): string {
        $format = strtolower(trim($format));
        if (!in_array($format, ['json', 'csv'], true)) {
            throw new InvalidArgumentException('Event history export format must be json or csv.');
        }
        if ($fromTimestamp < 0 || $toTimestamp < 0) {
            throw new InvalidArgumentException('Event history export timestamps must not be negative.');
        }
        if ($fromTimestamp > 0 && $toTimestamp > 0 && $fromTimestamp > $toTimestamp) {
            throw new InvalidArgumentException('Event history export start must not be after its end.');
        }

        $eventType = trim($eventType);
        $filtered = array_values(array_filter(
            $history,
            static fn (array $entry): bool => ($fromTimestamp === 0 || $entry['Time'] >= $fromTimestamp)
                && ($toTimestamp === 0 || $entry['Time'] <= $toTimestamp)
                && ($eventType === '' || $entry['Event'] === $eventType)
        ));

        if ($format === 'json') {
            return json_encode(
                $filtered,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return self::csv($filtered);
    }

    /**
     * @param list<array{Time:int,Event:string,Mode:int,State:int,Source:string,PartitionID:string}> $history
     */
    private static function csv(array $history): string
    {
        $rows = [implode(',', self::FIELDS)];
        foreach ($history as $entry) {
            $row = [];
            foreach (self::FIELDS as $field) {
                $row[] = self::csvField((string) $entry[$field]);
            }
            $rows[] = implode(',', $row);
        }

        return implode("\r\n", $rows) . "\r\n";
    }

    private static function csvField(string $value): string
    {
        if (preg_match('/^[=+\-@\t\r]/u', $value) === 1) {
            $value = "'" . $value;
        }
        if (preg_match('/[",\r\n]/', $value) !== 1) {
            return $value;
        }

        return '"' . str_replace('"', '""', $value) . '"';
    }
}
