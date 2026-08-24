<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use JsonException;
use UnexpectedValueException;

/**
 * Validates and normalizes persisted alarm sensor and fault configurations.
 */
final class AlarmConfigurationNormalizer
{
    /**
     * @param list<int> $validSensorTypes
     *
     * @return list<array{
     *     Enabled: bool,
     *     PartitionID: string,
     *     Name: string,
     *     VariableID: int,
     *     SensorType: int,
     *     TriggerValue: string,
     *     ArmHome: bool,
     *     ArmAway: bool,
     *     ArmNight: bool,
     *     AlwaysActive: bool,
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }>
     */
    public static function sensors(
        string $encodedSensors,
        array $validSensorTypes,
        int $defaultSensorType
    ): array {
        try {
            $sensors = json_decode($encodedSensors, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Invalid sensor configuration JSON.', 0, $exception);
        }

        if (!is_array($sensors) || !array_is_list($sensors)) {
            throw new UnexpectedValueException('Sensor configuration must be a list.');
        }

        $normalizedSensors = [];
        foreach ($sensors as $sensor) {
            if (!is_array($sensor)) {
                throw new UnexpectedValueException('Every sensor configuration must be an object.');
            }

            $variableID = self::integerField($sensor, 'VariableID', 0, 'Sensor');
            if ($variableID < 0) {
                throw new UnexpectedValueException('Sensor VariableID must not be negative.');
            }

            $sensorType = self::integerField($sensor, 'SensorType', $defaultSensorType, 'Sensor');
            if (!in_array($sensorType, $validSensorTypes, true)) {
                throw new UnexpectedValueException('Unsupported sensor type.');
            }

            $normalizedSensors[] = [
                'Enabled'      => self::booleanField($sensor, 'Enabled', true, 'Sensor'),
                'PartitionID'  => strtolower(trim(self::stringField($sensor, 'PartitionID', '', 'Sensor'))),
                'Name'         => trim(self::stringField($sensor, 'Name', '', 'Sensor')),
                'VariableID'   => $variableID,
                'SensorType'   => $sensorType,
                'TriggerValue' => self::stringField($sensor, 'TriggerValue', '1', 'Sensor'),
                'ArmHome'      => self::booleanField($sensor, 'ArmHome', false, 'Sensor'),
                'ArmAway'      => self::booleanField($sensor, 'ArmAway', true, 'Sensor'),
                'ArmNight'     => self::booleanField($sensor, 'ArmNight', false, 'Sensor'),
                'AlwaysActive' => self::booleanField($sensor, 'AlwaysActive', false, 'Sensor'),
                'ExitDelay'    => self::booleanField($sensor, 'ExitDelay', false, 'Sensor'),
                'EntryDelay'   => self::booleanField($sensor, 'EntryDelay', false, 'Sensor')
            ];
        }

        return $normalizedSensors;
    }

    /**
     * @param list<int> $validFaultTypes
     *
     * @return list<array{
     *     Enabled: bool,
     *     PartitionID: string,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * }>
     */
    public static function faultInputs(
        string $encodedFaultInputs,
        array $validFaultTypes,
        int $defaultFaultType
    ): array {
        if (trim($encodedFaultInputs) === '') {
            return [];
        }

        try {
            $faultInputs = json_decode($encodedFaultInputs, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Invalid fault input configuration JSON.', 0, $exception);
        }

        if (!is_array($faultInputs) || !array_is_list($faultInputs)) {
            throw new UnexpectedValueException('Fault input configuration must be a list.');
        }

        $normalizedFaultInputs = [];
        $usedVariableIDs = [];
        foreach ($faultInputs as $faultInput) {
            if (!is_array($faultInput)) {
                throw new UnexpectedValueException('Every fault input configuration must be an object.');
            }

            $variableID = self::integerField($faultInput, 'VariableID', 0, 'Fault input');
            if ($variableID < 0) {
                throw new UnexpectedValueException('Fault input VariableID must not be negative.');
            }

            $faultType = self::integerField($faultInput, 'FaultType', $defaultFaultType, 'Fault input');
            if (!in_array($faultType, $validFaultTypes, true)) {
                throw new UnexpectedValueException('Unsupported fault input type.');
            }

            $normalized = [
                'Enabled'      => self::booleanField($faultInput, 'Enabled', true, 'Fault input'),
                'PartitionID'  => strtolower(trim(self::stringField($faultInput, 'PartitionID', '', 'Fault input'))),
                'Name'         => trim(self::stringField($faultInput, 'Name', '', 'Fault input')),
                'VariableID'   => $variableID,
                'FaultType'    => $faultType,
                'TriggerValue' => self::stringField($faultInput, 'TriggerValue', '1', 'Fault input'),
                'BlockArming'  => self::booleanField($faultInput, 'BlockArming', false, 'Fault input'),
                'TriggerAlarm' => self::booleanField($faultInput, 'TriggerAlarm', false, 'Fault input')
            ];
            if ($normalized['VariableID'] > 0) {
                if (isset($usedVariableIDs[$normalized['VariableID']])) {
                    throw new UnexpectedValueException('A fault input variable can only be configured once.');
                }
                $usedVariableIDs[$normalized['VariableID']] = true;
            }
            $normalizedFaultInputs[] = $normalized;
        }

        return $normalizedFaultInputs;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private static function booleanField(
        array $configuration,
        string $key,
        bool $default,
        string $context
    ): bool {
        $value = $configuration[$key] ?? $default;
        if (!is_bool($value)) {
            throw new UnexpectedValueException(sprintf('%s field %s must be boolean.', $context, $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private static function integerField(
        array $configuration,
        string $key,
        int $default,
        string $context
    ): int {
        $value = $configuration[$key] ?? $default;
        if (!is_int($value)) {
            throw new UnexpectedValueException(sprintf('%s field %s must be integer.', $context, $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private static function stringField(
        array $configuration,
        string $key,
        string $default,
        string $context
    ): string {
        $value = $configuration[$key] ?? $default;
        if (!is_string($value)) {
            throw new UnexpectedValueException(sprintf('%s field %s must be string.', $context, $key));
        }

        return $value;
    }
}
