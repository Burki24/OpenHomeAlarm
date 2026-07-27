<?php

declare(strict_types=1);

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    3001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    3002 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    3001 => false,
    3002 => false
];

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

class IPSModuleStrict
{
    /** @var array<string,mixed> */
    private array $properties = [];

    /** @var array<string,int> */
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

    public function TestSetAttributeInteger(string $name, int $value): void
    {
        $this->attributes[$name] = $value;
    }

    /** @return array<string,array{interval:int,script:string}> */
    public function TestTimers(): array
    {
        return $this->timers;
    }

    /** @return array<string,int> */
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

    protected function ReadAttributeInteger(string $name): int
    {
        return $this->attributes[$name] ?? 0;
    }

    protected function WriteAttributeInteger(string $name, int $value): void
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
}

function assertDelay(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function delaySensor(int $variableID, bool $entryDelay): array
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

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 10);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 5);
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([delaySensor(3001, true), delaySensor(3002, false)], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();

$timers = $instance->TestTimers();
assertDelay(
    ($timers['ExitDelay']['interval'] ?? null) === 0
    && ($timers['EntryDelay']['interval'] ?? null) === 0,
    'A5 timers must be registered disabled.'
);
assertDelay(
    ($timers['ExitDelay']['script'] ?? '') === 'OHA_CompleteExitDelay($_IPS[\'TARGET\']);'
    && ($timers['EntryDelay']['script'] ?? '') === 'OHA_CompleteEntryDelay($_IPS[\'TARGET\']);',
    'A5 timers must call their public module callbacks.'
);

// A successful arming request starts the configured exit delay.
assertDelay($instance->ArmAway() === true, 'Away arming must start when all Away sensors are inactive.');
assertDelay(
    ($instance->TestWrittenValues()['Mode'] ?? null) === 2
    && ($instance->TestWrittenValues()['State'] ?? null) === 1,
    'A positive exit delay must enter Away/Exit delay instead of arming immediately.'
);
assertDelay(
    ($instance->TestTimers()['ExitDelay']['interval'] ?? null) === 10000,
    'Exit delay timer must use the configured number of seconds.'
);
assertDelay(
    ($instance->TestAttributes()['ExitDelayDeadline'] ?? 0) >= time() + 9,
    'Exit delay deadline must be persisted.'
);

// Sensor changes during exit delay only affect readiness. The final check happens at expiry.
global $testValues;
$testValues[3001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(1, 3001, VM_UPDATE, [true, true, false]);
assertDelay(
    ($instance->TestWrittenValues()['ReadyToArm'] ?? null) === false
    && !array_key_exists('State', $instance->TestWrittenValues()),
    'A sensor opening during exit delay must not start an entry delay or alarm immediately.'
);
$instance->TestClearWrittenValues();
$instance->CompleteExitDelay();
assertDelay(
    $instance->TestWrittenValues() === ['ReadyToArm' => false, 'State' => 0, 'Mode' => 0],
    'An open relevant sensor at exit-delay expiry must safely cancel arming.'
);
assertDelay(
    ($instance->TestTimers()['ExitDelay']['interval'] ?? null) === 0,
    'Completing or cancelling exit delay must disable its timer.'
);

// A clear exit route allows the delay to complete into Armed.
$testValues[3001] = false;
$instance->TestClearWrittenValues();
assertDelay($instance->ArmAway() === true, 'Away arming must restart after the sensor clears.');
$instance->TestClearWrittenValues();
$instance->CompleteExitDelay();
assertDelay(
    ($instance->TestWrittenValues()['State'] ?? null) === 2,
    'A clear exit route must enter Armed when the exit delay expires.'
);

// A delayed sensor starts one entry countdown. Closing or retriggering it must not cancel/restart it.
$testValues[3001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(2, 3001, VM_UPDATE, [true, true, false]);
assertDelay(
    ($instance->TestWrittenValues()['State'] ?? null) === 3,
    'A configured entry sensor must enter Entry delay while armed.'
);
$entryDeadline = $instance->TestAttributes()['EntryDelayDeadline'] ?? 0;
assertDelay(
    ($instance->TestTimers()['EntryDelay']['interval'] ?? null) === 5000 && $entryDeadline >= time() + 4,
    'Entry delay timer and persisted deadline must use the configured duration.'
);

$testValues[3001] = false;
$instance->TestClearWrittenValues();
$instance->MessageSink(3, 3001, VM_UPDATE, [false, true, true]);
assertDelay(
    !array_key_exists('State', $instance->TestWrittenValues())
    && ($instance->TestAttributes()['EntryDelayDeadline'] ?? 0) === $entryDeadline,
    'Closing an entry sensor must not cancel its running countdown.'
);

$testValues[3001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(4, 3001, VM_UPDATE, [true, true, false]);
assertDelay(
    !array_key_exists('State', $instance->TestWrittenValues())
    && ($instance->TestAttributes()['EntryDelayDeadline'] ?? 0) === $entryDeadline,
    'Retriggering a delayed sensor during Entry delay must not restart the countdown.'
);

// Any immediate sensor escalates a running entry delay to Alarm.
$testValues[3002] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(5, 3002, VM_UPDATE, [true, true, false]);
assertDelay(
    ($instance->TestWrittenValues()['State'] ?? null) === 4,
    'An immediate sensor must enter Alarm even while Entry delay is already running.'
);
assertDelay(
    ($instance->TestTimers()['EntryDelay']['interval'] ?? null) === 0
    && ($instance->TestAttributes()['EntryDelayDeadline'] ?? null) === 0,
    'Entering Alarm must cancel a running entry timer.'
);

// Disarming always cancels both countdowns.
$instance->TestClearWrittenValues();
assertDelay($instance->Disarm() === true, 'Disarm must succeed from Alarm.');
assertDelay(
    $instance->TestWrittenValues() === ['State' => 0, 'Mode' => 0],
    'Disarm must return to None/Disarmed.'
);
assertDelay(
    ($instance->TestTimers()['ExitDelay']['interval'] ?? null) === 0
    && ($instance->TestTimers()['EntryDelay']['interval'] ?? null) === 0,
    'Disarm must cancel all delay timers.'
);

// Entry-delay expiry changes only the internal state; external alarm actions are not part of A5.
$testValues[3001] = false;
$testValues[3002] = false;
$instance->TestClearWrittenValues();
assertDelay($instance->ArmAway() === true, 'Away arming must be possible again.');
$instance->CompleteExitDelay();
$testValues[3001] = true;
$instance->MessageSink(6, 3001, VM_UPDATE, [true, true, false]);
$instance->TestClearWrittenValues();
$instance->CompleteEntryDelay();
assertDelay(
    $instance->TestWrittenValues() === ['State' => 4],
    'Entry-delay expiry must enter the internal Alarm state.'
);

// A zero duration disables the corresponding delay.
$instance->Disarm();
$testValues[3001] = false;
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$instance->TestClearWrittenValues();
assertDelay($instance->ArmAway() === true, 'Zero exit delay must still permit arming.');
assertDelay(
    ($instance->TestWrittenValues()['State'] ?? null) === 2
    && ($instance->TestTimers()['ExitDelay']['interval'] ?? null) === 0,
    'Zero exit delay must arm immediately without a timer.'
);
$testValues[3001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(7, 3001, VM_UPDATE, [true, true, false]);
assertDelay(
    ($instance->TestWrittenValues()['State'] ?? null) === 4,
    'Zero entry delay must make a delayed sensor enter Alarm immediately.'
);

// ApplyChanges restores a persisted running countdown because Symcon timers are stateless.
$restoreInstance = new OpenHomeAlarm();
$restoreInstance->Create();
$restoreInstance->TestSetPropertyString('Sensors', json_encode([delaySensor(3001, true)], JSON_THROW_ON_ERROR));
$restoreInstance->TestSetCurrentValue('Mode', 2);
$restoreInstance->TestSetCurrentValue('State', 1);
$restoreInstance->TestSetAttributeInteger('ExitDelayDeadline', time() + 20);
$testValues[3001] = false;
$restoreInstance->TestClearWrittenValues();
$restoreInstance->ApplyChanges();
$restoredInterval = $restoreInstance->TestTimers()['ExitDelay']['interval'] ?? 0;
assertDelay(
    $restoredInterval > 0 && $restoredInterval <= 20000,
    'ApplyChanges must restore the remaining exit-delay timer from its persisted deadline.'
);

$restoreInstance->TestSetCurrentValue('State', 3);
$restoreInstance->TestSetAttributeInteger('EntryDelayDeadline', time() - 1);
$restoreInstance->TestClearWrittenValues();
$restoreInstance->ApplyChanges();
assertDelay(
    ($restoreInstance->TestWrittenValues()['State'] ?? null) === 4,
    'An already expired persisted entry delay must complete immediately during ApplyChanges.'
);

// If the same variable is configured more than once, an immediate row must win over a delayed row.
$duplicateInstance = new OpenHomeAlarm();
$duplicateInstance->Create();
$duplicateInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$duplicateInstance->TestSetPropertyInteger('EntryDelaySeconds', 5);
$duplicateInstance->TestSetPropertyString(
    'Sensors',
    json_encode([delaySensor(3001, true), delaySensor(3001, false)], JSON_THROW_ON_ERROR)
);
$testValues[3001] = false;
$duplicateInstance->TestClearWrittenValues();
assertDelay($duplicateInstance->ArmAway() === true, 'Duplicate-sensor test must arm while the variable is inactive.');
$testValues[3001] = true;
$duplicateInstance->TestClearWrittenValues();
$duplicateInstance->MessageSink(8, 3001, VM_UPDATE, [true, true, false]);
assertDelay(
    ($duplicateInstance->TestWrittenValues()['State'] ?? null) === 4,
    'An immediate configuration row must take precedence when the same variable also has a delayed row.'
);

fwrite(STDOUT, "OpenHomeAlarm delay checks passed.\n");
