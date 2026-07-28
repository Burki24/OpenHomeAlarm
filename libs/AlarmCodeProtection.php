<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Evaluates failed disarm-code attempts and temporary lockouts without accessing Symcon state.
 */
final class AlarmCodeProtection
{
    /**
     * @return array{
     *     Enabled: bool,
     *     Locked: bool,
     *     FailedAttempts: int,
     *     MaxAttempts: int,
     *     RemainingAttempts: int,
     *     LockoutUntil: int,
     *     LockoutRemaining: int
     * }
     */
    public static function status(
        bool $enabled,
        int $failedAttempts,
        int $lockoutUntil,
        int $maxAttempts,
        int $now
    ): array {
        $maxAttempts = max(1, $maxAttempts);
        $failedAttempts = max(0, min($maxAttempts, $failedAttempts));
        $lockoutUntil = max(0, $lockoutUntil);

        if (!$enabled || $lockoutUntil <= $now) {
            if (!$enabled || $lockoutUntil > 0) {
                $failedAttempts = 0;
            }
            $lockoutUntil = 0;
        }

        $locked = $enabled && $lockoutUntil > $now;

        return [
            'Enabled'           => $enabled,
            'Locked'            => $locked,
            'FailedAttempts'    => $failedAttempts,
            'MaxAttempts'       => $maxAttempts,
            'RemainingAttempts' => $locked ? 0 : max(0, $maxAttempts - $failedAttempts),
            'LockoutUntil'      => $lockoutUntil,
            'LockoutRemaining'  => $locked ? max(1, $lockoutUntil - $now) : 0
        ];
    }

    /**
     * @param array{
     *     Enabled: bool,
     *     Locked: bool,
     *     FailedAttempts: int,
     *     MaxAttempts: int,
     *     RemainingAttempts: int,
     *     LockoutUntil: int,
     *     LockoutRemaining: int
     * } $status
     *
     * @return array{
     *     Enabled: bool,
     *     Locked: bool,
     *     FailedAttempts: int,
     *     MaxAttempts: int,
     *     RemainingAttempts: int,
     *     LockoutUntil: int,
     *     LockoutRemaining: int
     * }
     */
    public static function registerFailure(array $status, int $lockoutSeconds, int $now): array
    {
        if (!$status['Enabled'] || $status['Locked']) {
            return $status;
        }

        $failedAttempts = min($status['MaxAttempts'], $status['FailedAttempts'] + 1);
        $lockoutUntil = $failedAttempts >= $status['MaxAttempts']
            ? $now + max(1, $lockoutSeconds)
            : 0;

        return self::status(
            true,
            $failedAttempts,
            $lockoutUntil,
            $status['MaxAttempts'],
            $now
        );
    }
}
