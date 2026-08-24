<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use JsonException;
use Throwable;

/**
 * Validates and executes one optional Symcon action behind an injected runner.
 */
final class AlarmActionExecutor
{
    /**
     * @param callable(string,array): bool $runner
     *
     * @return array{Succeeded:bool,Error:?string}
     */
    public static function execute(bool $enabled, string $encodedAction, callable $runner): array
    {
        if (!$enabled) {
            return self::success();
        }

        $encodedAction = trim($encodedAction);
        if ($encodedAction === '' || $encodedAction === '{}' || $encodedAction === 'false' || $encodedAction === 'null') {
            return self::success();
        }

        try {
            $action = json_decode($encodedAction, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return self::failure('Invalid configured action: ' . $exception->getMessage());
        }

        if ($action === false || $action === null) {
            return self::success();
        }
        if (!is_array($action)) {
            return self::failure('Configured action must decode to an object.');
        }

        $actionID = $action['actionID'] ?? null;
        $parameters = $action['parameters'] ?? null;
        if (!is_string($actionID) || $actionID === '' || !is_array($parameters)) {
            return self::failure('Configured action is missing actionID or parameters.');
        }

        try {
            $executed = $runner($actionID, $parameters);
        } catch (Throwable $exception) {
            return self::failure('Configured action failed: ' . $exception->getMessage());
        }

        return $executed
            ? self::success()
            : self::failure('Configured action could not be started.');
    }

    /**
     * @return array{Succeeded:true,Error:null}
     */
    private static function success(): array
    {
        return ['Succeeded' => true, 'Error' => null];
    }

    /**
     * @return array{Succeeded:false,Error:string}
     */
    private static function failure(string $error): array
    {
        return ['Succeeded' => false, 'Error' => $error];
    }
}
