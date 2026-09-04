<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Selects monitored alarm sensors and detects availability transitions.
 */
final class AlarmSensorMonitor
{
    /** @param array<string,mixed> $sensor */
    public static function isMonitored(array $sensor): bool
    {
        return ($sensor['AlwaysActive'] ?? false) === true
            || ($sensor['ArmHome'] ?? false) === true
            || ($sensor['ArmAway'] ?? false) === true
            || ($sensor['ArmNight'] ?? false) === true;
    }

    /** @param list<array<string,mixed>> $sensors */
    public static function containsVariable(int $variableID, array $sensors): bool
    {
        foreach ($sensors as $sensor) {
            if (
                ($sensor['Enabled'] ?? false) === true
                && self::isMonitored($sensor)
                && ($sensor['VariableID'] ?? 0) === $variableID
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable(array): ?bool $triggerState
     *
     * @return array{UnavailableIDs:list<int>,NewUnavailableIDs:list<int>,RestoredIDs:list<int>}
     */
    public static function availabilityTransitions(array $sensors, array $previousUnavailableIDs, callable $triggerState): array
    {
        $currentUnavailableIDs = [];
        foreach ($sensors as $sensor) {
            $variableID = $sensor['VariableID'] ?? 0;
            if (
                ($sensor['Enabled'] ?? false) !== true
                || !self::isMonitored($sensor)
                || !is_int($variableID)
                || $variableID <= 0
            ) {
                continue;
            }

            if ($triggerState($sensor) === null) {
                $currentUnavailableIDs[$variableID] = true;
            }
        }

        $currentUnavailableIDs = array_map('intval', array_keys($currentUnavailableIDs));
        sort($currentUnavailableIDs, SORT_NUMERIC);
        $previousUnavailableIDs = self::normalizeIDs($previousUnavailableIDs);

        return [
            'UnavailableIDs'    => $currentUnavailableIDs,
            'NewUnavailableIDs' => array_values(array_diff($currentUnavailableIDs, $previousUnavailableIDs)),
            'RestoredIDs'       => array_values(array_diff($previousUnavailableIDs, $currentUnavailableIDs))
        ];
    }

    /**
     * @return list<int>
     */
    private static function normalizeIDs(array $variableIDs): array
    {
        $normalized = [];
        foreach ($variableIDs as $variableID) {
            if (is_int($variableID) && $variableID > 0) {
                $normalized[$variableID] = true;
            }
        }

        $result = array_map('intval', array_keys($normalized));
        sort($result, SORT_NUMERIC);

        return $result;
    }
}
