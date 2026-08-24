<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Formats the stable, presentation-independent control API payload.
 */
final class AlarmControlStateAdapter
{
    /**
     * @return array{Mode:array{Value:int,Name:string},State:array{Value:int,Name:string}}
     */
    public static function identity(int $mode, int $state): array
    {
        return [
            'Mode'  => ['Value' => $mode, 'Name' => AlarmStateMachine::modeName($mode)],
            'State' => ['Value' => $state, 'Name' => AlarmStateMachine::stateName($state)]
        ];
    }

    /**
     * @return array{CodeRequired:bool,CanDisarm:bool,CanManageBypasses:bool,CanResetAlarmOutput:bool,CanClearAlarmMemory:bool}
     */
    public static function capabilities(
        int $mode,
        int $state,
        bool $codeRequired,
        bool $alarmMemory,
        bool $alarmOutputActive
    ): array {
        $isDisarmed = $state === AlarmStateMachine::STATE_DISARMED;

        return [
            'CodeRequired'        => $codeRequired,
            'CanDisarm'           => !$isDisarmed || $mode !== AlarmStateMachine::MODE_NONE,
            'CanManageBypasses'   => $isDisarmed,
            'CanResetAlarmOutput' => $state === AlarmStateMachine::STATE_ALARM && $alarmOutputActive,
            'CanClearAlarmMemory' => $alarmMemory && $state !== AlarmStateMachine::STATE_ALARM
        ];
    }

    /** @param array<string,mixed> $state */
    public static function withInteraction(array $state, ?array $interaction): array
    {
        if ($interaction !== null) {
            $state['Interaction'] = $interaction;
        }

        return $state;
    }

    /** @param array<string,mixed> $payload */
    public static function encode(array $payload): string
    {
        return json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
