<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use InvalidArgumentException;

/** Maintains restart-safe, independent mode and delay state for alarm partitions. */
final class AlarmPartitionRuntime
{
    /** @return array{Mode:int,State:int,Deadline:int,DelaySource:string,PendingSourceID:int} */
    public static function initial(): array
    {
        return ['Mode' => AlarmStateMachine::MODE_NONE, 'State' => AlarmStateMachine::STATE_DISARMED,
            'Deadline' => 0, 'DelaySource' => '', 'PendingSourceID' => 0];
    }

    /** @param list<array{Enabled:bool,ID:string,Name:string,Default:bool}> $partitions
     * @param array<string,mixed> $stored
     * @return array<string,array{Mode:int,State:int,Deadline:int,DelaySource:string,PendingSourceID:int}> */
    public static function synchronize(array $partitions, array $stored): array
    {
        $result = [];
        foreach ($partitions as $partition) {
            if ($partition['Enabled']) {
                $value = $stored[$partition['ID']] ?? [];
                $result[$partition['ID']] = self::normalize(is_array($value) ? $value : []);
            }
        }
        return $result;
    }

    /** @param array<string,mixed> $state */
    public static function arm(array $state, int $mode, int $now, int $delaySeconds): array
    {
        $state = self::normalize($state);
        if (!AlarmStateMachine::canArm($state['State'], $mode)) {
            throw new InvalidArgumentException('Alarm partition cannot be armed in its current state.');
        }
        $state['Mode'] = $mode;
        $state['State'] = $delaySeconds > 0 ? AlarmStateMachine::STATE_EXIT_DELAY : AlarmStateMachine::STATE_ARMED;
        $state['Deadline'] = $delaySeconds > 0 ? $now + $delaySeconds : 0;
        return $state;
    }

    /** @param array<string,mixed> $state */
    public static function startEntryDelay(array $state, int $now, int $delaySeconds, string $source, int $sourceID): array
    {
        $state = self::normalize($state);
        if (!AlarmStateMachine::canStartEntryDelay($state['State'])) {
            return $state;
        }
        $state['State'] = $delaySeconds > 0 ? AlarmStateMachine::STATE_ENTRY_DELAY : AlarmStateMachine::STATE_ALARM;
        $state['Deadline'] = $delaySeconds > 0 ? $now + $delaySeconds : 0;
        $state['DelaySource'] = $delaySeconds > 0 ? $source : '';
        $state['PendingSourceID'] = $delaySeconds > 0 ? $sourceID : 0;
        return $state;
    }

    /** @param array<string,mixed> $state */
    public static function alarm(array $state): array
    {
        $state = self::normalize($state);
        $state['State'] = AlarmStateMachine::STATE_ALARM;
        $state['Deadline'] = 0;
        $state['DelaySource'] = '';
        $state['PendingSourceID'] = 0;
        return $state;
    }

    /** @param array<string,mixed> $state */
    public static function disarm(array $state): array
    {
        return self::initial();
    }

    /** @param array<string,mixed> $state */
    public static function advance(array $state, int $now): array
    {
        $state = self::normalize($state);
        if ($state['Deadline'] <= 0 || $state['Deadline'] > $now) {
            return $state;
        }
        if ($state['State'] === AlarmStateMachine::STATE_EXIT_DELAY) {
            $state['State'] = AlarmStateMachine::STATE_ARMED;
        } elseif ($state['State'] === AlarmStateMachine::STATE_ENTRY_DELAY) {
            $state['State'] = AlarmStateMachine::STATE_ALARM;
        }
        $state['Deadline'] = 0;
        $state['DelaySource'] = '';
        $state['PendingSourceID'] = 0;
        return $state;
    }

    /** @param array<string,mixed> $state */
    private static function normalize(array $state): array
    {
        $mode = is_int($state['Mode'] ?? null) && AlarmStateMachine::isValidMode($state['Mode']) ? $state['Mode'] : AlarmStateMachine::MODE_NONE;
        $alarmState = is_int($state['State'] ?? null) && AlarmStateMachine::isValidState($state['State']) ? $state['State'] : AlarmStateMachine::STATE_DISARMED;
        return ['Mode'        => $mode, 'State' => $alarmState,
            'Deadline'        => max(0, is_int($state['Deadline'] ?? null) ? $state['Deadline'] : 0),
            'DelaySource'     => is_string($state['DelaySource'] ?? null) ? $state['DelaySource'] : '',
            'PendingSourceID' => max(0, is_int($state['PendingSourceID'] ?? null) ? $state['PendingSourceID'] : 0)];
    }
}
