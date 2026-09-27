<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Defines the stable alarm modes and states independently from Symcon I/O.
 *
 * The class contains only transition predicates and stable name/value mappings;
 * Symcon I/O and state-changing side effects remain in the module boundary.
 */
final class AlarmStateMachine
{
    public const MODE_NONE = 0;
    public const MODE_HOME = 1;
    public const MODE_AWAY = 2;
    public const MODE_NIGHT = 3;

    public const STATE_DISARMED = 0;
    public const STATE_EXIT_DELAY = 1;
    public const STATE_ARMED = 2;
    public const STATE_ENTRY_DELAY = 3;
    public const STATE_ALARM = 4;

    /**
     * @return list<int>
     */
    public static function modes(): array
    {
        return [self::MODE_NONE, self::MODE_HOME, self::MODE_AWAY, self::MODE_NIGHT];
    }

    /**
     * @return list<int>
     */
    public static function states(): array
    {
        return [
            self::STATE_DISARMED,
            self::STATE_EXIT_DELAY,
            self::STATE_ARMED,
            self::STATE_ENTRY_DELAY,
            self::STATE_ALARM
        ];
    }

    /** Returns whether the value is one of the defined alarm modes. */
    public static function isValidMode(int $mode): bool
    {
        return in_array($mode, self::modes(), true);
    }

    /** Returns whether the value is one of the defined alarm states. */
    public static function isValidState(int $state): bool
    {
        return in_array($state, self::states(), true);
    }

    /** Returns whether the mode represents Home, Away or Night arming. */
    public static function isArmingMode(int $mode): bool
    {
        return in_array($mode, [self::MODE_HOME, self::MODE_AWAY, self::MODE_NIGHT], true);
    }

    /** Returns whether a disarmed state may enter the requested arming mode. */
    public static function canArm(int $state, int $targetMode): bool
    {
        return $state === self::STATE_DISARMED && self::isArmingMode($targetMode);
    }

    /** Returns whether the current mode and state may complete an exit delay. */
    public static function canCompleteExitDelay(int $state, int $mode): bool
    {
        return $state === self::STATE_EXIT_DELAY && self::isArmingMode($mode);
    }

    /** Returns whether normal armed sensors must currently be evaluated. */
    public static function monitorsArmedSensors(int $state): bool
    {
        return in_array($state, [self::STATE_ARMED, self::STATE_ENTRY_DELAY], true);
    }

    /** Returns whether an entry delay may start from the current state. */
    public static function canStartEntryDelay(int $state): bool
    {
        return $state === self::STATE_ARMED;
    }

    /** Returns whether the current valid state may transition into Alarm. */
    public static function canEnterAlarm(int $state): bool
    {
        return self::isValidState($state) && $state !== self::STATE_ALARM;
    }

    /** Converts a case-insensitive public mode name to its numeric value. */
    public static function armingModeFromName(string $mode): ?int
    {
        return match (strtolower(trim($mode))) {
            'home'  => self::MODE_HOME,
            'away'  => self::MODE_AWAY,
            'night' => self::MODE_NIGHT,
            default => null
        };
    }

    /** Returns the stable public name of a mode or `unknown`. */
    public static function modeName(int $mode): string
    {
        return match ($mode) {
            self::MODE_NONE  => 'none',
            self::MODE_HOME  => 'home',
            self::MODE_AWAY  => 'away',
            self::MODE_NIGHT => 'night',
            default          => 'unknown'
        };
    }

    /** Returns the stable public name of a state or `unknown`. */
    public static function stateName(int $state): string
    {
        return match ($state) {
            self::STATE_DISARMED    => 'disarmed',
            self::STATE_EXIT_DELAY  => 'exit_delay',
            self::STATE_ARMED       => 'armed',
            self::STATE_ENTRY_DELAY => 'entry_delay',
            self::STATE_ALARM       => 'alarm',
            default                 => 'unknown'
        };
    }
}
