<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use InvalidArgumentException;
use JsonException;

/** Creates and validates portable, versioned OpenHomeAlarm configuration backups. */
final class AlarmConfigurationBackup
{
    public const FORMAT = 'OpenHomeAlarm.ConfigurationBackup';
    public const VERSION = 1;
    public const MODULE_ID = '{763D545E-C8B3-A3E2-130A-006A10D6181F}';

    /** @param array<string,bool|int|float|string> $configuration */
    public static function create(array $configuration, int $exportedAt): array
    {
        self::validateConfiguration($configuration);
        ksort($configuration);

        return [
            'Format'          => self::FORMAT,
            'Version'         => self::VERSION,
            'ModuleID'        => self::MODULE_ID,
            'ExportedAt'      => max(0, $exportedAt),
            'ContainsSecrets' => true,
            'Configuration'   => $configuration
        ];
    }

    /** @param array<string,mixed> $backup */
    public static function encode(array $backup): string
    {
        return json_encode(
            $backup,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /** @return array<string,mixed> */
    public static function decode(string $json): array
    {
        try {
            $backup = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Configuration backup must be valid JSON.', 0, $exception);
        }
        if (!is_array($backup)) {
            throw new InvalidArgumentException('Configuration backup must be a JSON object.');
        }
        if (($backup['Format'] ?? null) !== self::FORMAT) {
            throw new InvalidArgumentException('Configuration backup format is not supported.');
        }
        if (($backup['Version'] ?? null) !== self::VERSION) {
            throw new InvalidArgumentException('Configuration backup version is not supported.');
        }
        if (($backup['ModuleID'] ?? null) !== self::MODULE_ID) {
            throw new InvalidArgumentException('Configuration backup belongs to another module.');
        }
        if (!isset($backup['Configuration']) || !is_array($backup['Configuration'])) {
            throw new InvalidArgumentException('Configuration backup does not contain a configuration object.');
        }

        self::validateConfiguration($backup['Configuration']);

        return $backup;
    }

    /**
     * @param array<string,mixed> $backup
     * @param array<string,bool|int|float|string> $current
     *
     * @return array<string,bool|int|float|string>
     */
    public static function configurationForRestore(array $backup, array $current): array
    {
        self::validateConfiguration($current);
        $configuration = $backup['Configuration'] ?? null;
        if (!is_array($configuration)) {
            throw new InvalidArgumentException('Configuration backup does not contain a configuration object.');
        }
        self::validateConfiguration($configuration);

        foreach ($configuration as $name => $value) {
            if (!array_key_exists($name, $current)) {
                throw new InvalidArgumentException('Configuration backup contains an unknown property: ' . $name . '.');
            }
            if (get_debug_type($value) !== get_debug_type($current[$name])) {
                throw new InvalidArgumentException('Configuration backup property type does not match: ' . $name . '.');
            }
        }

        return array_replace($current, $configuration);
    }

    /** @param array<mixed,mixed> $configuration */
    private static function validateConfiguration(array $configuration): void
    {
        foreach ($configuration as $name => $value) {
            if (!is_string($name) || $name === '') {
                throw new InvalidArgumentException('Configuration backup property names must be non-empty strings.');
            }
            if (!is_bool($value) && !is_int($value) && !is_float($value) && !is_string($value)) {
                throw new InvalidArgumentException('Configuration backup properties must contain scalar values.');
            }
        }
    }
}
