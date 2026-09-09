<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    4001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    4002 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    4001 => false,
    4002 => false
];

/** @var list<array{actionID:string,parameters:array<string,mixed>}> */
$testActions = [];

$testPendingConfigurationChanges = false;
$testResetConfigurationChanges = 0;
/** @var list<array{tileID:int,title:string,message:string,icon:string,sound:string,targetID:int}> */
$testPushNotifications = [];

function VISU_PostNotificationEx(
    int $tileID,
    string $title,
    string $message,
    string $icon,
    string $sound,
    int $targetID
): int|false {
    global $testPushNotifications;

    $testPushNotifications[] = [
        'tileID'   => $tileID,
        'title'    => $title,
        'message'  => $message,
        'icon'     => $icon,
        'sound'    => $sound,
        'targetID' => $targetID
    ];

    return count($testPushNotifications);
}

function IPS_HasChanges(int $instanceID): bool
{
    global $testPendingConfigurationChanges;

    return $testPendingConfigurationChanges;
}

function IPS_ResetChanges(int $instanceID): bool
{
    global $testPendingConfigurationChanges, $testResetConfigurationChanges;

    $testPendingConfigurationChanges = false;
    ++$testResetConfigurationChanges;

    return true;
}

function IPS_VariableExists(int $variableID): bool
{
    global $testVariables;

    return array_key_exists($variableID, $testVariables);
}

/** @return array<string,mixed> */
function IPS_GetVariable(int $variableID): array
{
    global $testVariables;

    if (!array_key_exists($variableID, $testVariables)) {
        throw new RuntimeException('Unknown test variable.');
    }

    return $testVariables[$variableID];
}

/** @return array<string,mixed> */
function IPS_GetVariablePresentation(int $variableID): array
{
    return [];
}

/** @return array<string,mixed> */
function IPS_GetVariableProfile(string $profileName): array
{
    return ['Associations' => []];
}

function GetValue(int $variableID): mixed
{
    global $testValues;

    if (!array_key_exists($variableID, $testValues)) {
        throw new RuntimeException('No test value available.');
    }

    return $testValues[$variableID];
}

/** @param array<string,mixed> $parameters */
function IPS_RunAction(string $actionID, array $parameters): bool
{
    global $testActions;

    $testActions[] = [
        'actionID'   => $actionID,
        'parameters' => $parameters
    ];

    return true;
}

class IPSModuleStrict
{
    public int $InstanceID = 0;

    /** @var list<array{field:string,parameter:string,value:mixed}> */
    private array $formUpdates = [];

    /** @var list<string> */
    private array $reloadedForms = [];

    /** @var array<string,mixed> */
    private array $properties = [];

    /** @var array<string,int|string> */
    private array $attributes = [];

    /** @var array<string,array{interval:int,script:string}> */
    private array $timers = [];

    /** @var array<int,list<int>> */
    private array $messages = [];

    /** @var array<string,mixed> */
    private array $writtenValues = [];

    /** @var array<string,mixed> */
    private array $currentValues = [];

    public function Create(): void
    {
    }

    public function Destroy(): void
    {
    }

    public function ApplyChanges(): void
    {
    }

    public function TestSetPropertyString(string $name, string $value): void
    {
        $this->properties[$name] = $value;
    }

    public function TestSetPropertyInteger(string $name, int $value): void
    {
        $this->properties[$name] = $value;
    }

    public function TestSetCurrentValue(string $ident, mixed $value): void
    {
        $this->currentValues[$ident] = $value;
    }

    /** @return array<string,mixed> */
    public function TestWrittenValues(): array
    {
        return $this->writtenValues;
    }

    public function TestClearWrittenValues(): void
    {
        $this->writtenValues = [];
    }

    /** @return array<string,int|string> */
    public function TestAttributes(): array
    {
        return $this->attributes;
    }

    /** @return array<string,array{interval:int,script:string}> */
    public function TestTimers(): array
    {
        return $this->timers;
    }

    /** @return list<array{field:string,parameter:string,value:mixed}> */
    public function TestFormUpdates(): array
    {
        return $this->formUpdates;
    }

    /** @return list<string> */
    public function TestReloadedForms(): array
    {
        return $this->reloadedForms;
    }

    public function TestSetAttributeString(string $name, string $value): void
    {
        $this->attributes[$name] = $value;
    }

    protected function SetVisualizationType(int $type): bool
    {
        return true;
    }

    protected function UpdateVisualizationValue(mixed $data): bool
    {
        return true;
    }

    protected function RegisterPropertyString(string $name, string $default): void
    {
        if (!array_key_exists($name, $this->properties)) {
            $this->properties[$name] = $default;
        }
    }

    protected function RegisterPropertyInteger(string $name, int $default): void
    {
        if (!array_key_exists($name, $this->properties)) {
            $this->properties[$name] = $default;
        }
    }

    protected function RegisterPropertyBoolean(string $name, bool $default): void
    {
        $this->RegisterPropertyInteger($name, $default ? 1 : 0);
    }

    protected function ReadPropertyBoolean(string $name): bool
    {
        return $this->ReadPropertyInteger($name) === 1;
    }

    protected function RegisterPropertyFloat(string $name, float $default): void
    {
    }

    protected function ReadPropertyFloat(string $name): float
    {
        return 0.0;
    }

    protected function ReadPropertyString(string $name): string
    {
        $value = $this->properties[$name] ?? '';

        return is_string($value) ? $value : '';
    }

    protected function ReadPropertyInteger(string $name): int
    {
        $value = $this->properties[$name] ?? 0;

        return is_int($value) ? $value : 0;
    }

    protected function RegisterAttributeInteger(string $name, int $default): void
    {
        if (!array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $default;
        }
    }

    protected function RegisterAttributeString(string $name, string $default): void
    {
        if (!array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $default;
        }
    }
    protected function ReadAttributeInteger(string $name): int
    {
        return $this->attributes[$name] ?? 0;
    }

    protected function ReadAttributeString(string $name): string
    {
        $value = $this->attributes[$name] ?? '';

        return is_string($value) ? $value : '';
    }
    protected function WriteAttributeInteger(string $name, int $value): void
    {
        $this->attributes[$name] = $value;
    }

    protected function WriteAttributeString(string $name, string $value): void
    {
        $this->attributes[$name] = $value;
    }
    protected function RegisterTimer(string $name, int $interval, string $script): bool
    {
        $this->timers[$name] = ['interval' => $interval, 'script' => $script];

        return true;
    }

    protected function SetTimerInterval(string $name, int $interval): bool
    {
        $this->timers[$name]['interval'] = $interval;

        return true;
    }

    protected function RegisterVariableInteger(string $ident, string $name, array $presentation, int $position): bool
    {
        return true;
    }

    protected function RegisterVariableBoolean(string $ident, string $name, array $presentation, int $position): bool
    {
        return true;
    }

    protected function RegisterVariableString(string $ident, string $name, array $presentation, int $position): bool
    {
        return true;
    }

    protected function SetValue(string $ident, mixed $value): void
    {
        $this->writtenValues[$ident] = $value;
        $this->currentValues[$ident] = $value;
    }

    protected function GetValue(string $ident): mixed
    {
        return $this->currentValues[$ident] ?? null;
    }

    protected function Translate(string $text): string
    {
        return $text;
    }

    protected function RegisterMessage(int $senderID, int $messageID): bool
    {
        $this->messages[$senderID] ??= [];
        if (!in_array($messageID, $this->messages[$senderID], true)) {
            $this->messages[$senderID][] = $messageID;
        }

        return true;
    }

    protected function UnregisterMessage(int $senderID, int $messageID): bool
    {
        return true;
    }

    /** @return array<int,list<int>> */
    protected function GetMessageList(): array
    {
        return $this->messages;
    }

    protected function UpdateFormField(string $field, string $parameter, mixed $value): bool
    {
        $this->formUpdates[] = [
            'field'     => $field,
            'parameter' => $parameter,
            'value'     => $value
        ];

        return true;
    }

    protected function ReloadForm(): bool
    {
        $this->reloadedForms[] = $this->GetConfigurationForm();

        return true;
    }

    protected function SendDebug(string $message, string $data, int $format): bool
    {
        return true;
    }
}

function assertAlarmAction(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function alarmActionSensor(int $variableID, bool $entryDelay): array
{
    return [
        'Enabled'      => true,
        'Name'         => 'Test ' . $variableID,
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => 'true',
        'ArmHome'      => false,
        'ArmAway'      => true,
        'ArmNight'     => false,
        'EntryDelay'   => $entryDelay
    ];
}

/**
 * @param list<array<string,mixed>> $elements
 *
 * @return array<string,mixed>|null
 */
function findAlarmActionFormField(array $elements, string $name): ?array
{
    foreach ($elements as $element) {
        if (!is_array($element)) {
            continue;
        }
        if (($element['name'] ?? null) === $name) {
            return $element;
        }
        foreach (['items', 'form'] as $childField) {
            if (isset($element[$childField]) && is_array($element[$childField])) {
                $found = findAlarmActionFormField($element[$childField], $name);
                if ($found !== null) {
                    return $found;
                }
            }
        }
    }

    return null;
}

$alarmAction = json_encode([
    'actionID'   => '{11111111-1111-1111-1111-111111111111}',
    'parameters' => [
        'TARGET'      => 5001,
        'ENVIRONMENT' => 'Default',
        'PARENT'      => 6001,
        'VALUE'       => true
    ]
], JSON_THROW_ON_ERROR);
$countdownAction = json_encode([
    'actionID'   => '{33333333-3333-3333-3333-333333333333}',
    'parameters' => [
        'TARGET'      => 5002,
        'ENVIRONMENT' => 'Default',
        'PARENT'      => 6001,
        'VALUE'       => true
    ]
], JSON_THROW_ON_ERROR);

// Countdown actions remain independent of escalation actions.
$testActions = [];
$testValues[4001] = false;
$testValues[4002] = false;
$delayedInstance = new OpenHomeAlarm();
$delayedInstance->Create();
$delayedInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$delayedInstance->TestSetPropertyInteger('EntryDelaySeconds', 10);
$delayedInstance->TestSetPropertyString('CountdownAction', $countdownAction);
$delayedInstance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4002, true)], JSON_THROW_ON_ERROR)
);
$delayedInstance->TestClearWrittenValues();
assertAlarmAction($delayedInstance->ArmAway() === true, 'Delayed alarm action test must arm successfully.');
$testValues[4002] = true;
$delayedInstance->MessageSink(3, 4002, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    count($testActions) === 1
    && $testActions[0]['actionID'] === '{33333333-3333-3333-3333-333333333333}',
    'Entry-delay start must run the optional countdown action, but not the alarm action.'
);
$entryDeadline = (int) ($delayedInstance->TestAttributes()['EntryDelayDeadline'] ?? 0);
$runCountdownStep = new ReflectionMethod(OpenHomeAlarm::class, 'RunCountdownActionStep');
$runCountdownStep->invoke($delayedInstance, $entryDeadline, 10);
assertAlarmAction(count($testActions) === 1, 'The same countdown step must not run twice.');
$runCountdownStep->invoke($delayedInstance, $entryDeadline, 9);
assertAlarmAction(
    count($testActions) === 2
    && $testActions[1]['actionID'] === '{33333333-3333-3333-3333-333333333333}',
    'A new positive countdown value must run the configured action once.'
);
$delayedInstance->CompleteEntryDelay();
assertAlarmAction(
    count($testActions) === 2,
    'Entry-delay expiry must not execute removed global alarm actions.'
);

// Optional action Lists may contain multiple independently enabled native actions.
$testActions = [];
$listCountdownInstance = new OpenHomeAlarm();
$listCountdownInstance->Create();
$listCountdownInstance->TestSetPropertyString('CountdownAction', json_encode([
    ['Enabled' => true, 'Name' => 'Countdown', 'Action' => $countdownAction],
    ['Enabled' => false, 'Name' => 'Disabled', 'Action' => $alarmAction]
], JSON_THROW_ON_ERROR));
$runCountdownStep->invoke($listCountdownInstance, time() + 10, 10);
assertAlarmAction(
    count($testActions) === 1
    && $testActions[0]['actionID'] === '{33333333-3333-3333-3333-333333333333}',
    'Optional action Lists must execute every enabled action and ignore disabled rows.'
);

// A broken optional countdown action must never block the delay state machine.
$testActions = [];
$testValues[4001] = false;
$brokenCountdownInstance = new OpenHomeAlarm();
$brokenCountdownInstance->Create();
$brokenCountdownInstance->TestSetPropertyInteger('ExitDelaySeconds', 5);
$brokenCountdownInstance->TestSetPropertyString('CountdownAction', '{invalid json');
$brokenCountdownInstance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction(
    $brokenCountdownInstance->ArmAway() === true
    && ($brokenCountdownInstance->TestWrittenValues()['State'] ?? null) === 1,
    'A broken countdown action must not block the normal exit-delay state.'
);
assertAlarmAction($testActions === [], 'An invalid countdown action must not call IPS_RunAction.');

// Escalation steps execute once relative to the global alarm start and stop with the alarm output.
$testActions = [];
$testValues[4001] = false;
$immediateEscalationAction = json_encode([
    'actionID'   => '{44444444-4444-4444-4444-444444444444}',
    'parameters' => ['VALUE' => 'immediate']
], JSON_THROW_ON_ERROR);
$delayedEscalationAction = json_encode([
    'actionID'   => '{55555555-5555-5555-5555-555555555555}',
    'parameters' => ['VALUE' => 'delayed']
], JSON_THROW_ON_ERROR);
$escalationInstance = new OpenHomeAlarm();
$escalationInstance->Create();
$escalationInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$escalationInstance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$escalationInstance->TestSetPropertyString('AlarmEscalationSteps', json_encode([
    ['Enabled' => true, 'Name' => 'Immediate', 'DelaySeconds' => 0, 'Action' => $immediateEscalationAction],
    ['Enabled' => true, 'Name' => 'Delayed', 'DelaySeconds' => 60, 'Action' => $delayedEscalationAction]
], JSON_THROW_ON_ERROR));
$escalationInstance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($escalationInstance->ArmAway(), 'Escalation test must arm successfully.');
$testValues[4001] = true;
$escalationInstance->MessageSink(30, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    count($testActions) === 1
    && $testActions[0]['actionID'] === '{44444444-4444-4444-4444-444444444444}',
    'A zero-delay escalation step must execute once when the alarm starts.'
);
assertAlarmAction(
    ($escalationInstance->TestTimers()['AlarmEscalation']['interval'] ?? 0) > 0,
    'A pending delayed escalation step must schedule the shared timer.'
);
$escalationInstance->ProcessAlarmEscalation();
assertAlarmAction(count($testActions) === 1, 'A processed escalation step must not execute twice.');
$escalationRuntime = json_decode(
    (string) ($escalationInstance->TestAttributes()['AlarmEscalationRuntime'] ?? '[]'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$escalationRuntime['StartedAt'] = time() - 120;
$escalationInstance->TestSetAttributeString(
    'AlarmEscalationRuntime',
    json_encode($escalationRuntime, JSON_THROW_ON_ERROR)
);
$escalationInstance->ProcessAlarmEscalation();
assertAlarmAction(
    count($testActions) === 2
    && $testActions[1]['actionID'] === '{55555555-5555-5555-5555-555555555555}',
    'An elapsed delayed escalation step must execute through the public timer callback.'
);
$escalationInstance->ProcessAlarmEscalation();
assertAlarmAction(count($testActions) === 2, 'A delayed escalation step must remain exactly-once after execution.');
assertAlarmAction($escalationInstance->ResetAlarmOutput(), 'The active alarm output must remain resettable.');
assertAlarmAction(
    ($escalationInstance->TestTimers()['AlarmEscalation']['interval'] ?? -1) === 0
    && ($escalationInstance->TestAttributes()['AlarmEscalationRuntime'] ?? '') === '[]',
    'Ending the last alarm output must cancel and clear its escalation cycle.'
);

// Native tile push notifications may be sent immediately without requiring a PHP action row.
$testPushNotifications = [];
$testValues[4001] = false;
$immediatePush = new OpenHomeAlarm();
$immediatePush->Create();
$immediatePush->TestSetPropertyInteger('ExitDelaySeconds', 0);
$immediatePush->TestSetPropertyInteger('EntryDelaySeconds', 0);
$immediatePush->TestSetPropertyInteger('PushNotificationMode', 1);
$immediatePush->TestSetPropertyInteger('PushNotificationTileID', 12345);
$immediatePush->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($immediatePush->ArmAway(), 'Immediate-push test must arm successfully.');
$testValues[4001] = true;
$immediatePush->MessageSink(34, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    $testPushNotifications === [[
        'tileID'   => 12345,
        'title'    => 'Intrusion alarm Main area!',
        'message'  => 'Sensor Test 4001 triggered.',
        'icon'     => 'Alert',
        'sound'    => 'siren',
        'targetID' => 0
    ]],
    'An immediate native push notification must include the affected area and triggering sensor.'
);
$immediatePush->ProcessAlarmEscalation();
assertAlarmAction(count($testPushNotifications) === 1, 'An immediate native push notification must be sent only once per alarm cycle.');

// The delayed mode shares the escalation timer but does not require a user-defined action.
$testPushNotifications = [];
$testValues[4001] = false;
$delayedPush = new OpenHomeAlarm();
$delayedPush->Create();
$delayedPush->TestSetPropertyInteger('ExitDelaySeconds', 0);
$delayedPush->TestSetPropertyInteger('EntryDelaySeconds', 0);
$delayedPush->TestSetPropertyInteger('PushNotificationMode', 2);
$delayedPush->TestSetPropertyInteger('PushNotificationTileID', 23456);
$delayedPush->TestSetPropertyInteger('PushNotificationDelaySeconds', 60);
$delayedPush->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($delayedPush->ArmAway(), 'Delayed-push test must arm successfully.');
$testValues[4001] = true;
$delayedPush->MessageSink(35, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    $testPushNotifications === []
    && ($delayedPush->TestTimers()['AlarmEscalation']['interval'] ?? 0) > 0,
    'A delayed native push notification must schedule the shared escalation timer without sending immediately.'
);
$delayedPushRuntime = json_decode(
    (string) ($delayedPush->TestAttributes()['AlarmEscalationRuntime'] ?? '[]'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$delayedPushRuntime['StartedAt'] = time() - 120;
$delayedPush->TestSetAttributeString(
    'AlarmEscalationRuntime',
    json_encode($delayedPushRuntime, JSON_THROW_ON_ERROR)
);
$delayedPush->ProcessAlarmEscalation();
assertAlarmAction(
    count($testPushNotifications) === 1
    && $testPushNotifications[0]['tileID'] === 23456
    && $testPushNotifications[0]['message'] === 'Sensor Test 4001 triggered.',
    'An elapsed native push delay must send the notification through the escalation timer.'
);

// Symcon persists each row of the current escalation form as one flat action.
$testActions = [];
$testValues[4001] = false;
$flatSignalGenerator = new OpenHomeAlarm();
$flatSignalGenerator->Create();
$flatSignalGenerator->TestSetPropertyInteger('ExitDelaySeconds', 0);
$flatSignalGenerator->TestSetPropertyInteger('EntryDelaySeconds', 0);
$flatSignalGenerator->TestSetPropertyString('AlarmEscalationSteps', json_encode([[
    'Enabled'         => true,
    'Name'            => 'Form siren',
    'DelaySeconds'    => 0,
    'Action'          => ['actionID' => '{FORM-SIREN}', 'parameters' => ['VALUE' => true]],
    'ResetMode'       => 1,
    'ResetAction'     => '',
    'SignalGenerator' => true
]], JSON_THROW_ON_ERROR));
$flatSignalGenerator->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($flatSignalGenerator->ArmAway(), 'The flat-form signal-generator test must arm successfully.');
$testValues[4001] = true;
$flatSignalGenerator->MessageSink(31, 4001, VM_UPDATE, [true, true, false]);
$flatSignalState = json_decode($flatSignalGenerator->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertAlarmAction(
    count($testActions) === 1
        && $testActions[0]['parameters']['VALUE'] === true
        && $flatSignalState['Alarm']['SignalGeneratorActive'] === true
        && $flatSignalState['Capabilities']['CanStopSignalGenerator'] === true,
    'A signal generator saved by the real Symcon form must expose its separate stop control.'
);
$flatSignalGenerator->TestSetPropertyString('DisarmCode', '1234');
assertAlarmAction(
    !$flatSignalGenerator->StopSignalGeneratorWithCode('0000') && count($testActions) === 1,
    'A configured code must reject stopping a signal generator without the correct code.'
);
assertAlarmAction(
    $flatSignalGenerator->StopSignalGeneratorWithCode('1234'),
    'The flat-form signal generator must be stoppable with the configured code.'
);
assertAlarmAction(
    count($testActions) === 2 && $testActions[1]['parameters']['VALUE'] === false,
    'Stopping a flat-form signal generator must execute its inverse Boolean action.'
);

// One escalation step may execute multiple actions and automatically invert Boolean set-value actions on reset.
$testActions = [];
$testValues[4001] = false;
$multiEscalation = new OpenHomeAlarm();
$multiEscalation->Create();
$multiEscalation->TestSetPropertyInteger('ExitDelaySeconds', 0);
$multiEscalation->TestSetPropertyInteger('EntryDelaySeconds', 0);
$multiEscalation->TestSetPropertyString('AlarmEscalationSteps', json_encode([[
    'Enabled'      => true,
    'Name'         => 'Outputs',
    'DelaySeconds' => 0,
    'Actions'      => [
        [
            'Enabled'      => true,
            'Name'         => 'Light',
            'Action'       => ['actionID' => '{LIGHT}', 'parameters' => ['VALUE' => true]],
            'ResetEnabled' => true
        ],
        [
            'Enabled'         => true,
            'Name'            => 'Siren',
            'Action'          => ['actionID' => '{SIREN}', 'parameters' => ['VALUE' => true]],
            'ResetEnabled'    => true,
            'SignalGenerator' => true
        ],
        [
            'Enabled'     => true,
            'Name'        => 'Shutter',
            'Action'      => ['actionID' => '{SHUTTER}', 'parameters' => ['VALUE' => 2]],
            'ResetMode'   => 2,
            'ResetAction' => ['actionID' => '{SHUTTER}', 'parameters' => ['VALUE' => 0]]
        ]
    ]
]], JSON_THROW_ON_ERROR));
$multiEscalation->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($multiEscalation->ArmAway(), 'Multiple-action escalation test must arm successfully.');
$testValues[4001] = true;
$multiEscalation->MessageSink(32, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    count($testActions) === 3
    && array_column($testActions, 'actionID') === ['{LIGHT}', '{SIREN}', '{SHUTTER}']
    && array_column(array_column($testActions, 'parameters'), 'VALUE') === [true, true, 2],
    'A due escalation step must execute all configured actions.'
);
$signalGeneratorState = json_decode($multiEscalation->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertAlarmAction(
    $signalGeneratorState['Alarm']['SignalGeneratorActive'] === true
    && $signalGeneratorState['Capabilities']['CanStopSignalGenerator'] === true,
    'A successfully executed signal generator must publish its active state and remain stoppable.'
);
assertAlarmAction($multiEscalation->StopSignalGenerator(), 'An active signal generator must be stoppable without resetting the alarm output.');
assertAlarmAction(
    count($testActions) === 4
    && $testActions[3]['actionID'] === '{SIREN}'
    && $testActions[3]['parameters']['VALUE'] === false,
    'Stopping the signal generator must execute only the siren reset action.'
);
$multiEscalationState = json_decode($multiEscalation->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertAlarmAction(
    $multiEscalationState['Alarm']['OutputActive'] === true
    && $multiEscalationState['Alarm']['SignalGeneratorActive'] === false
    && $multiEscalationState['Capabilities']['CanStopSignalGenerator'] === false,
    'Silencing a signal generator must retain the alarm output while hiding the already consumed silence control.'
);
assertAlarmAction($multiEscalation->ResetAlarmOutput(), 'Multiple escalation actions must remain resettable.');
assertAlarmAction(
    count($testActions) === 6
    && array_column(array_slice($testActions, 4), 'actionID') === ['{SHUTTER}', '{LIGHT}']
    && array_column(array_column(array_slice($testActions, 4), 'parameters'), 'VALUE') === [0, false],
    'Reset must preserve an already silenced signal generator and reset the remaining actions in reverse execution order.'
);

// The shared signal-generator state must only expose the stop control in an
// area whose alarm output is active.
$testActions = [];
$testValues[4001] = false;
$areaSignalGenerator = new OpenHomeAlarm();
$areaSignalGenerator->Create();
$areaSignalGenerator->TestSetPropertyInteger('ExitDelaySeconds', 0);
$areaSignalGenerator->TestSetPropertyInteger('EntryDelaySeconds', 0);
$areaSignalGenerator->TestSetPropertyString(
    'Partitions',
    '[{"Enabled":true,"ID":"main","Name":"Main area","Default":true},{"Enabled":true,"ID":"schuppen","Name":"Schuppen","Default":false}]'
);
$areaSignalGenerator->TestSetPropertyString('AlarmEscalationSteps', json_encode([[
    'Enabled'      => true,
    'Name'         => 'Area output',
    'DelaySeconds' => 0,
    'Actions'      => [[
        'Enabled'         => true,
        'Name'            => 'Area siren',
        'Action'          => ['actionID' => '{SIREN}', 'parameters' => ['VALUE' => true]],
        'ResetEnabled'    => true,
        'SignalGenerator' => true
    ]]
]], JSON_THROW_ON_ERROR));
$areaSignalGenerator->TestSetPropertyString(
    'Sensors',
    json_encode([
        array_merge(alarmActionSensor(4001, false), ['PartitionID' => 'schuppen'])
    ], JSON_THROW_ON_ERROR)
);
assertAlarmAction(
    $areaSignalGenerator->ArmPartition('main', 'away'),
    'The area signal-generator test must arm all areas through main.'
);
$testValues[4001] = true;
$areaSignalGenerator->MessageSink(33, 4001, VM_UPDATE, [true, true, false]);
$areaSignalState = json_decode($areaSignalGenerator->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertAlarmAction(
    $areaSignalState['Alarm']['SignalGeneratorActive'] === true
    && $areaSignalState['Partitions']['schuppen']['Alarm']['SignalGeneratorActive'] === true
    && $areaSignalState['Partitions']['schuppen']['Capabilities']['CanStopSignalGenerator'] === true
    && $areaSignalState['Partitions']['main']['Alarm']['SignalGeneratorActive'] === false
    && $areaSignalState['Partitions']['main']['Capabilities']['CanStopSignalGenerator'] === false,
    'The signal-generator control must follow the alarmed area instead of the currently selected area.'
);

// A running alarm must retain its dedicated signal-generator control even when
// an update or restored installation has lost the escalation runtime cache.
$testActions = [];
$testValues[4001] = false;
$missingEscalationRuntime = new OpenHomeAlarm();
$missingEscalationRuntime->Create();
$missingEscalationRuntime->TestSetPropertyInteger('ExitDelaySeconds', 0);
$missingEscalationRuntime->TestSetPropertyInteger('EntryDelaySeconds', 0);
$missingEscalationRuntime->TestSetPropertyString('AlarmEscalationSteps', json_encode([[
    'Enabled'      => true,
    'Name'         => 'Outputs',
    'DelaySeconds' => 0,
    'Actions'      => [[
        'Enabled'         => true,
        'Name'            => 'Siren',
        'Action'          => ['actionID' => '{SIREN}', 'parameters' => ['VALUE' => true]],
        'ResetEnabled'    => true,
        'SignalGenerator' => true
    ]]
]], JSON_THROW_ON_ERROR));
$missingEscalationRuntime->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($missingEscalationRuntime->ArmAway(), 'Missing-runtime signal-generator test must arm successfully.');
$testValues[4001] = true;
$missingEscalationRuntime->MessageSink(32, 4001, VM_UPDATE, [true, true, false]);
$missingEscalationRuntime->TestSetAttributeString('AlarmEscalationRuntime', '[]');
$missingRuntimeState = json_decode($missingEscalationRuntime->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertAlarmAction(
    $missingRuntimeState['Alarm']['SignalGeneratorActive'] === true
    && $missingRuntimeState['Capabilities']['CanStopSignalGenerator'] === true,
    'The explicit signal-generator state must remain available when the escalation runtime cache is missing.'
);
assertAlarmAction(
    $missingEscalationRuntime->StopSignalGenerator()
    && count($testActions) === 2
    && $testActions[1]['actionID'] === '{SIREN}'
    && $testActions[1]['parameters']['VALUE'] === false,
    'Stopping without a runtime cache must execute the configured signal-generator reset action.'
);
$missingRuntimeState = json_decode($missingEscalationRuntime->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertAlarmAction(
    $missingRuntimeState['Alarm']['SignalGeneratorActive'] === false
    && $missingRuntimeState['Capabilities']['CanStopSignalGenerator'] === false,
    'The signal-generator control must disappear after the configured fallback reset was executed.'
);

// Silencing a delayed signal generator must prevent it from starting later in
// the same alarm cycle.
$testActions = [];
$testValues[4001] = false;
$pendingSignalGenerator = new OpenHomeAlarm();
$pendingSignalGenerator->Create();
$pendingSignalGenerator->TestSetPropertyInteger('ExitDelaySeconds', 0);
$pendingSignalGenerator->TestSetPropertyInteger('EntryDelaySeconds', 0);
$pendingSignalGenerator->TestSetPropertyString('AlarmEscalationSteps', json_encode([[
    'Enabled'      => true,
    'Name'         => 'Delayed output',
    'DelaySeconds' => 60,
    'Actions'      => [[
        'Enabled'         => true,
        'Name'            => 'Delayed siren',
        'Action'          => ['actionID' => '{SIREN}', 'parameters' => ['VALUE' => true]],
        'ResetEnabled'    => true,
        'SignalGenerator' => true
    ]]
]], JSON_THROW_ON_ERROR));
$pendingSignalGenerator->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($pendingSignalGenerator->ArmAway(), 'Pending signal-generator test must arm successfully.');
$testValues[4001] = true;
$pendingSignalGenerator->MessageSink(32, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    $pendingSignalGenerator->StopSignalGenerator()
    && count($testActions) === 1
    && $testActions[0]['parameters']['VALUE'] === false,
    'A pending signal generator must be silenced through its configured reset action.'
);
$pendingRuntime = json_decode($pendingSignalGenerator->TestAttributes()['AlarmEscalationRuntime'], true, 512, JSON_THROW_ON_ERROR);
$pendingRuntime['StartedAt'] = time() - 61;
$pendingSignalGenerator->TestSetAttributeString('AlarmEscalationRuntime', json_encode($pendingRuntime, JSON_THROW_ON_ERROR));
$pendingSignalGenerator->ProcessAlarmEscalation();
assertAlarmAction(
    count($testActions) === 1,
    'A signal generator silenced earlier in the alarm cycle must not start when its delay expires.'
);

// Disarming directly from Alarm must execute the same escalation reset actions.
$testActions = [];
$testValues[4001] = false;
$disarmEscalation = new OpenHomeAlarm();
$disarmEscalation->Create();
$disarmEscalation->TestSetPropertyInteger('ExitDelaySeconds', 0);
$disarmEscalation->TestSetPropertyInteger('EntryDelaySeconds', 0);
$disarmEscalation->TestSetPropertyString('AlarmEscalationSteps', json_encode([[
    'Enabled'      => true,
    'Name'         => 'Disarm outputs',
    'DelaySeconds' => 0,
    'Actions'      => [
        [
            'Enabled'     => true,
            'Name'        => 'Light',
            'Action'      => ['actionID' => '{LIGHT}', 'parameters' => ['VALUE' => true]],
            'ResetMode'   => 1,
            'ResetAction' => ''
        ],
        [
            'Enabled'     => true,
            'Name'        => 'Shutter',
            'Action'      => ['actionID' => '{SHUTTER}', 'parameters' => ['VALUE' => 2]],
            'ResetMode'   => 2,
            'ResetAction' => ['actionID' => '{SHUTTER}', 'parameters' => ['VALUE' => 0]]
        ]
    ]
]], JSON_THROW_ON_ERROR));
$disarmEscalation->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($disarmEscalation->ArmAway(), 'Disarm escalation test must arm successfully.');
$testValues[4001] = true;
$disarmEscalation->MessageSink(33, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction($disarmEscalation->Disarm(), 'Disarming an active alarm must succeed.');
assertAlarmAction(
    count($testActions) === 4
    && array_column($testActions, 'actionID') === ['{LIGHT}', '{SHUTTER}', '{SHUTTER}', '{LIGHT}']
    && array_column(array_column($testActions, 'parameters'), 'VALUE') === [true, 2, 0, false],
    'Disarming must execute custom and inverted escalation reset actions in reverse execution order.'
);

// ApplyChanges and a service restart use the persisted absolute start without repeating completed steps.
$testActions = [];
$testValues[4001] = false;
$restoredEscalation = new OpenHomeAlarm();
$restoredEscalation->Create();
$restoredEscalation->TestSetPropertyInteger('ExitDelaySeconds', 0);
$restoredEscalation->TestSetPropertyInteger('EntryDelaySeconds', 0);
$restoredEscalation->TestSetPropertyString('AlarmEscalationSteps', json_encode([
    ['Enabled' => true, 'Name' => 'Restored', 'DelaySeconds' => 60, 'Action' => $delayedEscalationAction]
], JSON_THROW_ON_ERROR));
$restoredEscalation->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($restoredEscalation->ArmAway(), 'Restart escalation test must arm successfully.');
$testValues[4001] = true;
$restoredEscalation->MessageSink(31, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction($testActions === [], 'A future escalation step must not execute at alarm start.');
$restoredRuntime = json_decode(
    (string) ($restoredEscalation->TestAttributes()['AlarmEscalationRuntime'] ?? '[]'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$restoredRuntime['StartedAt'] = time() - 120;
$restoredEscalation->TestSetAttributeString(
    'AlarmEscalationRuntime',
    json_encode($restoredRuntime, JSON_THROW_ON_ERROR)
);
$restoredEscalation->ApplyChanges();
assertAlarmAction(
    count($testActions) === 1
    && $testActions[0]['actionID'] === '{55555555-5555-5555-5555-555555555555}',
    'ApplyChanges must execute an overdue persisted escalation step once.'
);
$restoredEscalation->ApplyChanges();
assertAlarmAction(count($testActions) === 1, 'Repeated recovery must not repeat an executed escalation step.');

$form = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
assertAlarmAction(
    findAlarmActionFormField($form['elements'] ?? [], 'AlarmAction') === null
    && findAlarmActionFormField($form['elements'] ?? [], 'AlarmResetAction') === null
    && findAlarmActionFormField($form['elements'] ?? [], 'DisarmAfterAlarmAction') === null
    && findAlarmActionFormField($form['elements'] ?? [], 'FaultAction') !== null
    && findAlarmActionFormField($form['elements'] ?? [], 'FaultClearedAction') !== null
    && findAlarmActionFormField($form['elements'] ?? [], 'CountdownAction') !== null,
    'Removed global alarm-action selectors must be absent from the configuration form.'
);
assertAlarmAction(
    findAlarmActionFormField($form['elements'] ?? [], 'AlarmActionEnabled') === null
    && findAlarmActionFormField($form['elements'] ?? [], 'AlarmResetActionEnabled') === null
    && findAlarmActionFormField($form['elements'] ?? [], 'DisarmAfterAlarmActionEnabled') === null,
    'Global alarm-action configuration must be removed completely.'
);
foreach (['FaultAction', 'FaultClearedAction', 'CountdownAction'] as $actionName) {
    $action = findAlarmActionFormField($form['elements'] ?? [], $actionName);
    assertAlarmAction(
        is_array($action)
        && ($action['type'] ?? null) === 'List'
        && ($action['add'] ?? null) === true
        && ($action['delete'] ?? null) === true,
        'Optional action ' . $actionName . ' must be an empty-safe native action List.'
    );
}

$dynamicFormInstance = new OpenHomeAlarm();
$dynamicFormInstance->Create();
$dynamicFormInstance->TestSetPropertyString('CountdownAction', $countdownAction);
$dynamicFormInstance->TestSetPropertyString('AlarmEscalationSteps', json_encode([[
    'Enabled'      => true,
    'Name'         => 'Legacy step',
    'DelaySeconds' => 0,
    'Action'       => $alarmAction
]], JSON_THROW_ON_ERROR));
$dynamicForm = json_decode($dynamicFormInstance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
assertAlarmAction(
    findAlarmActionFormField($dynamicForm['elements'] ?? [], 'AlarmAction') === null,
    'GetConfigurationForm must not expose removed global alarm-action fields.'
);
$dynamicEscalationList = findAlarmActionFormField($dynamicForm['elements'] ?? [], 'AlarmEscalationSteps');
$dynamicEscalationValues = $dynamicEscalationList['values'] ?? [];
assertAlarmAction(
    count($dynamicEscalationValues) === 1
    && ($dynamicEscalationValues[0]['Name'] ?? null) === 'Legacy step'
    && ($dynamicEscalationValues[0]['Action'] ?? null) === $alarmAction
    && ($dynamicEscalationValues[0]['ResetMode'] ?? null) === 0
    && ($dynamicEscalationValues[0]['ResetAction'] ?? null) === '',
    'GetConfigurationForm must expose a legacy single action as one directly editable escalation row.'
);
$incompleteEscalationFormInstance = new OpenHomeAlarm();
$incompleteEscalationFormInstance->Create();
$incompleteEscalationFormInstance->TestSetPropertyString('AlarmEscalationSteps', json_encode([
    [
        'Enabled'         => true,
        'Name'            => 'Siren',
        'DelaySeconds'    => 0,
        'Action'          => $alarmAction,
        'ResetMode'       => 0,
        'ResetAction'     => '',
        'SignalGenerator' => true
    ],
    [
        'Enabled'         => true,
        'Name'            => 'Light',
        'DelaySeconds'    => 0,
        'Action'          => $alarmAction,
        'ResetMode'       => 2,
        'ResetAction'     => '',
        'SignalGenerator' => false
    ]
], JSON_THROW_ON_ERROR));
$incompleteEscalationForm = json_decode(
    $incompleteEscalationFormInstance->GetConfigurationForm(),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$incompleteEscalationValues = findAlarmActionFormField(
    $incompleteEscalationForm['elements'] ?? [],
    'AlarmEscalationSteps'
)['values'] ?? [];
assertAlarmAction(
    count($incompleteEscalationValues) === 2
    && ($incompleteEscalationValues[0]['SignalGenerator'] ?? null) === true
    && ($incompleteEscalationValues[0]['ResetMode'] ?? null) === 0
    && ($incompleteEscalationValues[1]['ResetMode'] ?? null) === 2
    && ($incompleteEscalationValues[1]['ResetAction'] ?? null) === '',
    'Incomplete signal-generator and custom-reset rows must remain editable without breaking the configuration form.'
);
$automaticResetForm = $dynamicFormInstance->GetAlarmEscalationEditForm([
    'ResetMode' => 1
]);
assertAlarmAction(
    findAlarmActionFormField($automaticResetForm, 'ResetAction') === null,
    'Boolean automatic reset must omit the custom SelectAction so an empty action cannot fail validation.'
);
$customResetForm = $dynamicFormInstance->GetAlarmEscalationEditForm([
    'ResetMode'   => 2,
    'ResetAction' => json_encode([
        'actionID'   => '{SHUTTER}',
        'parameters' => ['VALUE' => 0]
    ], JSON_THROW_ON_ERROR)
]);
$customResetSelector = findAlarmActionFormField($customResetForm, 'ResetAction');
assertAlarmAction(
    is_array($customResetSelector)
    && ($customResetSelector['type'] ?? null) === 'SelectAction'
    && ($customResetSelector['targetID'] ?? null) === -2
    && json_decode((string) ($customResetSelector['value'] ?? ''), true, 512, JSON_THROW_ON_ERROR) === [
        'actionID'   => '{SHUTTER}',
        'parameters' => ['VALUE' => 0]
    ],
    'A custom reset mode must expose its stored native Symcon reset action selector.'
);

assertAlarmAction(
    findAlarmActionFormField($dynamicForm['elements'] ?? [], 'AlarmResetAction') === null,
    'Removed global alarm-reset selectors must never be injected.'
);

$dynamicForm = json_decode($dynamicFormInstance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
$dynamicCountdownAction = findAlarmActionFormField($dynamicForm['elements'] ?? [], 'CountdownAction');
assertAlarmAction(
    is_array($dynamicCountdownAction)
    && ($dynamicCountdownAction['type'] ?? null) === 'List'
    && ($dynamicCountdownAction['values'][0]['Name'] ?? null) === 'Configured action'
    && ($dynamicCountdownAction['values'][0]['Action'] ?? null) === $countdownAction,
    'GetConfigurationForm must migrate a previously configured optional action into its editable List.'
);

$lockedFormInstance = new OpenHomeAlarm();
$lockedFormInstance->Create();
$lockedFormInstance->TestSetCurrentValue('Mode', 2);
$lockedFormInstance->TestSetCurrentValue('State', 2);
$lockedForm = json_decode($lockedFormInstance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
foreach (['Partitions', 'ExitDelaySeconds', 'CountdownAction', 'AlarmEscalationSteps', 'AutoRearmAfterAlarm'] as $fieldName) {
    $field = findAlarmActionFormField($lockedForm['elements'] ?? [], $fieldName);
    assertAlarmAction(
        is_array($field) && ($field['enabled'] ?? null) === false,
        'Every alarm configuration field must be disabled while an alarm partition is active.'
    );
}
$lockedCountdown = findAlarmActionFormField($lockedForm['elements'] ?? [], 'CountdownAction');
assertAlarmAction(
    ($lockedCountdown['add'] ?? null) === false && ($lockedCountdown['delete'] ?? null) === false,
    'Active alarm partitions must disable adding and deleting optional actions.'
);
assertAlarmAction(
    str_contains((string) ($lockedForm['elements'][0]['caption'] ?? ''), 'instance configuration is locked'),
    'The locked configuration form must explain that disarming is required before editing alarm settings.'
);
$reloadCountBeforeRejectedChange = count($lockedFormInstance->TestReloadedForms());
$testPendingConfigurationChanges = true;
$resetCountBeforeRejectedChange = $testResetConfigurationChanges;
$lockedFormInstance->ApplyChanges();
assertAlarmAction(
    $testPendingConfigurationChanges === false
        && $testResetConfigurationChanges === $resetCountBeforeRejectedChange + 1,
    'ApplyChanges must discard pending configuration changes when an alarm partition became active.'
);
assertAlarmAction(
    count($lockedFormInstance->TestReloadedForms()) === $reloadCountBeforeRejectedChange + 1,
    'Rejecting a stale configuration form must reload it with the active-state lock.'
);

$lockTransitionInstance = new OpenHomeAlarm();
$lockTransitionInstance->Create();
$reloadCountBeforeArming = count($lockTransitionInstance->TestReloadedForms());
assertAlarmAction($lockTransitionInstance->ArmAway(), 'The form-lock transition test must arm successfully.');
assertAlarmAction(
    count($lockTransitionInstance->TestReloadedForms()) === $reloadCountBeforeArming + 1,
    'Arming must reload an already open configuration form so its fields become locked.'
);
$reloadCountBeforeDisarming = count($lockTransitionInstance->TestReloadedForms());
assertAlarmAction($lockTransitionInstance->Disarm(), 'The form-lock transition test must disarm successfully.');
assertAlarmAction(
    count($lockTransitionInstance->TestReloadedForms()) === $reloadCountBeforeDisarming + 1,
    'Disarming must reload an already open configuration form so its fields become editable again.'
);
$optionalActionForm = $dynamicFormInstance->GetOptionalActionEditForm([
    'Action' => $countdownAction
]);
$optionalActionSelector = findAlarmActionFormField($optionalActionForm, 'Action');
assertAlarmAction(
    is_array($optionalActionSelector)
    && ($optionalActionSelector['type'] ?? null) === 'SelectAction'
    && ($optionalActionSelector['value'] ?? null) === $countdownAction,
    'Optional action editing must retain the previously selected native action.'
);

$locale = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$translations = $locale['translations']['de'] ?? [];
foreach ([
    'Alarm escalation',
    'Countdown output',
    'Countdown actions',
    'Actions on new fault',
    'Actions on fault cleared',
    'Configured action',
    'Optional: Add an action for output on every second of an active entry or exit delay. Typical uses are a spoken remaining time, a gong, a signal tone or a status display. An empty list runs no action. Scripts can read the remaining time, triggering sensor, arming mode and state through the public OHA_GetControlState() API.',
    'Optional: Add an action that runs once when a configured fault or a monitored sensor becomes faulty. Typical uses are a notification, spoken warning or warning light. An empty list runs no action.',
    'Optional: Add an action that runs once when a previously active fault is cleared. Typical uses are an all-clear notification or switching off a warning light. An empty list runs no action.'
] as $translationKey) {
    assertAlarmAction(isset($translations[$translationKey]), 'Missing German translation for ' . $translationKey . '.');
}

fwrite(STDOUT, "OpenHomeAlarm alarm action checks passed.\n");
