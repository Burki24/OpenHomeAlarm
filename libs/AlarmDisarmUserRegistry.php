<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use JsonException;
use UnexpectedValueException;

/**
 * Normalizes named disarm users and resolves submitted codes without exposing them.
 */
final class AlarmDisarmUserRegistry
{
    /** @return list<array{Enabled:bool,Name:string,Code:string}> */
    public static function users(string $encodedUsers): array
    {
        if (trim($encodedUsers) === '') {
            return [];
        }

        try {
            $users = json_decode($encodedUsers, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Invalid disarm user configuration JSON.', 0, $exception);
        }
        if (!is_array($users) || !array_is_list($users)) {
            throw new UnexpectedValueException('Disarm user configuration must be a list.');
        }

        $normalized = [];
        $enabledCodes = [];
        foreach ($users as $index => $user) {
            if (!is_array($user)) {
                throw new UnexpectedValueException('Every disarm user configuration must be an object.');
            }
            $enabled = $user['Enabled'] ?? true;
            $name = $user['Name'] ?? '';
            $code = $user['Code'] ?? '';
            if (!is_bool($enabled) || !is_string($name) || !is_string($code)) {
                throw new UnexpectedValueException('Invalid disarm user field type.');
            }
            $name = trim($name);
            $code = trim($code);
            if ($name === '') {
                $name = sprintf('User %d', $index + 1);
            }
            if ($code !== '' && preg_match('/^[0-9]{4,8}$/', $code) !== 1) {
                throw new UnexpectedValueException('Disarm user code must contain 4 to 8 digits.');
            }
            if ($enabled && $code !== '') {
                if (isset($enabledCodes[$code])) {
                    throw new UnexpectedValueException('Enabled disarm user codes must be unique.');
                }
                $enabledCodes[$code] = true;
            }
            $normalized[] = ['Enabled' => $enabled, 'Name' => $name, 'Code' => $code];
        }

        return $normalized;
    }

    /** @param list<array{Enabled:bool,Name:string,Code:string}> $users */
    public static function matchingUser(string $submittedCode, array $users): ?string
    {
        $submittedCode = trim($submittedCode);
        foreach ($users as $user) {
            if ($user['Enabled'] && $user['Code'] !== '' && hash_equals($user['Code'], $submittedCode)) {
                return $user['Name'];
            }
        }

        return null;
    }

    /** @param list<array{Enabled:bool,Name:string,Code:string}> $users */
    public static function hasEnabledCode(array $users): bool
    {
        foreach ($users as $user) {
            if ($user['Enabled'] && $user['Code'] !== '') {
                return true;
            }
        }

        return false;
    }
}
