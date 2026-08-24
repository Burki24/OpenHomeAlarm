<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Defines the stable alarm modes and states independently from Symcon I/O.
 *
 * State-changing side effects remain in the module for now and will be moved
 * behind this domain boundary in later, independently verified slices.
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

    public static function isValidMode(int $mode): bool
    {
        return in_array($mode, self::modes(), true);
    }

    public static function isValidState(int $state): bool
    {
        return in_array($state, self::states(), true);
    }

    public static function isArmingMode(int $mode): bool
    {
        return in_array($mode, [self::MODE_HOME, self::MODE_AWAY, self::MODE_NIGHT], true);
    }

    public static function canArm(int $state, int $targetMode): bool
    {
        return $state === self::STATE_DISARMED && self::isArmingMode($targetMode);
    }

    public static function canCompleteExitDelay(int $state, int $mode): bool
    {
        return $state === self::STATE_EXIT_DELAY && self::isArmingMode($mode);
    }

    public static function monitorsArmedSensors(int $state): bool
    {
        return in_array($state, [self::STATE_ARMED, self::STATE_ENTRY_DELAY], true);
    }

    public static function canStartEntryDelay(int $state): bool
    {
        return $state === self::STATE_ARMED;
    }

    public static function canEnterAlarm(int $state): bool
    {
        return self::isValidState($state) && $state !== self::STATE_ALARM;
    }

    public static function armingModeFromName(string $mode): ?int
    {
        return match (strtolower(trim($mode))) {
            'home'  => self::MODE_HOME,
            'away'  => self::MODE_AWAY,
            'night' => self::MODE_NIGHT,
            default => null
        };
    }

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
