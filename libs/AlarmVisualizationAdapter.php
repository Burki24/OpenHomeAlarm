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
            'Arm'                                                                                           => self::stringValue($value, 'Arm action requires a mode string.'),
            'DisarmWithCode'                                                                                => self::stringValue($value, 'DisarmWithCode action requires a code string.'),
            'BypassSensor', 'RemoveSensorBypass'                                                            => self::variableID($value),
            'Disarm', 'RefreshVisualization', 'ClearSensorBypasses', 'ClearAlarmMemory', 'ResetAlarmOutput' => null,
            default                                                                                         => throw new InvalidArgumentException('Unknown visualization action.')
        };

        return ['Action' => $action, 'Value' => $normalizedValue];
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
}
