<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use JsonException;
use UnexpectedValueException;

/** Normalizes independently addressable alarm partitions. */
final class AlarmPartitionRegistry
{
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
        $enabledCount = 0;
        $defaultCount = 0;
        foreach ($partitions as $index => $partition) {
            if (!is_array($partition)) {
                throw new UnexpectedValueException('Every partition configuration must be an object.');
            }

            $enabled = $partition['Enabled'] ?? true;
            $id = $partition['ID'] ?? '';
            $name = $partition['Name'] ?? '';
            $default = $partition['Default'] ?? false;
            if (!is_bool($enabled) || !is_string($id) || !is_string($name) || !is_bool($default)) {
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
            if ($default && !$enabled) {
                throw new UnexpectedValueException('The default partition must be enabled.');
            }

            $knownIDs[$id] = true;
            $enabledCount += $enabled ? 1 : 0;
            $defaultCount += $default ? 1 : 0;
            $normalized[] = [
                'Enabled' => $enabled,
                'ID'      => $id,
                'Name'    => $name !== '' ? $name : sprintf('Partition %d', $index + 1),
                'Default' => $default
            ];
        }

        if ($enabledCount === 0) {
            throw new UnexpectedValueException('At least one partition must be enabled.');
        }
        if ($defaultCount !== 1) {
            throw new UnexpectedValueException('Exactly one enabled partition must be the default.');
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
                throw new UnexpectedValueException(sprintf('%s must reference an enabled partition.', $context));
            }

            return $partitionID;
        }

        throw new UnexpectedValueException(sprintf('%s references an unknown partition.', $context));
    }
}
