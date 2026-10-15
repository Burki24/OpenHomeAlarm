<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    9001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    9002 => ['VariableType' => 3, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    9003 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    9001 => false,
    9002 => 'OK',
    9003 => false
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
    if ($variableID === 9002) {
        return [
            'OPTIONS' => json_encode([
                ['Value' => 'OK', 'Caption' => 'Online'],
                ['Value' => 'FAULT', 'Caption' => 'Offline']
            ], JSON_THROW_ON_ERROR)
        ];
    }

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

    private int $status = 102;

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

    /** @return array<string,int|string> */
    public function TestAttributes(): array
    {
        return $this->attributes;
    }

    public function TestClearWrittenValues(): void
    {
        $this->writtenValues = [];
    }

    public function TestStatus(): int
    {
        return $this->status;
    }

    /** @return array<int,list<int>> */
    public function TestMessages(): array
    {
        ksort($this->messages);

        return $this->messages;
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
        if (!isset($this->messages[$senderID])) {
            return true;
        }

        $this->messages[$senderID] = array_values(array_filter(
            $this->messages[$senderID],
            static fn (int $registeredMessage): bool => $registeredMessage !== $messageID
        ));
        if ($this->messages[$senderID] === []) {
            unset($this->messages[$senderID]);
        }

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

    protected function SetStatus(int $status): void
    {
        $this->status = $status;
    }

    protected function SendDebug(string $message, string $data, int $format): bool
    {
        return true;
    }
}

function assertFaultMonitoring(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function faultInput(
    int $variableID,
    string $name,
    int $faultType,
    string $triggerValue,
    bool $blockArming,
    bool $triggerAlarm
): array {
    return [
        'Enabled'      => true,
        'Name'         => $name,
        'VariableID'   => $variableID,
        'FaultType'    => $faultType,
        'TriggerValue' => $triggerValue,
        'BlockArming'  => $blockArming,
        'TriggerAlarm' => $triggerAlarm
    ];
}

$faultAction = json_encode([
    'actionID'   => '{AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA}',
    'parameters' => ['VALUE' => true]
], JSON_THROW_ON_ERROR);
$faultClearedAction = json_encode([
    'actionID'   => '{BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB}',
    'parameters' => ['VALUE' => false]
], JSON_THROW_ON_ERROR);

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyString('FaultAction', $faultAction);
$instance->TestSetPropertyString('FaultClearedAction', $faultClearedAction);
$instance->TestSetPropertyString('AlarmEscalationSteps', json_encode([[
    'Enabled'           => true,
    'Name'              => '24/7 fault output',
    'DelaySeconds'      => 0,
    'Action'            => ['actionID' => '{CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC}', 'parameters' => ['VALUE' => true]],
    'ApplyHome'         => false,
    'ApplyAway'         => false,
    'ApplyNight'        => false,
    'ApplyAlwaysActive' => true
]], JSON_THROW_ON_ERROR));
$instance->TestSetPropertyString(
    'FaultInputs',
    json_encode([
        faultInput(9001, 'Sabotage Zentrale', 0, 'true', true, true),
        faultInput(9002, 'Funkverbindung', 2, 'FAULT', true, false),
        faultInput(9003, 'Batterie Fenster', 1, 'true', false, false)
    ], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();
$instance->ApplyChanges();
assertFaultMonitoring(
    $instance->TestMessages() === [
        0    => [IPS_KERNELSTARTED],
        9001 => [VM_UPDATE, OM_UNREGISTER],
        9002 => [VM_UPDATE, OM_UNREGISTER],
        9003 => [VM_UPDATE, OM_UNREGISTER]
    ],
    'Every enabled fault input must be monitored continuously.'
);
assertFaultMonitoring(
    ($instance->TestWrittenValues()['ReadyAway'] ?? null) === true,
    'Healthy blocking fault inputs must not affect arming readiness.'
);

// A non-alarming communication fault must be published and block every arming mode.
global $testValues, $testActions;
$testValues[9002] = 'FAULT';
$instance->TestClearWrittenValues();
$instance->MessageSink(1, 9002, VM_UPDATE, ['FAULT', true, false]);
$written = $instance->TestWrittenValues();
assertFaultMonitoring(($written['SystemFault'] ?? null) === true, 'An active fault must set SystemFault.');
assertFaultMonitoring(($written['ActiveFaults'] ?? null) === 'Funkverbindung', 'ActiveFaults must expose the configured name.');
assertFaultMonitoring(($written['BlockingFaults'] ?? null) === 'Funkverbindung', 'BlockingFaults must expose arming blockers.');
assertFaultMonitoring(
    ($written['ReadyHome'] ?? null) === false
    && ($written['ReadyAway'] ?? null) === false
    && ($written['ReadyNight'] ?? null) === false,
    'A blocking system fault must block every arming mode.'
);
assertFaultMonitoring(!array_key_exists('State', $written), 'A non-alarming fault must not enter the Alarm state.');
assertFaultMonitoring(count($testActions) === 1, 'A newly active fault must run the configured fault action once.');
assertFaultMonitoring(
    $testActions[0]['actionID'] === '{AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA}',
    'The configured fault action must be preserved.'
);
assertFaultMonitoring($instance->ArmAway() === false, 'A blocking fault must reject arming.');

// Re-evaluating the same active condition must not duplicate events/actions.
$instance->ApplyChanges();
assertFaultMonitoring(count($testActions) === 1, 'ApplyChanges must not repeat an already active fault action.');

// Clearing the fault restores readiness and runs the clear action.
$testValues[9002] = 'OK';
$instance->TestClearWrittenValues();
$instance->MessageSink(2, 9002, VM_UPDATE, ['OK', true, true]);
$written = $instance->TestWrittenValues();
assertFaultMonitoring(($written['SystemFault'] ?? null) === false, 'Clearing the last fault must reset SystemFault.');
assertFaultMonitoring(($written['ActiveFaults'] ?? null) === '', 'Clearing the last fault must empty ActiveFaults.');
assertFaultMonitoring(($written['BlockingFaults'] ?? null) === '', 'Clearing the last blocking fault must empty BlockingFaults.');
assertFaultMonitoring(($written['ReadyAway'] ?? null) === true, 'Clearing a blocking fault must restore readiness.');
assertFaultMonitoring(count($testActions) === 2, 'Clearing a fault must run the configured clear action once.');
assertFaultMonitoring(
    $testActions[1]['actionID'] === '{BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB}',
    'The configured fault-cleared action must be preserved.'
);

// Clearing one of several active faults must not signal an all-clear prematurely.
$testValues[9002] = 'OK';
$testValues[9003] = false;
$multipleFaults = new OpenHomeAlarm();
$multipleFaults->Create();
$multipleFaults->TestSetPropertyInteger('ExitDelaySeconds', 0);
$multipleFaults->TestSetPropertyString('FaultAction', $faultAction);
$multipleFaults->TestSetPropertyString('FaultClearedAction', $faultClearedAction);
$multipleFaults->TestSetPropertyString(
    'FaultInputs',
    json_encode([
        faultInput(9002, 'Funkverbindung', 2, 'FAULT', false, false),
        faultInput(9003, 'Batterie Fenster', 1, 'true', false, false)
    ], JSON_THROW_ON_ERROR)
);
$multipleFaults->ApplyChanges();
$testActions = [];

$testValues[9002] = 'FAULT';
$multipleFaults->MessageSink(10, 9002, VM_UPDATE, ['FAULT', true, false]);
$testValues[9003] = true;
$multipleFaults->MessageSink(11, 9003, VM_UPDATE, [true, true, false]);
assertFaultMonitoring(count($testActions) === 2, 'Every newly active fault must still run the configured fault action.');

$testValues[9002] = 'OK';
$multipleFaults->TestClearWrittenValues();
$multipleFaults->MessageSink(12, 9002, VM_UPDATE, ['OK', true, true]);
$multipleWritten = $multipleFaults->TestWrittenValues();
assertFaultMonitoring(
    ($multipleWritten['SystemFault'] ?? null) === true
    && ($multipleWritten['ActiveFaults'] ?? null) === 'Batterie Fenster',
    'Clearing one of several active faults must keep the aggregate system fault active.'
);
assertFaultMonitoring(
    count($testActions) === 2,
    'Clearing one of several active faults must not run the configured all-clear action.'
);

$multipleFaults->ApplyChanges();
assertFaultMonitoring(
    count($testActions) === 2,
    'ApplyChanges with a remaining active fault must not run the configured all-clear action.'
);

$testValues[9003] = false;
$multipleFaults->TestClearWrittenValues();
$multipleFaults->MessageSink(13, 9003, VM_UPDATE, [false, true, true]);
$multipleWritten = $multipleFaults->TestWrittenValues();
assertFaultMonitoring(
    ($multipleWritten['SystemFault'] ?? null) === false
    && ($multipleWritten['ActiveFaults'] ?? null) === '',
    'Clearing the last active fault must clear the aggregate system fault.'
);
assertFaultMonitoring(
    count($testActions) === 3
    && $testActions[2]['actionID'] === '{BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB}',
    'Clearing the last active fault must run the configured all-clear action exactly once.'
);

// A tamper input configured for alarm is 24/7 and alarms even while disarmed.
$testValues[9001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(3, 9001, VM_UPDATE, [true, true, false]);
$written = $instance->TestWrittenValues();
assertFaultMonitoring(($written['State'] ?? null) === 4, 'A fault configured for alarm must alarm immediately.');
assertFaultMonitoring(($written['AlarmMemory'] ?? null) === true, 'A fault alarm must set the alarm memory.');
assertFaultMonitoring(
    ($written['LastAlarmSource'] ?? null) === 'Sabotage Zentrale',
    'A fault alarm must preserve the configured fault name as alarm source.'
);
assertFaultMonitoring(($written['LastFaultSource'] ?? null) === 'Sabotage Zentrale', 'LastFaultSource must track the new fault.');
assertFaultMonitoring(is_string($written['LastFaultTime'] ?? null) && $written['LastFaultTime'] !== '', 'LastFaultTime must be published.');
assertFaultMonitoring(
    array_column(array_slice($testActions, -2), 'actionID') === [
        '{AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA}',
        '{CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC}'
    ],
    'An alarm-triggering fault must execute escalation actions assigned to the 24/7 source category.'
);
$faultAlarmStates = json_decode(
    (string) $instance->TestAttributes()['PartitionAlarms'],
    true,
    512,
    JSON_THROW_ON_ERROR
);
assertFaultMonitoring(
    ($faultAlarmStates['main']['AlarmSources'] ?? null) === ['always_active'],
    'An alarm-triggering fault must persist its 24/7 source category.'
);

// Missing variables are fail-safe faults for status/blocking, but do not create Alarm without a positive value match.
$missing = new OpenHomeAlarm();
$missing->Create();
$missing->TestSetPropertyInteger('ExitDelaySeconds', 0);
$missing->TestSetPropertyString(
    'FaultInputs',
    json_encode([
        faultInput(9999, 'Gateway nicht erreichbar', 2, 'true', true, true)
    ], JSON_THROW_ON_ERROR)
);
$missing->TestClearWrittenValues();
$missing->ApplyChanges();
$missingWritten = $missing->TestWrittenValues();
assertFaultMonitoring(($missingWritten['SystemFault'] ?? null) === true, 'A missing configured fault variable must be reported.');
assertFaultMonitoring(($missingWritten['BlockingFaults'] ?? null) === 'Gateway nicht erreichbar', 'A missing blocking fault input must block arming.');
assertFaultMonitoring(!array_key_exists('State', $missingWritten), 'An unreadable fault input must not trigger the main alarm by itself.');

// Different source IDs with the same name must stay visible as separate faults.
$sameNames = new OpenHomeAlarm();
$sameNames->Create();
$sameNames->TestSetPropertyString(
    'FaultInputs',
    json_encode([
        faultInput(9002, 'Funkverbindung', 2, 'FAULT', true, false),
        faultInput(9003, 'Funkverbindung', 1, 'true', true, false)
    ], JSON_THROW_ON_ERROR)
);
$testValues[9002] = 'FAULT';
$testValues[9003] = true;
$sameNames->ApplyChanges();
$sameNamesWritten = $sameNames->TestWrittenValues();
assertFaultMonitoring(
    ($sameNamesWritten['ActiveFaults'] ?? null) === 'Funkverbindung, Funkverbindung',
    'Different fault variable IDs with the same label must remain separate without visible IDs.'
);
assertFaultMonitoring(
    ($sameNamesWritten['BlockingFaults'] ?? null) === 'Funkverbindung, Funkverbindung',
    'Different blocking fault IDs with the same label must not collapse into one entry.'
);

// Duplicate positive variable IDs are rejected without crashing ApplyChanges.
$duplicate = new OpenHomeAlarm();
$duplicate->Create();
$duplicate->TestSetPropertyString(
    'FaultInputs',
    json_encode([
        faultInput(9001, 'A', 0, 'true', false, false),
        faultInput(9001, 'B', 4, 'true', false, false)
    ], JSON_THROW_ON_ERROR)
);
$duplicate->ApplyChanges();
assertFaultMonitoring(
    $duplicate->TestStatus() === 202,
    'Duplicate fault input variable IDs must produce a controlled invalid-configuration status.'
);

// Functionally identical sensor rows are rejected without crashing ApplyChanges.
$duplicateSensors = new OpenHomeAlarm();
$duplicateSensors->Create();
$duplicateSensors->TestSetPropertyString(
    'Sensors',
    json_encode([
        [
            'Name'           => 'Front door',
            'VariableID'     => 9001,
            'Partition_main' => true,
            'ArmAway'        => true,
            'EntryDelay'     => true
        ],
        [
            'Name'           => 'Renamed front door',
            'VariableID'     => 9001,
            'Partition_main' => true,
            'ArmAway'        => true,
            'EntryDelay'     => true
        ]
    ], JSON_THROW_ON_ERROR)
);
$duplicateSensors->ApplyChanges();
assertFaultMonitoring(
    $duplicateSensors->TestStatus() === 202,
    'Functionally identical sensor rows must produce a controlled invalid-configuration status.'
);

// Invalid schedules and alarm actions need their own actionable status instead of a sensor/fault error.
$invalidSchedule = new OpenHomeAlarm();
$invalidSchedule->Create();
$invalidSchedule->TestSetPropertyString(
    'AutomaticArmingSchedules',
    json_encode([[
        'Enabled'   => true,
        'Name'      => 'No weekday',
        'Monday'    => false,
        'Tuesday'   => false,
        'Wednesday' => false,
        'Thursday'  => false,
        'Friday'    => false,
        'Saturday'  => false,
        'Sunday'    => false,
        'Time'      => '08:00',
        'Mode'      => 'away'
    ]], JSON_THROW_ON_ERROR)
);
$invalidSchedule->ApplyChanges();
assertFaultMonitoring(
    $invalidSchedule->TestStatus() === 203,
    'Invalid automatic arming schedules must produce their dedicated configuration status.'
);

$invalidSignalGenerator = new OpenHomeAlarm();
$invalidSignalGenerator->Create();
$invalidSignalGenerator->TestSetPropertyString(
    'AlarmEscalationSteps',
    json_encode([[
        'Enabled'      => true,
        'Name'         => 'Immediate alarm actions',
        'DelaySeconds' => 0,
        'Actions'      => [[
            'Enabled'           => true,
            'Name'              => 'Hall strobe',
            'Action'            => [
                'actionID'   => '{11111111-1111-1111-1111-111111111111}',
                'parameters' => ['VALUE' => true]
            ],
            'ResetMode'         => 0,
            'ResetAction'       => '',
            'SignalGenerator'   => true,
            'ApplicableTo'      => 'normal',
            'ApplyHome'         => true,
            'ApplyAway'         => false,
            'ApplyNight'        => true,
            'ApplyAlwaysActive' => true
        ]]
    ]], JSON_THROW_ON_ERROR)
);
$invalidSignalGenerator->ApplyChanges();
assertFaultMonitoring(
    $invalidSignalGenerator->TestStatus() === 204,
    'A signal generator without reset must produce the dedicated alarm-action status.'
);

/**
 * @param array<string,mixed> $action
 */
function detailedAlarmActionStatusCaption(array $action): string
{
    $instance = new OpenHomeAlarm();
    $instance->Create();
    $instance->TestSetPropertyString(
        'AlarmEscalationSteps',
        json_encode([[
            'Enabled'      => true,
            'Name'         => 'Detailed validation',
            'DelaySeconds' => 0,
            'Actions'      => [$action]
        ]], JSON_THROW_ON_ERROR)
    );
    $instance->ApplyChanges();
    assertFaultMonitoring(
        $instance->TestStatus() === 204,
        sprintf('Invalid alarm action "%s" must produce status 204.', $action['Name'] ?? '')
    );
    $form = json_decode($instance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
    foreach ($form['status'] ?? [] as $status) {
        if (($status['code'] ?? null) === 204) {
            return (string) ($status['caption'] ?? '');
        }
    }

    return '';
}

$validAlarmAction = [
    'Enabled'           => true,
    'Name'              => 'Test action',
    'Action'            => [
        'actionID'   => '{11111111-1111-1111-1111-111111111111}',
        'parameters' => ['VALUE' => true]
    ],
    'ResetMode'         => 0,
    'ResetAction'       => '',
    'SignalGenerator'   => false,
    'ApplicableTo'      => 'normal',
    'ApplyHome'         => true,
    'ApplyAway'         => false,
    'ApplyNight'        => false,
    'ApplyAlwaysActive' => false
];

$missingActionCaption = detailedAlarmActionStatusCaption(array_replace(
    $validAlarmAction,
    ['Name' => 'Empty hallway action', 'Action' => '']
));
assertFaultMonitoring(
    str_contains($missingActionCaption, 'Empty hallway action')
        && str_contains($missingActionCaption, 'no action is selected')
        && str_contains($missingActionCaption, 'Open the row'),
    'Status 204 must name the row and explain how to select a missing action.'
);

$invalidAutomaticResetCaption = detailedAlarmActionStatusCaption(array_replace(
    $validAlarmAction,
    [
        'Name'      => 'Spoken warning',
        'Action'    => [
            'actionID'   => '{11111111-1111-1111-1111-111111111111}',
            'parameters' => ['VALUE' => 'Alarm']
        ],
        'ResetMode' => 1
    ]
));
assertFaultMonitoring(
    str_contains($invalidAutomaticResetCaption, 'Spoken warning')
        && str_contains($invalidAutomaticResetCaption, 'Invert Boolean automatically')
        && str_contains($invalidAutomaticResetCaption, 'Use custom reset action'),
    'Status 204 must explain that automatic inversion only works for Boolean set-value actions.'
);

$missingCustomResetCaption = detailedAlarmActionStatusCaption(array_replace(
    $validAlarmAction,
    ['Name' => 'Outside strobe', 'ResetMode' => 2]
));
assertFaultMonitoring(
    str_contains($missingCustomResetCaption, 'Outside strobe')
        && str_contains($missingCustomResetCaption, 'Use custom reset action')
        && str_contains($missingCustomResetCaption, 'no reset action is selected'),
    'Status 204 must explain how to complete a missing custom reset action.'
);

$signalGeneratorCaption = detailedAlarmActionStatusCaption(array_replace(
    $validAlarmAction,
    ['Name' => 'Hall strobe', 'SignalGenerator' => true]
));
assertFaultMonitoring(
    str_contains($signalGeneratorCaption, 'Hall strobe')
        && str_contains($signalGeneratorCaption, 'Signal generator')
        && str_contains($signalGeneratorCaption, 'No reset')
        && str_contains($signalGeneratorCaption, 'Invert Boolean automatically')
        && str_contains($signalGeneratorCaption, 'Use custom reset action'),
    'Status 204 must name a signal generator and list both valid reset choices.'
);

$missingCategoryCaption = detailedAlarmActionStatusCaption(array_replace(
    $validAlarmAction,
    [
        'Name'              => 'Unassigned siren',
        'ApplyHome'         => false,
        'ApplyAway'         => false,
        'ApplyNight'        => false,
        'ApplyAlwaysActive' => false
    ]
));
assertFaultMonitoring(
    str_contains($missingCategoryCaption, 'Unassigned siren')
        && str_contains($missingCategoryCaption, 'no trigger category is selected')
        && str_contains($missingCategoryCaption, 'Home, Away, Night or 24/7'),
    'Status 204 must list the exact trigger categories required by an unassigned action.'
);

// Configuration form and dynamic fault-value selection must expose the new system-monitoring controls.
$form = json_decode($instance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
$systemPanel = null;
foreach ($form['elements'] ?? [] as $element) {
    if (($element['type'] ?? null) === 'ExpansionPanel' && ($element['caption'] ?? null) === 'System monitoring') {
        $systemPanel = $element;
        break;
    }
}
assertFaultMonitoring(is_array($systemPanel), 'A18 must add a System monitoring panel.');
$faultList = null;
$faultImporter = null;
foreach ($systemPanel['items'] ?? [] as $item) {
    if (($item['type'] ?? null) === 'List' && ($item['name'] ?? null) === 'FaultInputs') {
        $faultList = $item;
    }
    if (($item['type'] ?? null) === 'PopupButton' && ($item['name'] ?? null) === 'FaultInputImport') {
        $faultImporter = $item;
    }
}
assertFaultMonitoring(is_array($faultList), 'System monitoring must contain the FaultInputs list.');
assertFaultMonitoring(
    is_array($faultImporter)
        && ($faultImporter['caption'] ?? null) === 'Find and add fault inputs'
        && str_contains(json_encode($faultImporter, JSON_THROW_ON_ERROR), 'FaultImportCandidates')
        && str_contains(
            (string) ($faultImporter['popup']['buttons'][0]['onClick'] ?? ''),
            'OHA_AddFaultInputCandidates'
        ),
    'System monitoring must offer bulk variable search with its add action in the popup footer.'
);
assertFaultMonitoring(
    in_array('PartitionID', array_column($faultList['columns'] ?? [], 'name'), true),
    'Fault input configuration must expose its alarm partition.'
);
assertFaultMonitoring(
    isset($faultList['values']) && is_array($faultList['values']) && count($faultList['values']) === 3,
    'Nested fault lists must receive their non-persistent trigger editor values.'
);
$editForm = $instance->GetFaultInputEditForm(faultInput(9002, 'Funkverbindung', 2, 'FAULT', true, false));
$selection = null;
$partitionSelection = null;
foreach ($editForm as $field) {
    if (($field['name'] ?? null) === 'TriggerValueSelection') {
        $selection = $field;
    }
    if (($field['name'] ?? null) === 'PartitionID') {
        $partitionSelection = $field;
    }
}
assertFaultMonitoring(
    ($partitionSelection['type'] ?? null) === 'Select'
    && ($partitionSelection['value'] ?? null) === 'main',
    'Fault input editor must resolve empty assignments to the default partition.'
);

$orphanedFaultEditForm = $instance->GetFaultInputEditForm(array_merge(
    faultInput(9002, 'Funkverbindung', 2, 'FAULT', true, false),
    ['PartitionID' => 'schuppen']
));
$orphanedPartitionSelection = null;
foreach ($orphanedFaultEditForm as $field) {
    if (($field['name'] ?? null) === 'PartitionID') {
        $orphanedPartitionSelection = $field;
        break;
    }
}
assertFaultMonitoring(
    ($orphanedPartitionSelection['value'] ?? null) === 'schuppen'
        && in_array('schuppen', array_column($orphanedPartitionSelection['options'] ?? [], 'value'), true),
    'Fault input editor must keep an orphaned partition visible until the user selects a valid area.'
);
assertFaultMonitoring(($selection['value'] ?? null) === 'FAULT', 'The stored fault value must be restored when editing.');
assertFaultMonitoring(
    array_column($selection['options'] ?? [], 'caption') === ['Online', 'Offline'],
    'String fault variables must use their Symcon presentation captions.'
);

$history = json_decode($instance->GetEventHistory(), true, 512, JSON_THROW_ON_ERROR);
$events = array_column($history, 'Event');
assertFaultMonitoring(in_array('fault_activated', $events, true), 'Fault activation must be written to event history.');
assertFaultMonitoring(in_array('fault_cleared', $events, true), 'Fault clearing must be written to event history.');

$locale = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$translations = $locale['translations']['de'] ?? [];
foreach (['System monitoring', 'Tamper and fault inputs', 'Block arming', 'Trigger alarm (24/7)', 'System fault', 'Active faults', 'Blocking faults'] as $translationKey) {
    assertFaultMonitoring(isset($translations[$translationKey]), 'Missing German translation for ' . $translationKey . '.');
}

fwrite(STDOUT, "OpenHomeAlarm fault and tamper monitoring checks passed.\n");
