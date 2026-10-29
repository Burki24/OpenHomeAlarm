<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use JsonException;
use UnexpectedValueException;

/**
 * Defines the provider-neutral contract for confirmed external alarm events.
 *
 * Transport authentication and alarm state changes remain in the module. This
 * class only validates persistent source permissions and incoming event data.
 */
final class AlarmExternalEvent
{
    public const API_VERSION = 1;
    public const MAX_EVENT_AGE_SECONDS = 300;
    public const MAX_FUTURE_SKEW_SECONDS = 60;
    public const RECEIPT_RETENTION_SECONDS = 86_400;
    public const RECEIPT_LIMIT = 512;

    private const ID_PATTERN = '/^[A-Za-z0-9][A-Za-z0-9_.:-]{0,127}$/';

    /**
     * @param list<string> $enabledPartitionIDs
     *
     * @return list<array{
     *     Enabled:bool,
     *     SourceID:string,
     *     Name:string,
     *     PartitionID:string,
     *     ArmHome:bool,
     *     ArmAway:bool,
     *     ArmNight:bool
     * }>
     */
    public static function sources(string $json, array $enabledPartitionIDs): array
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('External alarm sources contain invalid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !array_is_list($decoded)) {
            throw new UnexpectedValueException('External alarm sources must be a JSON list.');
        }

        $knownSourceIDs = [];
        $sources = [];
        foreach ($decoded as $index => $source) {
            if (!is_array($source)) {
                throw new UnexpectedValueException(sprintf('External alarm source %d must be an object.', $index + 1));
            }

            $enabled = $source['Enabled'] ?? true;
            $sourceID = is_string($source['SourceID'] ?? null) ? trim($source['SourceID']) : '';
            $name = is_string($source['Name'] ?? null) ? trim($source['Name']) : '';
            $partitionID = is_string($source['PartitionID'] ?? null) ? strtolower(trim($source['PartitionID'])) : '';
            $armHome = $source['ArmHome'] ?? false;
            $armAway = $source['ArmAway'] ?? true;
            $armNight = $source['ArmNight'] ?? false;

            if (!is_bool($enabled)
                || !is_bool($armHome)
                || !is_bool($armAway)
                || !is_bool($armNight)
            ) {
                throw new UnexpectedValueException(sprintf(
                    'External alarm source %d contains a non-Boolean switch.',
                    $index + 1
                ));
            }
            if (preg_match(self::ID_PATTERN, $sourceID) !== 1) {
                throw new UnexpectedValueException(sprintf(
                    'External alarm source %d has an invalid source ID.',
                    $index + 1
                ));
            }
            if (isset($knownSourceIDs[$sourceID])) {
                throw new UnexpectedValueException(sprintf(
                    'External alarm source ID "%s" is configured more than once.',
                    $sourceID
                ));
            }
            if (!in_array($partitionID, $enabledPartitionIDs, true)) {
                throw new UnexpectedValueException(sprintf(
                    'External alarm source "%s" refers to a missing or disabled alarm partition "%s".',
                    $sourceID,
                    $partitionID
                ));
            }
            if ($enabled && !$armHome && !$armAway && !$armNight) {
                throw new UnexpectedValueException(sprintf(
                    'Enabled external alarm source "%s" must allow at least one arming mode.',
                    $sourceID
                ));
            }

            $knownSourceIDs[$sourceID] = true;
            $sources[] = [
                'Enabled'     => $enabled,
                'SourceID'    => $sourceID,
                'Name'        => $name !== '' ? $name : $sourceID,
                'PartitionID' => $partitionID,
                'ArmHome'     => $armHome,
                'ArmAway'     => $armAway,
                'ArmNight'    => $armNight
            ];
        }

        return $sources;
    }

    /**
     * @return array{ApiVersion:int,EventID:string,SourceID:string,EventType:string,OccurredAt:int}
     */
    public static function event(string $json, int $now): array
    {
        if (strlen($json) > 16_384) {
            throw new UnexpectedValueException('External alarm event exceeds the 16 KiB size limit.');
        }

        try {
            $decoded = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('External alarm event contains invalid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || array_is_list($decoded)) {
            throw new UnexpectedValueException('External alarm event must be a JSON object.');
        }
        if (($decoded['ApiVersion'] ?? null) !== self::API_VERSION) {
            throw new UnexpectedValueException(sprintf(
                'External alarm event ApiVersion must be %d.',
                self::API_VERSION
            ));
        }

        $eventID = is_string($decoded['EventID'] ?? null) ? trim($decoded['EventID']) : '';
        $sourceID = is_string($decoded['SourceID'] ?? null) ? trim($decoded['SourceID']) : '';
        $eventType = is_string($decoded['EventType'] ?? null) ? strtolower(trim($decoded['EventType'])) : '';
        $occurredAt = $decoded['OccurredAt'] ?? null;

        if (preg_match(self::ID_PATTERN, $eventID) !== 1) {
            throw new UnexpectedValueException('External alarm event has an invalid EventID.');
        }
        if (preg_match(self::ID_PATTERN, $sourceID) !== 1) {
            throw new UnexpectedValueException('External alarm event has an invalid SourceID.');
        }
        if ($eventType !== 'alarm') {
            throw new UnexpectedValueException('External alarm event EventType must be "alarm".');
        }
        if (!is_int($occurredAt) || $occurredAt <= 0) {
            throw new UnexpectedValueException('External alarm event OccurredAt must be a Unix timestamp in seconds.');
        }
        if ($occurredAt < $now - self::MAX_EVENT_AGE_SECONDS) {
            throw new UnexpectedValueException('External alarm event is older than the permitted five-minute window.');
        }
        if ($occurredAt > $now + self::MAX_FUTURE_SKEW_SECONDS) {
            throw new UnexpectedValueException('External alarm event timestamp is too far in the future.');
        }

        return [
            'ApiVersion' => self::API_VERSION,
            'EventID'    => $eventID,
            'SourceID'   => $sourceID,
            'EventType'  => 'alarm',
            'OccurredAt' => $occurredAt
        ];
    }

    /**
     * @param list<array{Enabled:bool,SourceID:string,Name:string,PartitionID:string,ArmHome:bool,ArmAway:bool,ArmNight:bool}> $sources
     *
     * @return array{Enabled:bool,SourceID:string,Name:string,PartitionID:string,ArmHome:bool,ArmAway:bool,ArmNight:bool}|null
     */
    public static function source(array $sources, string $sourceID): ?array
    {
        foreach ($sources as $source) {
            if ($source['SourceID'] === $sourceID) {
                return $source;
            }
        }

        return null;
    }

    /** @param array{ArmHome:bool,ArmAway:bool,ArmNight:bool} $source */
    public static function allowsMode(array $source, int $mode): bool
    {
        return match ($mode) {
            AlarmStateMachine::MODE_HOME  => $source['ArmHome'],
            AlarmStateMachine::MODE_AWAY  => $source['ArmAway'],
            AlarmStateMachine::MODE_NIGHT => $source['ArmNight'],
            default                       => false
        };
    }

    public static function receiptKey(string $sourceID, string $eventID): string
    {
        return hash('sha256', $sourceID . "\0" . $eventID);
    }

    /**
     * @param array<array-key,mixed> $receipts
     *
     * @return array<string,int>
     */
    public static function receipts(array $receipts, int $now): array
    {
        $normalized = [];
        foreach ($receipts as $key => $timestamp) {
            if (!is_string($key)
                || preg_match('/^[a-f0-9]{64}$/', $key) !== 1
                || !is_int($timestamp)
                || $timestamp < $now - self::RECEIPT_RETENTION_SECONDS
                || $timestamp > $now + self::MAX_FUTURE_SKEW_SECONDS
            ) {
                continue;
            }
            $normalized[$key] = $timestamp;
        }
        arsort($normalized, SORT_NUMERIC);

        return array_slice($normalized, 0, self::RECEIPT_LIMIT, true);
    }
}
