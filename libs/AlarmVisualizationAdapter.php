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
            'ArmPartition'                                                                                                                             => self::armPartitionValue($value),
            'ArmPartitions'                                                                                                                            => self::armPartitionsValue($value),
            'DisarmPartition', 'ClearSensorBypassesPartition', 'ClearAlarmMemoryPartition', 'ResetAlarmOutputPartition', 'ResetFalseAlarmPartition'    => self::partitionValue($value, false),
            'ResetFalseAlarmPartitionWithCode'                                                                                                         => self::partitionValue($value, true),
            'DisarmPartitionWithCode'                                                                                                                  => self::partitionValue($value, true),
            'Arm'                                                                                                                                      => self::stringValue($value, 'Arm action requires a mode string.'),
            'DisarmWithCode', 'StopSignalGeneratorWithCode'                                                                                            => self::stringValue($value, 'Code-protected visualization action requires a code string.'),
            'ExportEventHistory', 'ExportDiagnostics'                                                                                                  => self::exportFormat($value),
            'BypassSensorPartition', 'RemoveSensorBypassPartition'                                                                                     => self::partitionVariableID($value),
            'BypassSensor', 'RemoveSensorBypass'                                                                                                       => self::variableID($value),
            'Disarm', 'RefreshVisualization', 'ClearSensorBypasses', 'ClearAlarmMemory', 'ResetAlarmOutput', 'ResetFalseAlarm', 'StopSignalGenerator'  => null,
            default                                                                                                                                    => throw new InvalidArgumentException('Unknown visualization action.')
        };

        return ['Action' => $action, 'Value' => $normalizedValue];
    }

    /** @return array{PartitionID:string,Value:mixed} */
    private static function partitionValue(mixed $value, bool $requiresValue): array
    {
        if (is_string($value)) {
            try {
                $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw new InvalidArgumentException('Partition visualization action contains invalid JSON.');
            }
        }
        if (!is_array($value)) {
            throw new InvalidArgumentException('Partition visualization action requires a JSON object.');
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

    /** @return array{PartitionID:string,Value:string,Silent:?bool,BypassActiveSensors:bool} */
    private static function armPartitionValue(mixed $value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        $partition = self::partitionValue($value, true);
        if (array_key_exists('Silent', $decoded) && !is_bool($decoded['Silent'])) {
            throw new InvalidArgumentException('Silent arming option must be a Boolean.');
        }
        if (array_key_exists('BypassActiveSensors', $decoded) && !is_bool($decoded['BypassActiveSensors'])) {
            throw new InvalidArgumentException('Active-sensor bypass option must be a Boolean.');
        }

        return $partition + [
            'Silent'              => $decoded['Silent'] ?? null,
            'BypassActiveSensors' => $decoded['BypassActiveSensors'] ?? false
        ];
    }

    /** @return array{PartitionIDs:list<string>,Value:string,Silent:?bool,BypassActiveSensors:bool} */
    private static function armPartitionsValue(mixed $value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($decoded)
            || !is_array($decoded['PartitionIDs'] ?? null)
            || !array_is_list($decoded['PartitionIDs'])) {
            throw new InvalidArgumentException('Multi-partition arming requires a partition ID list.');
        }
        $partitionIDs = [];
        foreach ($decoded['PartitionIDs'] as $partitionID) {
            $partition = self::partitionValue(['PartitionID' => $partitionID], false);
            $partitionIDs[] = $partition['PartitionID'];
        }
        if ($partitionIDs === []) {
            throw new InvalidArgumentException('Multi-partition arming requires at least one partition.');
        }
        if (!is_string($decoded['Value'] ?? null)) {
            throw new InvalidArgumentException('Multi-partition arming requires a mode string.');
        }
        if (array_key_exists('Silent', $decoded) && !is_bool($decoded['Silent'])) {
            throw new InvalidArgumentException('Silent arming option must be a Boolean.');
        }
        if (array_key_exists('BypassActiveSensors', $decoded) && !is_bool($decoded['BypassActiveSensors'])) {
            throw new InvalidArgumentException('Active-sensor bypass option must be a Boolean.');
        }

        return [
            'PartitionIDs'        => array_values(array_unique($partitionIDs)),
            'Value'               => $decoded['Value'],
            'Silent'              => $decoded['Silent'] ?? null,
            'BypassActiveSensors' => $decoded['BypassActiveSensors'] ?? false
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

    /** @return array{PartitionID:string,Value:int} */
    private static function partitionVariableID(mixed $value): array
    {
        $partition = self::partitionValue($value, false);
        if (is_string($value)) {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }
        $partition['Value'] = self::variableID(is_array($value) ? ($value['Value'] ?? null) : null);

        return $partition;
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
