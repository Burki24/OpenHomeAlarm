<?php

declare(strict_types=1);

use Burki24\OpenHomeAlarm\AlarmExternalEvent;
use Burki24\OpenHomeAlarm\AlarmStateMachine;

require_once dirname(__DIR__) . '/libs/AlarmStateMachine.php';
require_once dirname(__DIR__) . '/libs/AlarmExternalEvent.php';

function assertExternalEvent(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expectExternalEventFailure(callable $operation, string $messagePart): void
{
    try {
        $operation();
    } catch (UnexpectedValueException $exception) {
        assertExternalEvent(
            str_contains($exception->getMessage(), $messagePart),
            sprintf('Expected error containing "%s", got "%s".', $messagePart, $exception->getMessage())
        );

        return;
    }

    throw new RuntimeException(sprintf('Expected an external event error containing "%s".', $messagePart));
}

$sources = AlarmExternalEvent::sources(json_encode([
    [
        'Enabled'     => true,
        'SourceID'    => 'frigate.front-door.person',
        'Name'        => 'Person at front door',
        'PartitionID' => 'house',
        'ArmHome'     => false,
        'ArmAway'     => true,
        'ArmNight'    => true
    ],
    [
        'Enabled'     => false,
        'SourceID'    => 'blueiris.driveway',
        'Name'        => '',
        'PartitionID' => 'outside'
    ]
], JSON_THROW_ON_ERROR), ['main', 'house', 'outside']);
assertExternalEvent(count($sources) === 2, 'Configured external alarm sources must be retained.');
assertExternalEvent(
    $sources[1]['Name'] === 'blueiris.driveway'
        && $sources[1]['ArmAway']
        && !$sources[1]['ArmHome']
        && !$sources[1]['ArmNight'],
    'External source defaults must be safe and stable.'
);
assertExternalEvent(
    AlarmExternalEvent::source($sources, 'frigate.front-door.person') === $sources[0]
        && AlarmExternalEvent::source($sources, 'unknown') === null,
    'External sources must resolve only by their exact stable ID.'
);
assertExternalEvent(
    !AlarmExternalEvent::allowsMode($sources[0], AlarmStateMachine::MODE_HOME)
        && AlarmExternalEvent::allowsMode($sources[0], AlarmStateMachine::MODE_AWAY)
        && AlarmExternalEvent::allowsMode($sources[0], AlarmStateMachine::MODE_NIGHT),
    'Each external source must enforce its configured arming modes.'
);

expectExternalEventFailure(
    static fn (): array => AlarmExternalEvent::sources('[{"SourceID":"duplicate","PartitionID":"main"},{"SourceID":"duplicate","PartitionID":"main"}]', ['main']),
    'configured more than once'
);
expectExternalEventFailure(
    static fn (): array => AlarmExternalEvent::sources('[{"SourceID":"missing","PartitionID":"garage"}]', ['main']),
    'missing or disabled alarm partition'
);
expectExternalEventFailure(
    static fn (): array => AlarmExternalEvent::sources('[{"SourceID":"inactive","PartitionID":"main","ArmAway":false}]', ['main']),
    'at least one arming mode'
);

$now = 1_800_000_000;
$event = AlarmExternalEvent::event(json_encode([
    'ApiVersion' => 1,
    'EventID'    => '0f97293e-19e4-4aa1-a0d4-588b8cfb0ea5',
    'SourceID'   => 'frigate.front-door.person',
    'EventType'  => 'alarm',
    'OccurredAt' => $now - 2,
    'Extension'  => ['ignored' => true]
], JSON_THROW_ON_ERROR), $now);
assertExternalEvent(
    $event === [
        'ApiVersion' => 1,
        'EventID'    => '0f97293e-19e4-4aa1-a0d4-588b8cfb0ea5',
        'SourceID'   => 'frigate.front-door.person',
        'EventType'  => 'alarm',
        'OccurredAt' => $now - 2
    ],
    'A valid alarm event must be normalized while unknown additive fields remain harmless.'
);

foreach ([
    [['ApiVersion' => 2, 'EventID' => 'event-1', 'SourceID' => 'source-1', 'EventType' => 'alarm', 'OccurredAt' => $now], 'ApiVersion must be 1'],
    [['ApiVersion' => 1, 'EventID' => 'event 1', 'SourceID' => 'source-1', 'EventType' => 'alarm', 'OccurredAt' => $now], 'invalid EventID'],
    [['ApiVersion' => 1, 'EventID' => 'event-1', 'SourceID' => 'source-1', 'EventType' => 'motion', 'OccurredAt' => $now], 'EventType must be "alarm"'],
    [['ApiVersion' => 1, 'EventID' => 'event-1', 'SourceID' => 'source-1', 'EventType' => 'alarm', 'OccurredAt' => $now - 301], 'older than'],
    [['ApiVersion' => 1, 'EventID' => 'event-1', 'SourceID' => 'source-1', 'EventType' => 'alarm', 'OccurredAt' => $now + 61], 'too far in the future']
] as [$invalidEvent, $errorPart]) {
    expectExternalEventFailure(
        static fn (): array => AlarmExternalEvent::event(json_encode($invalidEvent, JSON_THROW_ON_ERROR), $now),
        $errorPart
    );
}

$receiptKey = AlarmExternalEvent::receiptKey('source-1', 'event-1');
assertExternalEvent(strlen($receiptKey) === 64, 'Receipt keys must not expose provider event identifiers.');
$receipts = AlarmExternalEvent::receipts([
    $receiptKey         => $now - 10,
    str_repeat('a', 64) => $now - AlarmExternalEvent::RECEIPT_RETENTION_SECONDS - 1,
    'invalid'           => $now,
    str_repeat('b', 64) => 'not-a-timestamp'
], $now);
assertExternalEvent(
    $receipts === [$receiptKey => $now - 10],
    'Only well-formed, recent replay receipts may survive restart normalization.'
);

$form = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$formJSON = json_encode($form, JSON_THROW_ON_ERROR);
assertExternalEvent(
    str_contains($formJSON, 'External alarm event interface')
        && str_contains($formJSON, 'ExternalAlarmEventApiEnabled')
        && str_contains($formJSON, 'ExternalAlarmEventSources')
        && str_contains($formJSON, 'ExternalAlarmEventHook')
        && str_contains($formJSON, 'ExternalAlarmEventToken')
        && str_contains($formJSON, 'X-OpenHomeAlarm-Token'),
    'The instance form must expose enablement, approved sources and confidential connection data.'
);
$status206 = array_values(array_filter(
    $form['status'] ?? [],
    static fn (mixed $status): bool => is_array($status) && ($status['code'] ?? null) === 206
));
assertExternalEvent(
    count($status206) === 1
        && ($status206[0]['icon'] ?? null) === 'error'
        && str_contains((string) ($status206[0]['caption'] ?? ''), 'unique source IDs'),
    'Invalid external source permissions need a dedicated actionable instance status.'
);

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertExternalEvent(
    str_contains($moduleSource, 'private const CONTROL_API_VERSION = 3;')
        && AlarmExternalEvent::API_VERSION === 1
        && str_contains($moduleSource, "return 'openhomealarm-events/'")
        && str_contains($moduleSource, "'HTTP_X_OPENHOMEALARM_TOKEN'")
        && str_contains($moduleSource, 'Content-Type must be application/json')
        && str_contains($moduleSource, 'public function TriggerExternalAlarmEvent(string $eventJSON): string')
        && str_contains($moduleSource, 'private function ProcessExternalAlarmEvent(array $event): array'),
    'The external event transport must remain separate from Control API 3 and reuse one validated alarm path.'
);

$moduleReadme = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/README.md');
$rootReadme = (string) file_get_contents(dirname(__DIR__) . '/README.md');
assertExternalEvent(
    str_contains($moduleReadme, 'OHA_GetExternalAlarmEventApi')
        && str_contains($moduleReadme, 'OHA_TriggerExternalAlarmEvent')
        && str_contains($moduleReadme, 'OHA_RotateExternalAlarmEventToken')
        && str_contains($moduleReadme, 'ApiVersion')
        && str_contains($moduleReadme, 'nur bei einer inkompatiblen Änderung')
        && str_contains($rootReadme, 'OHA_TriggerExternalAlarmEvent'),
    'Both documentation levels must cover the public external event contract and compatibility policy.'
);
$security = (string) file_get_contents(dirname(__DIR__) . '/SECURITY.md');
$acceptance = (string) file_get_contents(dirname(__DIR__) . '/docs/SYMCON_9_ACCEPTANCE.md');
assertExternalEvent(
    str_contains($security, '256-Bit-Zugangsschlüssel')
        && str_contains($security, 'Ereignis-IDs werden als Hash persistent gespeichert')
        && str_contains($acceptance, '| F-10 |')
        && str_contains($acceptance, '| F-14 |'),
    'Security boundaries and practical acceptance cases must accompany the new inbound interface.'
);

fwrite(STDOUT, "OpenHomeAlarm external alarm event contract checks passed.\n");
