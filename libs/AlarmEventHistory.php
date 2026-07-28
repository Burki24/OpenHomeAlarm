<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Validates and updates the bounded persistent security event history.
 */
final class AlarmEventHistory
{
    /**
     * @param array<mixed> $history
     * @param list<int>    $validModes
     * @param list<int>    $validStates
     *
     * @return list<array{Time:int,Event:string,Mode:int,State:int,Source:string}>
     */
    public static function normalize(
        array $history,
        array $validModes,
        array $validStates,
        int $limit
    ): array {
        if (!array_is_list($history) || $limit < 1) {
            return [];
        }

        $normalized = [];
        foreach ($history as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $time = $entry['Time'] ?? null;
            $event = $entry['Event'] ?? null;
            $mode = $entry['Mode'] ?? null;
            $state = $entry['State'] ?? null;
            $source = $entry['Source'] ?? null;
            if (
                !is_int($time)
                || !is_string($event)
                || !is_int($mode)
                || !in_array($mode, $validModes, true)
                || !is_int($state)
                || !in_array($state, $validStates, true)
                || !is_string($source)
            ) {
                continue;
            }

            $normalized[] = [
                'Time'   => $time,
                'Event'  => $event,
                'Mode'   => $mode,
                'State'  => $state,
                'Source' => $source
            ];
        }

        return array_slice($normalized, 0, $limit);
    }

    /**
     * @param list<array{Time:int,Event:string,Mode:int,State:int,Source:string}> $history
     * @param array{Time:int,Event:string,Mode:int,State:int,Source:string}       $entry
     *
     * @return list<array{Time:int,Event:string,Mode:int,State:int,Source:string}>
     */
    public static function prepend(array $history, array $entry, int $limit): array
    {
        if ($limit < 1) {
            return [];
        }

        array_unshift($history, $entry);

        return array_slice($history, 0, $limit);
    }
}
