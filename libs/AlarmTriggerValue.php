<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Converts and compares persisted trigger values according to a Symcon variable type.
 */
final class AlarmTriggerValue
{
    /**
     * Converts a Symcon value to the stable string representation stored in module properties.
     *
     * @param int|null $variableType Symcon variable type, or null when it is not known
     */
    public static function toStorageString(mixed $value, ?int $variableType = null): ?string
    {
        if ($variableType === 0 && (is_bool($value) || is_int($value) || is_float($value))) {
            return (bool) $value ? 'true' : 'false';
        }
        if ($variableType === 1 && (is_int($value) || is_float($value))) {
            return (string) (int) $value;
        }
        if ($variableType === 2 && (is_int($value) || is_float($value))) {
            return json_encode((float) $value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        }
        if ($variableType === 3 && is_scalar($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        }
        if (is_string($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Returns true for a matching value, false for a mismatch, and null when the
     * value cannot be evaluated for the supplied Symcon variable type.
     *
     * @param int $variableType Symcon variable type
     */
    public static function matches(int $variableType, string $triggerValue, mixed $currentValue): ?bool
    {
        switch ($variableType) {
            case 0:
                if (!is_bool($currentValue)) {
                    return null;
                }

                $normalizedTrigger = strtolower(trim($triggerValue));
                if (in_array($normalizedTrigger, ['true', '1'], true)) {
                    return $currentValue === true;
                }
                if (in_array($normalizedTrigger, ['false', '0'], true)) {
                    return $currentValue === false;
                }

                return null;

            case 1:
                if (!is_int($currentValue) || preg_match('/^-?\d+$/', trim($triggerValue)) !== 1) {
                    return null;
                }

                return $currentValue === (int) $triggerValue;

            case 2:
                if (!is_float($currentValue) || !is_numeric(trim($triggerValue))) {
                    return null;
                }

                return $currentValue === (float) $triggerValue;

            case 3:
                if (!is_string($currentValue)) {
                    return null;
                }

                return $currentValue === $triggerValue;
        }

        return null;
    }
}
