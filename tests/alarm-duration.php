<?php

declare(strict_types=1);

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    7001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    7001 => false
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

    public function TestGetCurrentValue(string $ident): mixed
    {
        return $this->currentValues[$ident] ?? null;
    }

    public function TestSetAttributeInteger(string $name, int $value): void
    {
        $this->attributes[$name] = $value;
    }

    /** @return array<string,array{interval:int,script:string}> */
    public function TestTimers(): array
    {
        return $this->timers;
    }

    /** @return array<string,int|string> */
    public function TestAttributes(): array
    {
        return $this->attributes;
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
        $value = $this->attributes[$name] ?? 0;

        return is_int($value) ? $value : 0;
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
        return true;
    }

    protected function SendDebug(string $message, string $data, int $format): bool
    {
        return true;
    }
}

function assertAlarmDuration(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function alarmDurationSensor(int $variableID): array
{
    return [
        'Enabled'      => true,
        'Name'         => 'Haustür',
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => 'true',
        'ArmHome'      => false,
        'ArmAway'      => true,
        'ArmNight'     => false,
        'AlwaysActive' => false,
        'ExitDelay'    => false,
        'EntryDelay'   => false
    ];
}

$alarmAction = json_encode([
    'actionID'   => '{11111111-1111-1111-1111-111111111111}',
    'parameters' => ['VALUE' => true]
], JSON_THROW_ON_ERROR);
$resetAction = json_encode([
    'actionID'   => '{22222222-2222-2222-2222-222222222222}',
    'parameters' => ['VALUE' => false]
], JSON_THROW_ON_ERROR);
$disarmAction = json_encode([
    'actionID'   => '{33333333-3333-3333-3333-333333333333}',
    'parameters' => ['VALUE' => false]
], JSON_THROW_ON_ERROR);

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$instance->TestSetPropertyInteger('AlarmDurationSeconds', 3);
$instance->TestSetPropertyString('AlarmAction', $alarmAction);
$instance->TestSetPropertyString('AlarmResetAction', $resetAction);
$instance->TestSetPropertyString('DisarmAfterAlarmAction', $disarmAction);
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmDurationSensor(7001)], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();

$timers = $instance->TestTimers();
assertAlarmDuration(
    ($timers['AlarmDuration']['interval'] ?? null) === 0
    && ($timers['AlarmDuration']['script'] ?? '') === 'OHA_CompleteAlarmDuration($_IPS[\'TARGET\']);',
    'Alarm-duration timer must be registered disabled and call its public callback.'
);

assertAlarmDuration($instance->ArmAway() === true, 'Alarm-duration test must arm successfully.');
global $testValues, $testActions;
$testValues[7001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(1, 7001, VM_UPDATE, [true, true, false]);
assertAlarmDuration(
    ($instance->TestWrittenValues()['State'] ?? null) === 4,
    'Triggered sensor must enter Alarm.'
);
assertAlarmDuration(
    ($instance->TestWrittenValues()['AlarmOutputActive'] ?? null) === true,
    'Alarm start must publish an active alarm output.'
);
assertAlarmDuration(
    ($instance->TestTimers()['AlarmDuration']['interval'] ?? null) === 3000,
    'Alarm duration must arm the reset timer with the configured seconds.'
);
assertAlarmDuration(
    ($instance->TestAttributes()['AlarmDurationDeadline'] ?? 0) >= time() + 2
    && ($instance->TestAttributes()['AlarmOutputActive'] ?? 0) === 1,
    'Alarm duration deadline and active state must be persisted.'
);
assertAlarmDuration(
    count($testActions) === 1
    && $testActions[0]['actionID'] === '{11111111-1111-1111-1111-111111111111}',
    'Alarm start action must still run exactly once.'
);

$instance->CompleteAlarmDuration();
assertAlarmDuration(
    ($instance->TestWrittenValues()['State'] ?? null) === 4,
    'Automatic alarm-output reset must not disarm or clear the Alarm state.'
);
assertAlarmDuration(
    ($instance->TestWrittenValues()['AlarmOutputActive'] ?? null) === false,
    'Automatic timeout must mark the alarm output inactive.'
);
assertAlarmDuration(
    ($instance->TestTimers()['AlarmDuration']['interval'] ?? null) === 0
    && ($instance->TestAttributes()['AlarmDurationDeadline'] ?? -1) === 0
    && ($instance->TestAttributes()['AlarmOutputActive'] ?? -1) === 0,
    'Automatic timeout must stop and clear the alarm-duration timer state.'
);
assertAlarmDuration(
    count($testActions) === 2
    && $testActions[1]['actionID'] === '{22222222-2222-2222-2222-222222222222}',
    'Automatic timeout must run the configured alarm reset action once.'
);
$history = json_decode($instance->GetEventHistory(), true, 512, JSON_THROW_ON_ERROR);
assertAlarmDuration(
    ($history[0]['Event'] ?? null) === 'alarm_output_reset'
    && ($history[0]['State'] ?? null) === 4,
    'Alarm-output reset must be recorded while the system remains in Alarm.'
);

$instance->CompleteAlarmDuration();
assertAlarmDuration(count($testActions) === 2, 'Repeated timer callbacks must not rerun the reset action.');
assertAlarmDuration($instance->ResetAlarmOutput() === false, 'An already reset alarm output must not reset twice.');

$instance->Disarm();
assertAlarmDuration(
    count($testActions) === 3
    && $testActions[2]['actionID'] === '{33333333-3333-3333-3333-333333333333}',
    'Disarming after an automatically reset alarm must still run the dedicated disarm-after-alarm action.'
);

// A duration of 0 keeps the alarm output active until manual reset or disarm.
$testActions = [];
$testValues[7001] = false;
$manual = new OpenHomeAlarm();
$manual->Create();
$manual->TestSetPropertyInteger('ExitDelaySeconds', 0);
$manual->TestSetPropertyInteger('EntryDelaySeconds', 0);
$manual->TestSetPropertyInteger('AlarmDurationSeconds', 0);
$manual->TestSetPropertyString('AlarmAction', $alarmAction);
$manual->TestSetPropertyString('AlarmResetAction', $resetAction);
$manual->TestSetPropertyString('Sensors', json_encode([alarmDurationSensor(7001)], JSON_THROW_ON_ERROR));
$manual->TestClearWrittenValues();
assertAlarmDuration($manual->ArmAway() === true, 'Manual-reset test must arm successfully.');
$testValues[7001] = true;
$manual->MessageSink(2, 7001, VM_UPDATE, [true, true, false]);
assertAlarmDuration(
    ($manual->TestTimers()['AlarmDuration']['interval'] ?? null) === 0
    && ($manual->TestWrittenValues()['AlarmOutputActive'] ?? null) === true,
    'Alarm duration 0 must keep the output active without an automatic timer.'
);
assertAlarmDuration($manual->ResetAlarmOutput() === true, 'Manual alarm-output reset must succeed while Alarm is active.');
assertAlarmDuration(
    ($manual->TestWrittenValues()['State'] ?? null) === 4
    && ($manual->TestWrittenValues()['AlarmOutputActive'] ?? null) === false,
    'Manual reset must silence only the alarm output and keep Alarm latched.'
);
assertAlarmDuration(count($testActions) === 2, 'Manual reset must run one alarm action and one reset action.');

// Persisted deadlines restore the remaining timeout after ApplyChanges/restart.
$testActions = [];
$restart = new OpenHomeAlarm();
$restart->Create();
$restart->TestSetPropertyInteger('AlarmDurationSeconds', 30);
$restart->TestSetCurrentValue('Mode', 2);
$restart->TestSetCurrentValue('State', 4);
$restart->TestSetCurrentValue('AlarmOutputActive', true);
$restart->TestSetAttributeInteger('AlarmOutputActive', 1);
$restart->TestSetAttributeInteger('AlarmDurationDeadline', time() + 20);
$restart->ApplyChanges();
$restoredInterval = $restart->TestTimers()['AlarmDuration']['interval'] ?? 0;
assertAlarmDuration(
    $restoredInterval >= 19000 && $restoredInterval <= 20000,
    'ApplyChanges must restore the remaining alarm-output duration from the persisted deadline.'
);
assertAlarmDuration(
    ($restart->TestWrittenValues()['AlarmOutputActive'] ?? null) === true,
    'Restart recovery must publish that the alarm output is still active.'
);

// An expired persisted deadline is reset immediately and exactly once.
$testActions = [];
$expired = new OpenHomeAlarm();
$expired->Create();
$expired->TestSetPropertyInteger('AlarmDurationSeconds', 30);
$expired->TestSetPropertyString('AlarmResetAction', $resetAction);
$expired->TestSetCurrentValue('Mode', 2);
$expired->TestSetCurrentValue('State', 4);
$expired->TestSetCurrentValue('AlarmOutputActive', true);
$expired->TestSetAttributeInteger('AlarmOutputActive', 1);
$expired->TestSetAttributeInteger('AlarmDurationDeadline', time() - 1);
$expired->ApplyChanges();
assertAlarmDuration(
    count($testActions) === 1
    && $testActions[0]['actionID'] === '{22222222-2222-2222-2222-222222222222}',
    'An expired alarm deadline must run the reset action during restart recovery.'
);
assertAlarmDuration(
    $expired->TestGetCurrentValue('State') === 4
    && ($expired->TestWrittenValues()['AlarmOutputActive'] ?? null) === false,
    'Expired deadline recovery must keep Alarm latched but mark its output inactive.'
);

$form = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$alarmPanel = null;
foreach ($form['elements'] ?? [] as $element) {
    if (($element['type'] ?? null) === 'ExpansionPanel' && ($element['caption'] ?? null) === 'Alarm actions') {
        $alarmPanel = $element;
        break;
    }
}
assertAlarmDuration(is_array($alarmPanel), 'Alarm actions panel must remain available.');
$fields = [];
foreach ($alarmPanel['items'] ?? [] as $item) {
    if (isset($item['name'])) {
        $fields[$item['name']] = $item;
    }
}
assertAlarmDuration(
    ($fields['AlarmDurationSeconds']['type'] ?? null) === 'NumberSpinner'
    && ($fields['AlarmDurationSeconds']['minimum'] ?? null) === 0,
    'Alarm actions panel must offer a non-negative alarm duration.'
);
assertAlarmDuration(
    ($fields['AlarmResetAction']['type'] ?? null) === 'SelectAction'
    && ($fields['AlarmResetAction']['targetID'] ?? null) === -2,
    'Alarm actions panel must offer a selectable alarm-output reset action.'
);

$locale = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$translations = $locale['translations']['de'] ?? [];
foreach (['Alarm duration (seconds)', 'On alarm output reset', 'Alarm output active', 'Alarm output inactive'] as $translationKey) {
    assertAlarmDuration(isset($translations[$translationKey]), 'Missing German translation for ' . $translationKey . '.');
}

fwrite(STDOUT, "OpenHomeAlarm alarm duration and reset checks passed.\n");
