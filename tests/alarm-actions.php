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
    /** @var list<array{field:string,parameter:string,value:mixed}> */
    private array $formUpdates = [];

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
        if (isset($element['items']) && is_array($element['items'])) {
            $found = findAlarmActionFormField($element['items'], $name);
            if ($found !== null) {
                return $found;
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
$disarmAction = json_encode([
    'actionID'   => '{22222222-2222-2222-2222-222222222222}',
    'parameters' => [
        'TARGET'      => 5001,
        'ENVIRONMENT' => 'Default',
        'PARENT'      => 6001,
        'VALUE'       => false
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

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$instance->TestSetPropertyInteger('AlarmActionEnabled', 1);
$instance->TestSetPropertyString('AlarmAction', $alarmAction);
$instance->TestSetPropertyInteger('DisarmAfterAlarmActionEnabled', 1);
$instance->TestSetPropertyString('DisarmAfterAlarmAction', $disarmAction);
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();

assertAlarmAction($instance->ArmAway() === true, 'Away arming must succeed before testing alarm actions.');

global $testValues, $testActions;
$testValues[4001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(1, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    ($instance->TestWrittenValues()['State'] ?? null) === 4,
    'An immediate sensor must enter Alarm before its configured action is executed.'
);
assertAlarmAction(count($testActions) === 1, 'Alarm action must run exactly once when Alarm starts.');
assertAlarmAction(
    $testActions[0]['actionID'] === '{11111111-1111-1111-1111-111111111111}'
    && ($testActions[0]['parameters']['VALUE'] ?? null) === true,
    'Alarm action must preserve the action ID and parameters selected by Symcon.'
);

$instance->MessageSink(2, 4001, VM_UPDATE, [true, true, true]);
assertAlarmAction(count($testActions) === 1, 'Further sensor updates during Alarm must not rerun the alarm action.');

$instance->Disarm();
assertAlarmAction(count($testActions) === 2, 'Disarming an active Alarm must run the reset action once.');
assertAlarmAction(
    $testActions[1]['actionID'] === '{22222222-2222-2222-2222-222222222222}'
    && ($testActions[1]['parameters']['VALUE'] ?? null) === false,
    'Reset action must preserve the action ID and parameters selected by Symcon.'
);
assertAlarmAction(
    ($instance->TestWrittenValues()['State'] ?? null) === 0
    && ($instance->TestWrittenValues()['Mode'] ?? null) === 0,
    'Disarming must remain successful independently of the configured reset action.'
);

$instance->Disarm();
assertAlarmAction(count($testActions) === 2, 'Disarming an already disarmed system must not rerun the reset action.');

// The native SelectAction false value represents a valid, deliberately unconfigured optional action.
$testActions = [];
$testValues[4001] = false;
$noActionInstance = new OpenHomeAlarm();
$noActionInstance->Create();
$noActionInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$noActionInstance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$noActionInstance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($noActionInstance->ArmAway() === true, 'Unconfigured optional-action test must arm successfully.');
$testValues[4001] = true;
$noActionInstance->MessageSink(20, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction($testActions === [], 'A native SelectAction false value must behave as no configured action.');

// Entry-delay completion must use the same central Alarm transition and therefore run the alarm action.
$testActions = [];
$testValues[4001] = false;
$testValues[4002] = false;
$delayedInstance = new OpenHomeAlarm();
$delayedInstance->Create();
$delayedInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$delayedInstance->TestSetPropertyInteger('EntryDelaySeconds', 10);
$delayedInstance->TestSetPropertyInteger('AlarmActionEnabled', 1);
$delayedInstance->TestSetPropertyString('AlarmAction', $alarmAction);
$delayedInstance->TestSetPropertyInteger('CountdownActionEnabled', 1);
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
    count($testActions) === 3
    && $testActions[2]['actionID'] === '{11111111-1111-1111-1111-111111111111}',
    'Entry-delay expiry must run the alarm action exactly once after the countdown actions.'
);

// A broken optional countdown action must never block the delay state machine.
$testActions = [];
$testValues[4001] = false;
$brokenCountdownInstance = new OpenHomeAlarm();
$brokenCountdownInstance->Create();
$brokenCountdownInstance->TestSetPropertyInteger('ExitDelaySeconds', 5);
$brokenCountdownInstance->TestSetPropertyInteger('CountdownActionEnabled', 1);
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

// Broken optional action configuration must never prevent the core alarm state transition.
$testActions = [];
$testValues[4001] = false;
$brokenInstance = new OpenHomeAlarm();
$brokenInstance->Create();
$brokenInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$brokenInstance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$brokenInstance->TestSetPropertyInteger('AlarmActionEnabled', 1);
$brokenInstance->TestSetPropertyString('AlarmAction', '{invalid json');
$brokenInstance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
$brokenInstance->TestClearWrittenValues();
assertAlarmAction($brokenInstance->ArmAway() === true, 'Broken optional alarm action must not block arming.');
$testValues[4001] = true;
$brokenInstance->MessageSink(4, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    ($brokenInstance->TestWrittenValues()['State'] ?? null) === 4,
    'Broken optional action configuration must not prevent the Alarm state.'
);
assertAlarmAction($testActions === [], 'Invalid action configuration must not call IPS_RunAction.');

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
    && findAlarmActionFormField($form['elements'] ?? [], 'FaultAction') === null
    && findAlarmActionFormField($form['elements'] ?? [], 'FaultClearedAction') === null
    && findAlarmActionFormField($form['elements'] ?? [], 'CountdownAction') === null,
    'Disabled optional SelectAction fields must be absent from static form.json so native validation cannot block unrelated changes.'
);
foreach (
    [
        'AlarmActionEnabled',
        'AlarmResetActionEnabled',
        'DisarmAfterAlarmActionEnabled',
        'FaultActionEnabled',
        'FaultClearedActionEnabled',
        'CountdownActionEnabled'
    ] as $toggleName
) {
    $toggle = findAlarmActionFormField($form['elements'] ?? [], $toggleName);
    assertAlarmAction(
        is_array($toggle) && ($toggle['type'] ?? null) === 'Select',
        'Optional action toggle ' . $toggleName . ' must be present in static form.json.'
    );
}

$dynamicFormInstance = new OpenHomeAlarm();
$dynamicFormInstance->Create();
$dynamicForm = json_decode($dynamicFormInstance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
assertAlarmAction(
    findAlarmActionFormField($dynamicForm['elements'] ?? [], 'AlarmAction') === null,
    'GetConfigurationForm must omit disabled SelectAction fields completely.'
);

$dynamicFormInstance->TestSetPropertyInteger('AlarmActionEnabled', 1);
$dynamicFormInstance->TestSetPropertyString('AlarmAction', $alarmAction);
$dynamicForm = json_decode($dynamicFormInstance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
$dynamicAlarmAction = findAlarmActionFormField($dynamicForm['elements'] ?? [], 'AlarmAction');
$dynamicAlarmActionToggle = findAlarmActionFormField($dynamicForm['elements'] ?? [], 'AlarmActionEnabled');
assertAlarmAction(
    is_array($dynamicAlarmAction)
    && ($dynamicAlarmAction['type'] ?? null) === 'SelectAction'
    && ($dynamicAlarmAction['targetID'] ?? null) === -2
    && ($dynamicAlarmAction['value'] ?? null) === $alarmAction,
    'GetConfigurationForm must inject the enabled native SelectAction with its stored value.'
);
assertAlarmAction(
    ($dynamicAlarmActionToggle['onChange'] ?? null)
        === "OHA_UpdateOptionalActionForm(\$id, 'AlarmAction', \$AlarmActionEnabled);",
    'Optional action toggles must update their selector immediately in the open form.'
);
$dynamicFormInstance->UpdateOptionalActionForm('AlarmAction', 0);
assertAlarmAction(
    $dynamicFormInstance->TestFormUpdates() === [
        ['field' => 'AlarmAction', 'parameter' => 'enabled', 'value' => false]
    ],
    'Disabling an optional action must bypass selector validation without clearing its configured target.'
);
assertAlarmAction(
    findAlarmActionFormField($dynamicForm['elements'] ?? [], 'AlarmResetAction') === null,
    'Enabling one optional action must not inject other disabled SelectAction fields.'
);

$dynamicFormInstance->TestSetPropertyInteger('CountdownActionEnabled', 1);
$dynamicForm = json_decode($dynamicFormInstance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
$dynamicCountdownAction = findAlarmActionFormField($dynamicForm['elements'] ?? [], 'CountdownAction');
assertAlarmAction(
    is_array($dynamicCountdownAction)
    && ($dynamicCountdownAction['type'] ?? null) === 'SelectAction'
    && ($dynamicCountdownAction['targetID'] ?? null) === -2,
    'GetConfigurationForm must inject the countdown SelectAction only after it is enabled.'
);

$locale = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$translations = $locale['translations']['de'] ?? [];
foreach (['Alarm actions', 'No action', 'Configure action', 'Alarm start', 'On alarm', 'On disarm after alarm', 'Countdown output', 'On countdown step'] as $translationKey) {
    assertAlarmAction(isset($translations[$translationKey]), 'Missing German translation for ' . $translationKey . '.');
}

fwrite(STDOUT, "OpenHomeAlarm alarm action checks passed.\n");
