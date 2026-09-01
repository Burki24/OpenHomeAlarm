<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use JsonException;
use UnexpectedValueException;

/** Validates the configured notification steps of one alarm escalation plan. */
final class AlarmEscalationPlan
{
    public const MAX_DELAY_SECONDS = 86400;

    /**
     * @return list<array{Enabled:bool,Name:string,DelaySeconds:int,Action:string}>
     */
    public static function steps(string $encodedSteps): array
    {
        $encodedSteps = trim($encodedSteps);
        if ($encodedSteps === '') {
            return [];
        }
        if (!str_starts_with($encodedSteps, '[')) {
            throw new UnexpectedValueException('Alarm escalation steps must be a list.');
        }

        try {
            $steps = json_decode($encodedSteps, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Invalid alarm escalation configuration JSON.', 0, $exception);
        }
        if (!is_array($steps) || !array_is_list($steps)) {
            throw new UnexpectedValueException('Alarm escalation steps must be a list.');
        }

        $normalized = [];
        foreach ($steps as $index => $step) {
            if (!is_array($step)) {
                throw new UnexpectedValueException('Every alarm escalation step must be an object.');
            }

            $enabled = $step['Enabled'] ?? true;
            $name = $step['Name'] ?? '';
            $delaySeconds = $step['DelaySeconds'] ?? 0;
            if (!is_bool($enabled) || !is_string($name) || !is_int($delaySeconds)) {
                throw new UnexpectedValueException('Invalid alarm escalation step field type.');
            }
            if ($delaySeconds < 0 || $delaySeconds > self::MAX_DELAY_SECONDS) {
                throw new UnexpectedValueException('Alarm escalation delay must be between 0 and 86400 seconds.');
            }

            $action = self::normalizeAction($step['Action'] ?? '');
            if ($enabled && $action === '') {
                throw new UnexpectedValueException('Enabled alarm escalation steps require an action.');
            }

            $name = trim($name);
            $normalized[] = [
                'Enabled'      => $enabled,
                'Name'         => $name !== '' ? $name : sprintf('Step %d', $index + 1),
                'DelaySeconds' => $delaySeconds,
                'Action'       => $action
            ];
        }

        return $normalized;
    }

    private static function normalizeAction(mixed $action): string
    {
        if ($action === '' || $action === false || $action === null) {
            return '';
        }
        if (is_string($action)) {
            $action = trim($action);
            if ($action === '' || $action === 'false' || $action === 'null' || $action === '{}') {
                return '';
            }
            try {
                $action = json_decode($action, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new UnexpectedValueException('Invalid alarm escalation action JSON.', 0, $exception);
            }
        }
        if (!is_array($action)) {
            throw new UnexpectedValueException('Alarm escalation action must be an object.');
        }
        if (
            !is_string($action['actionID'] ?? null)
            || $action['actionID'] === ''
            || !is_array($action['parameters'] ?? null)
        ) {
            throw new UnexpectedValueException('Alarm escalation action is missing actionID or parameters.');
        }

        return json_encode($action, JSON_THROW_ON_ERROR);
    }
}
