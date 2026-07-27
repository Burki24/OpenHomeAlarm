<?php

declare(strict_types=1);

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
$instance->TestSetPropertyInteger('FaultActionEnabled', 1);
$instance->TestSetPropertyString('FaultAction', $faultAction);
$instance->TestSetPropertyInteger('FaultClearedActionEnabled', 1);
$instance->TestSetPropertyString('FaultClearedAction', $faultClearedAction);
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
    $instance->TestMessages() === [9001 => [VM_UPDATE], 9002 => [VM_UPDATE], 9003 => [VM_UPDATE]],
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

// Duplicate positive variable IDs are rejected to keep transition tracking unambiguous.
$duplicate = new OpenHomeAlarm();
$duplicate->Create();
$duplicate->TestSetPropertyString(
    'FaultInputs',
    json_encode([
        faultInput(9001, 'A', 0, 'true', false, false),
        faultInput(9001, 'B', 4, 'true', false, false)
    ], JSON_THROW_ON_ERROR)
);
try {
    $duplicate->ApplyChanges();
    throw new RuntimeException('Duplicate fault input variable IDs must be rejected.');
} catch (UnexpectedValueException) {
}

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
foreach ($systemPanel['items'] ?? [] as $item) {
    if (($item['type'] ?? null) === 'List' && ($item['name'] ?? null) === 'FaultInputs') {
        $faultList = $item;
        break;
    }
}
assertFaultMonitoring(is_array($faultList), 'System monitoring must contain the FaultInputs list.');
assertFaultMonitoring(
    isset($faultList['values']) && is_array($faultList['values']) && count($faultList['values']) === 3,
    'Nested fault lists must receive their non-persistent trigger editor values.'
);
$editForm = $instance->GetFaultInputEditForm(faultInput(9002, 'Funkverbindung', 2, 'FAULT', true, false));
$selection = null;
foreach ($editForm as $field) {
    if (($field['name'] ?? null) === 'TriggerValueSelection') {
        $selection = $field;
        break;
    }
}
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
