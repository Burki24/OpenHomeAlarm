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
     * @return list<array{Time:int,Event:string,Mode:int,State:int,Source:string,PartitionID:string,ClientName:string}>
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
            $partitionID = $entry['PartitionID'] ?? '';
            $clientName = $entry['ClientName'] ?? '';
            if (
                !is_int($time)
                || !is_string($event)
                || !is_int($mode)
                || !in_array($mode, $validModes, true)
                || !is_int($state)
                || !in_array($state, $validStates, true)
                || !is_string($source)
                || !is_string($partitionID)
                || !is_string($clientName)
            ) {
                continue;
            }

            $normalized[] = [
                'Time'        => $time,
                'Event'       => $event,
                'Mode'        => $mode,
                'State'       => $state,
                'Source'      => $source,
                'PartitionID' => $partitionID,
                'ClientName'  => $clientName
            ];
        }

        return array_slice($normalized, 0, $limit);
    }

    /**
     * @param list<array{Time:int,Event:string,Mode:int,State:int,Source:string,PartitionID:string,ClientName:string}> $history
     * @param array{Time:int,Event:string,Mode:int,State:int,Source:string,PartitionID:string,ClientName:string}       $entry
     *
     * @return list<array{Time:int,Event:string,Mode:int,State:int,Source:string,PartitionID:string,ClientName:string}>
     */
    public static function prepend(array $history, array $entry, int $limit): array
    {
        if ($limit < 1) {
            return [];
        }

        array_unshift($history, $entry);

        return array_slice($history, 0, $limit);
    }

    /**
     * Builds a display-only complete-system summary without changing the raw
     * partition events. Entries are combined only when every matching record
     * belongs to a different partition; repeated events from the same partition
     * therefore remain individually visible.
     *
     * @param list<array{Time:int,Event:string,Mode:int,State:int,Source:string,PartitionID:string,ClientName:string}> $history
     *
     * @return list<array{Time:int,Event:string,Mode:int,State:int,Source:string,PartitionID:string,ClientName:string,PartitionIDs:list<string>,PartitionCount:int}>
     */
    public static function summarizeForCompleteSystem(
        array $history,
        string $mainPartitionID,
        int $limit
    ): array {
        if ($limit < 1) {
            return [];
        }

        $signatures = [];
        foreach ($history as $index => $entry) {
            $signature = serialize([
                $entry['Time'],
                $entry['Event'],
                $entry['Mode'],
                $entry['State'],
                $entry['Source'],
                $entry['ClientName']
            ]);
            $signatures[$signature][] = $index;
        }

        $result = [];
        $handled = [];
        foreach ($history as $index => $entry) {
            if (isset($handled[$index])) {
                continue;
            }

            $signature = serialize([
                $entry['Time'],
                $entry['Event'],
                $entry['Mode'],
                $entry['State'],
                $entry['Source'],
                $entry['ClientName']
            ]);
            $matchingIndexes = $signatures[$signature] ?? [$index];
            $partitionIDs = array_map(
                static fn (int $matchingIndex): string => $history[$matchingIndex]['PartitionID'],
                $matchingIndexes
            );
            $uniquePartitionIDs = array_values(array_unique($partitionIDs));
            $canSummarize = count($matchingIndexes) > 1
                && count($uniquePartitionIDs) === count($matchingIndexes);

            if ($canSummarize) {
                foreach ($matchingIndexes as $matchingIndex) {
                    $handled[$matchingIndex] = true;
                }
                sort($uniquePartitionIDs, SORT_STRING);
                $mainIndex = array_search($mainPartitionID, $uniquePartitionIDs, true);
                if ($mainIndex !== false) {
                    unset($uniquePartitionIDs[$mainIndex]);
                    array_unshift($uniquePartitionIDs, $mainPartitionID);
                    $uniquePartitionIDs = array_values($uniquePartitionIDs);
                }
                $entry['PartitionID'] = $mainPartitionID;
            } else {
                $uniquePartitionIDs = [$entry['PartitionID']];
            }

            $entry['PartitionIDs'] = $uniquePartitionIDs;
            $entry['PartitionCount'] = count($uniquePartitionIDs);
            $result[] = $entry;
            if (count($result) >= $limit) {
                break;
            }
        }

        return $result;
    }
}
