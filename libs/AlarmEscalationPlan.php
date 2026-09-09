<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use JsonException;
use UnexpectedValueException;

/** Validates the configured notification steps of one alarm escalation plan. */
final class AlarmEscalationPlan
{
    public const MAX_DELAY_SECONDS = 86400;

    /** @return list<array{Enabled:bool,Name:string,DelaySeconds:int,Actions:list<array{Enabled:bool,Name:string,Action:string,ResetMode:int,ResetAction:string,SignalGenerator:bool}>}> */
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

            $actions = self::normalizeActions(
                $step['Actions'] ?? null,
                $step['Action'] ?? null,
                $step['ResetEnabled'] ?? false,
                $step['ResetMode'] ?? null,
                $step['ResetAction'] ?? '',
                $step['SignalGenerator'] ?? false,
                $index,
                $enabled,
                $name
            );
            if ($enabled && !array_filter($actions, static fn (array $action): bool => $action['Enabled'])) {
                throw new UnexpectedValueException('Enabled alarm escalation steps require at least one enabled action.');
            }

            $name = trim($name);
            $normalized[] = [
                'Enabled'      => $enabled,
                'Name'         => $name !== '' ? $name : sprintf('Step %d', $index + 1),
                'DelaySeconds' => $delaySeconds,
                'Actions'      => $actions
            ];
        }

        return $normalized;
    }

    /** @return array{StartedAt:int,ExecutedStepKeys:list<string>,ResetActionKeys:list<string>,ExecutedActions:list<array<string,mixed>>} */
    public static function start(int $timestamp): array
    {
        return ['StartedAt' => max(1, $timestamp), 'ExecutedStepKeys' => [], 'ResetActionKeys' => [], 'ExecutedActions' => []];
    }

    /**
     * @param array<array-key,mixed> $stored
     *
     * @return array{StartedAt:int,ExecutedStepKeys:list<string>,ResetActionKeys:list<string>,ExecutedActions:list<array<string,mixed>>}|null
     */
    public static function runtime(array $stored): ?array
    {
        if ($stored === []) {
            return null;
        }
        $startedAt = $stored['StartedAt'] ?? null;
        $executedStepKeys = $stored['ExecutedStepKeys'] ?? null;
        if (!is_int($startedAt) || $startedAt <= 0 || !is_array($executedStepKeys)) {
            throw new UnexpectedValueException('Invalid alarm escalation runtime state.');
        }

        $normalizedKeys = [];
        foreach ($executedStepKeys as $key) {
            if (!is_string($key) || $key === '') {
                throw new UnexpectedValueException('Invalid executed alarm escalation step key.');
            }
            if (!in_array($key, $normalizedKeys, true)) {
                $normalizedKeys[] = $key;
            }
        }

        $resetActionKeys = self::normalizeKeys($stored['ResetActionKeys'] ?? [], 'reset alarm escalation action');
        $executedActions = [];
        foreach ($stored['ExecutedActions'] ?? [] as $executedAction) {
            if (!is_array($executedAction)
                || !is_string($executedAction['Key'] ?? null)
                || !is_string($executedAction['StepName'] ?? null)
                || !is_string($executedAction['ActionName'] ?? null)
                || (!is_int($executedAction['ResetMode'] ?? null) && !is_bool($executedAction['ResetEnabled'] ?? null))
                || !is_string($executedAction['ResetAction'] ?? '')
                || !is_string($executedAction['Action'] ?? null)) {
                throw new UnexpectedValueException('Invalid executed alarm escalation action.');
            }
            $executedAction['ResetMode'] = is_int($executedAction['ResetMode'] ?? null)
                ? $executedAction['ResetMode']
                : (($executedAction['ResetEnabled'] ?? false) ? 1 : 0);
            if (!in_array($executedAction['ResetMode'], [0, 1, 2], true)) {
                throw new UnexpectedValueException('Unsupported alarm escalation reset mode.');
            }
            $executedAction['ResetAction'] ??= '';
            $executedAction['SignalGenerator'] = $executedAction['SignalGenerator'] ?? false;
            if (!is_bool($executedAction['SignalGenerator'])) {
                throw new UnexpectedValueException('Invalid alarm signal generator flag.');
            }
            $executedActions[] = $executedAction;
        }

        return [
            'StartedAt'         => $startedAt,
            'ExecutedStepKeys'  => $normalizedKeys,
            'ResetActionKeys'   => $resetActionKeys,
            'ExecutedActions'   => $executedActions
        ];
    }

    /** @param array<string,mixed> $step */
    public static function stepKey(array $step, int $index): string
    {
        return hash('sha256', json_encode([$index, $step], JSON_THROW_ON_ERROR));
    }

    /** @param array<string,mixed> $action */
    public static function actionKey(array $step, int $stepIndex, array $action, int $actionIndex): string
    {
        return hash('sha256', json_encode([$stepIndex, $step['Name'], $step['DelaySeconds'], $actionIndex, $action], JSON_THROW_ON_ERROR));
    }

    /**
     * @param list<array<string,mixed>>                                        $steps
     * @param array<string,mixed> $runtime
     *
     * @return list<array{Key:string,Step:array<string,mixed>,Action:array<string,mixed>}>
     */
    public static function dueSteps(array $steps, array $runtime, int $timestamp): array
    {
        $due = [];
        foreach ($steps as $index => $step) {
            if (!$step['Enabled'] || $runtime['StartedAt'] + $step['DelaySeconds'] > $timestamp) {
                continue;
            }
            foreach ($step['Actions'] as $actionIndex => $action) {
                if (!$action['Enabled']) {
                    continue;
                }
                $key = self::actionKey($step, $index, $action, $actionIndex);
                if (!in_array($key, $runtime['ExecutedStepKeys'], true)) {
                    $due[] = ['Key' => $key, 'Step' => $step, 'Action' => $action];
                }
            }
        }

        return $due;
    }

    /**
     * @param list<array<string,mixed>> $steps
     * @param array<string,mixed>       $runtime
     */
    public static function nextDeadline(array $steps, array $runtime): int
    {
        $deadline = 0;
        foreach ($steps as $index => $step) {
            if (!$step['Enabled']) {
                continue;
            }
            $pending = false;
            foreach ($step['Actions'] as $actionIndex => $action) {
                if ($action['Enabled'] && !in_array(self::actionKey($step, $index, $action, $actionIndex), $runtime['ExecutedStepKeys'], true)) {
                    $pending = true;
                    break;
                }
            }
            if (!$pending) {
                continue;
            }
            $stepDeadline = $runtime['StartedAt'] + $step['DelaySeconds'];
            if ($deadline === 0 || $stepDeadline < $deadline) {
                $deadline = $stepDeadline;
            }
        }

        return $deadline;
    }

    public static function inverseAction(string $encodedAction): string
    {
        $action = json_decode($encodedAction, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($action) || !is_bool($action['parameters']['VALUE'] ?? null)) {
            return '';
        }
        $action['parameters']['VALUE'] = !$action['parameters']['VALUE'];

        return json_encode($action, JSON_THROW_ON_ERROR);
    }

    /** @param array{ResetMode:int,ResetAction:string,Action:string} $executedAction */
    public static function resetAction(array $executedAction): string
    {
        return match ($executedAction['ResetMode']) {
            0       => '',
            1       => self::inverseAction($executedAction['Action']),
            2       => $executedAction['ResetAction'],
            default => throw new UnexpectedValueException('Unsupported alarm escalation reset mode.')
        };
    }

    /** @return list<array{Enabled:bool,Name:string,Action:string,ResetMode:int,ResetAction:string,SignalGenerator:bool}> */
    private static function normalizeActions(
        mixed $configured,
        mixed $flatAction,
        mixed $flatLegacyResetEnabled,
        mixed $flatResetMode,
        mixed $flatResetAction,
        mixed $flatSignalGenerator,
        int $stepIndex,
        bool $stepEnabled,
        string $stepName
    ): array {
        if ($configured === null && $flatAction !== null) {
            if (!is_bool($flatLegacyResetEnabled)) {
                throw new UnexpectedValueException('Invalid automatic reset field type.');
            }
            $normalizedFlatAction = self::normalizeAction($flatAction);
            if ($normalizedFlatAction === '') {
                return [];
            }
            $configured = [[
                'Enabled'         => true,
                'Name'            => trim($stepName) !== '' ? trim($stepName) : sprintf('Action %d', $stepIndex + 1),
                'Action'          => $normalizedFlatAction,
                'ResetEnabled'    => $flatLegacyResetEnabled,
                'ResetMode'       => $flatResetMode ?? ($flatLegacyResetEnabled ? 1 : 0),
                'ResetAction'     => $flatResetAction,
                'SignalGenerator' => $flatSignalGenerator
            ]];
        }
        if (is_string($configured)) {
            try {
                $configured = json_decode($configured, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new UnexpectedValueException('Invalid alarm escalation actions JSON.', 0, $exception);
            }
        }
        if (!is_array($configured) || !array_is_list($configured)) {
            throw new UnexpectedValueException('Alarm escalation actions must be a list.');
        }
        $actions = [];
        foreach ($configured as $index => $entry) {
            if (!is_array($entry)) {
                throw new UnexpectedValueException('Every alarm escalation action must be an object.');
            }
            $enabled = $entry['Enabled'] ?? true;
            $name = $entry['Name'] ?? '';
            $legacyResetEnabled = $entry['ResetEnabled'] ?? false;
            $resetMode = $entry['ResetMode'] ?? ($legacyResetEnabled ? 1 : 0);
            $signalGenerator = $entry['SignalGenerator'] ?? false;
            if (!is_bool($enabled) || !is_string($name) || !is_bool($legacyResetEnabled) || !is_int($resetMode) || !is_bool($signalGenerator)) {
                throw new UnexpectedValueException('Invalid alarm escalation action field type.');
            }
            if (!in_array($resetMode, [0, 1, 2], true)) {
                throw new UnexpectedValueException('Unsupported alarm escalation reset mode.');
            }
            $action = self::normalizeAction($entry['Action'] ?? '');
            $resetAction = self::normalizeAction($entry['ResetAction'] ?? '');
            if ($stepEnabled && $enabled && $action === '') {
                throw new UnexpectedValueException('Enabled alarm escalation actions require an action.');
            }
            if ($stepEnabled && $enabled && $resetMode === 1 && $action !== '' && self::inverseAction($action) === '') {
                throw new UnexpectedValueException(
                    'Automatic inverse reset requires a Boolean set-value action. Select a custom reset action for this action type.'
                );
            }
            if ($stepEnabled && $enabled && $resetMode === 2 && $resetAction === '') {
                throw new UnexpectedValueException('A custom reset mode requires a reset action.');
            }
            if ($stepEnabled && $enabled && $signalGenerator && $resetMode === 0) {
                throw new UnexpectedValueException('Signal generators require an automatic or custom reset action.');
            }
            $actions[] = [
                'Enabled'         => $enabled,
                'Name'            => trim($name) !== '' ? trim($name) : sprintf('Action %d.%d', $stepIndex + 1, $index + 1),
                'Action'          => $action,
                'ResetMode'       => $resetMode,
                'ResetAction'     => $resetAction,
                'SignalGenerator' => $signalGenerator
            ];
        }
        return $actions;
    }

    /** @return list<string> */
    private static function normalizeKeys(mixed $keys, string $context): array
    {
        if (!is_array($keys)) {
            throw new UnexpectedValueException(sprintf('Invalid executed %s key list.', $context));
        }
        $normalized = [];
        foreach ($keys as $key) {
            if (!is_string($key) || $key === '') {
                throw new UnexpectedValueException(sprintf('Invalid executed %s key.', $context));
            }
            if (!in_array($key, $normalized, true)) {
                $normalized[] = $key;
            }
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
