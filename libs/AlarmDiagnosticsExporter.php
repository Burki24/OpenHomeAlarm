<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use InvalidArgumentException;

/** Serializes a read-only diagnostics snapshot for browser downloads. */
final class AlarmDiagnosticsExporter
{
    private const FIELDS = [
        'Kind',
        'Name',
        'VariableID',
        'PartitionID',
        'Enabled',
        'Monitored',
        'Status',
        'Active',
        'LastChanged',
        'LastUpdated'
    ];

    /** @param array<string,mixed> $diagnostics */
    public static function export(array $diagnostics, string $format): string
    {
        $format = strtolower(trim($format));
        if (!in_array($format, ['json', 'csv'], true)) {
            throw new InvalidArgumentException('Diagnostics export format must be json or csv.');
        }
        if ($format === 'json') {
            return json_encode(
                $diagnostics,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        $rows = [implode(',', self::FIELDS)];
        foreach ($diagnostics['Items'] ?? [] as $item) {
            $row = [];
            foreach (self::FIELDS as $field) {
                $value = $item[$field] ?? null;
                $row[] = self::csvField(match (true) {
                    $value === null  => '',
                    $value === true  => 'true',
                    $value === false => 'false',
                    default          => (string) $value
                });
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
