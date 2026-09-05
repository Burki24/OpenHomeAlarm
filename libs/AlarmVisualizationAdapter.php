<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use InvalidArgumentException;

/**
 * Validates and normalizes commands received from visualization transports.
 */
final class AlarmVisualizationAdapter
{
    /**
     * @return array{Action:string,Value:mixed}
     */
    public static function command(string $action, mixed $value): array
    {
        $normalizedValue = match ($action) {
            'ArmPartition'                                                                                              => self::partitionValue($value, true),
            'DisarmPartition', 'ClearSensorBypassesPartition', 'ClearAlarmMemoryPartition', 'ResetAlarmOutputPartition' => self::partitionValue($value, false),
            'DisarmPartitionWithCode'                                                                                   => self::partitionValue($value, true),
            'Arm'                                                                                                       => self::stringValue($value, 'Arm action requires a mode string.'),
            'DisarmWithCode'                                                                                            => self::stringValue($value, 'DisarmWithCode action requires a code string.'),
            'ExportEventHistory', 'ExportDiagnostics'                                                                   => self::exportFormat($value),
            'BypassSensor', 'RemoveSensorBypass'                                                                        => self::variableID($value),
            'Disarm', 'RefreshVisualization', 'ClearSensorBypasses', 'ClearAlarmMemory', 'ResetAlarmOutput'             => null,
            default                                                                                                     => throw new InvalidArgumentException('Unknown visualization action.')
        };

        return ['Action' => $action, 'Value' => $normalizedValue];
    }

    /** @return array{PartitionID:string,Value:mixed} */
    private static function partitionValue(mixed $value, bool $requiresValue): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Partition visualization action requires an object.');
        }

        $partitionID = strtolower(trim(self::stringValue(
            $value['PartitionID'] ?? null,
            'Partition visualization action requires a partition ID.'
        )));
        if (preg_match('/^[a-z][a-z0-9_-]{0,31}$/', $partitionID) !== 1) {
            throw new InvalidArgumentException('Partition visualization action contains an invalid partition ID.');
        }
        if ($requiresValue && !is_string($value['Value'] ?? null)) {
            throw new InvalidArgumentException('Partition visualization action requires a string value.');
        }

        return [
            'PartitionID' => $partitionID,
            'Value'       => $requiresValue ? $value['Value'] : null
        ];
    }

    private static function stringValue(mixed $value, string $error): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException($error);
        }

        return $value;
    }

    private static function variableID(mixed $value): int
    {
        $variableID = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        if ($variableID === false) {
            throw new InvalidArgumentException('Visualization action requires a positive variable ID.');
        }

        return $variableID;
    }

    private static function exportFormat(mixed $value): string
    {
        $format = strtolower(trim(self::stringValue($value, 'Event history export format must be json or csv.')));
        if (!in_array($format, ['json', 'csv'], true)) {
            throw new InvalidArgumentException('Event history export format must be json or csv.');
        }

        return $format;
    }
}
