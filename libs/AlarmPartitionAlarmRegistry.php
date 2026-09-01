<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Maintains partition-local alarm output and memory state and derives the
 * instance-wide summary used by shared physical alarm actions.
 */
final class AlarmPartitionAlarmRegistry
{
    /** @return array{OutputActive:bool,OutputDeadline:int,MemoryActive:bool,LastSource:string,LastTimestamp:int} */
    public static function initial(): array
    {
        return [
            'OutputActive'   => false,
            'OutputDeadline' => 0,
            'MemoryActive'   => false,
            'LastSource'     => '',
            'LastTimestamp'  => 0
        ];
    }

    /**
     * @param list<array{Enabled:bool,ID:string,Name:string,Default:bool}> $partitions
     * @param array<string,mixed>                                        $stored
     *
     * @return array<string,array{OutputActive:bool,OutputDeadline:int,MemoryActive:bool,LastSource:string,LastTimestamp:int}>
     */
    public static function synchronize(array $partitions, array $stored): array
    {
        $result = [];
        foreach ($partitions as $partition) {
            if (!$partition['Enabled']) {
                continue;
            }
            $value = $stored[$partition['ID']] ?? [];
            $result[$partition['ID']] = self::normalize(is_array($value) ? $value : []);
        }

        return $result;
    }

    /** @param array<string,mixed> $state */
    public static function alarm(
        array $state,
        string $source,
        int $timestamp,
        int $durationSeconds = 0,
        ?int $outputStartedAt = null
    ): array {
        $state = self::normalize($state);
        $state['OutputActive'] = true;
        $state['OutputDeadline'] = $durationSeconds > 0
            ? max(0, $outputStartedAt ?? $timestamp) + $durationSeconds
            : 0;
        $state['MemoryActive'] = true;
        $state['LastSource'] = trim($source);
        $state['LastTimestamp'] = max(0, $timestamp);

        return $state;
    }

    /** @param array<string,mixed> $state */
    public static function resetOutput(array $state): array
    {
        $state = self::normalize($state);
        $state['OutputActive'] = false;
        $state['OutputDeadline'] = 0;

        return $state;
    }

    /** @param array<string,mixed> $state */
    public static function clearMemory(array $state): array
    {
        $state = self::normalize($state);
        if ($state['OutputActive']) {
            return $state;
        }
        $state['MemoryActive'] = false;
        $state['LastSource'] = '';
        $state['LastTimestamp'] = 0;

        return $state;
    }

    /**
     * @param array<string,array{OutputActive:bool,OutputDeadline:int,MemoryActive:bool,LastSource:string,LastTimestamp:int}> $states
     *
     * @return array{
     *     OutputActive:bool,
     *     MemoryActive:bool,
     *     LastPartitionID:string,
     *     LastSource:string,
     *     LastTimestamp:int,
     *     ActivePartitionIDs:list<string>,
     *     MemoryPartitionIDs:list<string>
     * }
     */
    public static function aggregate(array $states): array
    {
        $activePartitionIDs = [];
        $memoryPartitionIDs = [];
        $lastPartitionID = '';
        $lastSource = '';
        $lastTimestamp = 0;

        foreach ($states as $partitionID => $rawState) {
            $state = self::normalize($rawState);
            if ($state['OutputActive']) {
                $activePartitionIDs[] = $partitionID;
            }
            if ($state['MemoryActive']) {
                $memoryPartitionIDs[] = $partitionID;
            }
            if ($state['LastTimestamp'] > $lastTimestamp) {
                $lastPartitionID = $partitionID;
                $lastSource = $state['LastSource'];
                $lastTimestamp = $state['LastTimestamp'];
            }
        }

        return [
            'OutputActive'       => $activePartitionIDs !== [],
            'MemoryActive'       => $memoryPartitionIDs !== [],
            'LastPartitionID'    => $lastPartitionID,
            'LastSource'         => $lastSource,
            'LastTimestamp'      => $lastTimestamp,
            'ActivePartitionIDs' => $activePartitionIDs,
            'MemoryPartitionIDs' => $memoryPartitionIDs
        ];
    }

    /**
     * @param array<string,array{OutputActive:bool,OutputDeadline:int,MemoryActive:bool,LastSource:string,LastTimestamp:int}> $states
     *
     * @return list<string>
     */
    public static function expiredOutputPartitionIDs(array $states, int $timestamp): array
    {
        $expired = [];
        foreach ($states as $partitionID => $rawState) {
            $state = self::normalize($rawState);
            if ($state['OutputActive'] && $state['OutputDeadline'] > 0 && $state['OutputDeadline'] <= $timestamp) {
                $expired[] = $partitionID;
            }
        }

        return $expired;
    }

    /**
     * @param array<string,mixed> $state
     *
     * @return array{OutputActive:bool,OutputDeadline:int,MemoryActive:bool,LastSource:string,LastTimestamp:int}
     */
    private static function normalize(array $state): array
    {
        return [
            'OutputActive'   => is_bool($state['OutputActive'] ?? null) ? $state['OutputActive'] : false,
            'OutputDeadline' => max(0, is_int($state['OutputDeadline'] ?? null) ? $state['OutputDeadline'] : 0),
            'MemoryActive'   => is_bool($state['MemoryActive'] ?? null) ? $state['MemoryActive'] : false,
            'LastSource'     => is_string($state['LastSource'] ?? null) ? trim($state['LastSource']) : '',
            'LastTimestamp'  => max(0, is_int($state['LastTimestamp'] ?? null) ? $state['LastTimestamp'] : 0)
        ];
    }
}
