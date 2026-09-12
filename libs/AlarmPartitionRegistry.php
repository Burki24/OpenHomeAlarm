<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use JsonException;
use UnexpectedValueException;

require_once __DIR__ . '/AlarmPartitionAssignmentException.php';

/** Normalizes independently addressable alarm partitions. */
final class AlarmPartitionRegistry
{
    private const MAIN_PARTITION_ID = 'main';

    /** @return list<array{Enabled:bool,ID:string,Name:string,Default:bool}> */
    public static function partitions(string $encodedPartitions): array
    {
        try {
            $partitions = json_decode($encodedPartitions, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Invalid partition configuration JSON.', 0, $exception);
        }
        if (!is_array($partitions) || !array_is_list($partitions) || $partitions === []) {
            throw new UnexpectedValueException('Partition configuration must be a non-empty list.');
        }

        $normalized = [];
        $knownIDs = [];
        $mainPartitionIndex = null;
        foreach ($partitions as $index => $partition) {
            if (!is_array($partition)) {
                throw new UnexpectedValueException('Every partition configuration must be an object.');
            }

            $enabled = $partition['Enabled'] ?? true;
            $id = $partition['ID'] ?? '';
            $name = $partition['Name'] ?? '';
            if (!is_bool($enabled) || !is_string($id) || !is_string($name)) {
                throw new UnexpectedValueException('Invalid partition field type.');
            }

            $id = strtolower(trim($id));
            $name = trim($name);
            if (preg_match('/^[a-z][a-z0-9_-]{0,31}$/', $id) !== 1) {
                throw new UnexpectedValueException('Partition IDs must use 1 to 32 lowercase letters, digits, underscores or hyphens.');
            }
            if (isset($knownIDs[$id])) {
                throw new UnexpectedValueException('Partition IDs must be unique.');
            }
            $knownIDs[$id] = true;
            if ($id === self::MAIN_PARTITION_ID) {
                $mainPartitionIndex = $index;
            }
            $normalized[] = [
                'Enabled' => $enabled,
                'ID'      => $id,
                'Name'    => $name !== '' ? $name : sprintf('Partition %d', $index + 1),
                'Default' => false
            ];
        }

        if ($mainPartitionIndex === null) {
            throw new UnexpectedValueException('The main partition is required.');
        }
        if (!$normalized[$mainPartitionIndex]['Enabled']) {
            throw new UnexpectedValueException('The main partition must be enabled.');
        }
        foreach ($normalized as $index => $partition) {
            $normalized[$index]['Default'] = $partition['ID'] === self::MAIN_PARTITION_ID;
        }

        return $normalized;
    }

    /**
     * @param list<array{Enabled:bool,ID:string,Name:string,Default:bool}> $partitions
     *
     * @return array{Enabled:bool,ID:string,Name:string,Default:bool}
     */
    public static function defaultPartition(array $partitions): array
    {
        foreach ($partitions as $partition) {
            if ($partition['Default']) {
                return $partition;
            }
        }

        throw new UnexpectedValueException('Default partition is missing.');
    }

    /**
     * Resolves an optional assignment to an enabled partition.
     *
     * @param list<array{Enabled:bool,ID:string,Name:string,Default:bool}> $partitions
     */
    public static function assignedPartitionID(
        string $partitionID,
        array $partitions,
        string $context
    ): string {
        $partitionID = strtolower(trim($partitionID));
        if ($partitionID === '') {
            return self::defaultPartition($partitions)['ID'];
        }

        foreach ($partitions as $partition) {
            if ($partition['ID'] !== $partitionID) {
                continue;
            }
            if (!$partition['Enabled']) {
                throw new AlarmPartitionAssignmentException(
                    sprintf('%s must reference an enabled partition.', $context)
                );
            }

            return $partitionID;
        }

        throw new AlarmPartitionAssignmentException(sprintf('%s references an unknown partition.', $context));
    }

    /**
     * Resolves a sensor assignment to one or more enabled partitions.
     * An empty assignment deliberately falls back to the default partition for
     * compatibility with configurations created before multi-area support.
     *
     * @param list<string> $partitionIDs
     * @param list<array{Enabled:bool,ID:string,Name:string,Default:bool}> $partitions
     *
     * @return list<string>
     */
    public static function assignedPartitionIDs(array $partitionIDs, array $partitions, string $context): array
    {
        if ($partitionIDs === []) {
            return [self::defaultPartition($partitions)['ID']];
        }

        $resolved = [];
        foreach ($partitionIDs as $partitionID) {
            $resolved[] = self::assignedPartitionID($partitionID, $partitions, $context);
        }

        return array_values(array_unique($resolved));
    }
}
