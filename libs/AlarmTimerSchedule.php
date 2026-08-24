<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Calculates persistent timer deadlines and restart-safe restoration plans.
 *
 * Symcon timer and attribute I/O deliberately remains in the module. Keeping
 * time as an explicit input makes the scheduling rules deterministic to test.
 */
final class AlarmTimerSchedule
{
    /**
     * @return array{Deadline:int,IntervalMilliseconds:int}
     */
    public static function start(int $now, int $durationSeconds): array
    {
        $durationSeconds = max(0, $durationSeconds);

        return [
            'Deadline'             => $now + $durationSeconds,
            'IntervalMilliseconds' => self::toMilliseconds($durationSeconds)
        ];
    }

    /**
     * @return array{Expired:bool,RemainingSeconds:int,IntervalMilliseconds:int}
     */
    public static function restore(int $deadline, int $now): array
    {
        $remainingSeconds = max(0, $deadline - $now);

        return [
            'Expired'              => $deadline <= 0 || $remainingSeconds === 0,
            'RemainingSeconds'     => $remainingSeconds,
            'IntervalMilliseconds' => self::toMilliseconds($remainingSeconds)
        ];
    }

    private static function toMilliseconds(int $seconds): int
    {
        return max(0, $seconds) * 1000;
    }
}
