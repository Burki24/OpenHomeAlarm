<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Selects continuously monitored fault inputs and detects their transitions.
 */
final class AlarmFaultMonitor
{
    /** @param list<array<string,mixed>> $faultInputs */
    public static function containsVariable(int $variableID, array $faultInputs): bool
    {
        foreach ($faultInputs as $faultInput) {
            if (
                ($faultInput['Enabled'] ?? false) === true
                && ($faultInput['VariableID'] ?? 0) === $variableID
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable(array): ?bool $triggerState
     *
     * @return array{ActiveIDs:list<int>,NewlyActiveInputs:list<array{0:array,1:?bool}>,ClearedIDs:list<int>}
     */
    public static function transitions(array $faultInputs, array $previousActiveIDs, callable $triggerState): array
    {
        $activeIDs = [];
        $newlyActiveInputs = [];
        $previousActiveIDs = self::normalizeIDs($previousActiveIDs);

        foreach ($faultInputs as $faultInput) {
            $variableID = $faultInput['VariableID'] ?? 0;
            if (
                ($faultInput['Enabled'] ?? false) !== true
                || !is_int($variableID)
                || $variableID <= 0
            ) {
                continue;
            }

            $state = $triggerState($faultInput);
            if ($state === false) {
                continue;
            }

            $activeIDs[$variableID] = true;
            if (!in_array($variableID, $previousActiveIDs, true)) {
                $newlyActiveInputs[] = [$faultInput, $state];
            }
        }

        $activeIDs = array_map('intval', array_keys($activeIDs));
        sort($activeIDs, SORT_NUMERIC);

        return [
            'ActiveIDs'         => $activeIDs,
            'NewlyActiveInputs' => $newlyActiveInputs,
            'ClearedIDs'        => array_values(array_diff($previousActiveIDs, $activeIDs))
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
