<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    8001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    8002 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    8003 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    8004 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    8005 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    8006 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    8001 => false,
    8002 => false,
    8003 => false,
    8004 => false,
    8005 => false,
    8006 => false
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

function assertRestartRecovery(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function restartSensor(
    int $variableID,
    string $name,
    bool $entryDelay = false,
    bool $armHome = false,
    bool $armAway = true,
    bool $armNight = false
): array {
    return [
        'Enabled'      => true,
        'Name'         => $name,
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => 'true',
        'ArmHome'      => $armHome,
        'ArmAway'      => $armAway,
        'ArmNight'     => $armNight,
        'AlwaysActive' => false,
        'EntryDelay'   => $entryDelay
    ];
}

/**
 * @param list<array<string,mixed>> $sensors
 */
function createArmedRestartInstance(array $sensors, int $entryDelaySeconds = 5): OpenHomeAlarm
{
    $instance = new OpenHomeAlarm();
    $instance->Create();
    $instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
    $instance->TestSetPropertyInteger('EntryDelaySeconds', $entryDelaySeconds);
    $instance->TestSetPropertyString('Sensors', json_encode($sensors, JSON_THROW_ON_ERROR));
    $instance->TestClearWrittenValues();

    assertRestartRecovery($instance->ArmAway() === true, 'Restart test setup must arm Away successfully.');
    assertRestartRecovery(
        ($instance->TestWrittenValues()['State'] ?? null) === 2,
        'Restart test setup must reach Armed immediately.'
    );
    $instance->TestClearWrittenValues();

    return $instance;
}

global $testValues;

// ApplyChanges must defer runtime access until Symcon reports a ready kernel.
$immediateInstance = createArmedRestartInstance([
    restartSensor(8001, 'Front door')
]);
$testValues[8001] = true;
TestSetKernelRunlevel(0);
$immediateInstance->ApplyChanges();
assertRestartRecovery(
    !array_key_exists('State', $immediateInstance->TestWrittenValues()),
    'ApplyChanges must not evaluate sensors before the Symcon kernel is ready.'
);

// The kernel-start message must initialize subscriptions and recover the current sensor state.
TestSetKernelRunlevel(KR_READY);
$immediateInstance->MessageSink(1, 0, IPS_KERNELSTARTED, []);
$immediateWrites = $immediateInstance->TestWrittenValues();
assertRestartRecovery(
    ($immediateWrites['State'] ?? null) === 4,
    'Kernel-ready initialization must enter Alarm when a relevant immediate sensor is already triggered after restart.'
);
assertRestartRecovery(
    ($immediateWrites['AlarmMemory'] ?? null) === true
    && ($immediateWrites['LastAlarmSource'] ?? null) === 'Front door',
    'Restart detection must use the regular alarm path and record the triggering sensor.'
);

// A currently triggered delayed sensor starts a fresh entry delay after restart.
$testValues[8002] = false;
$delayedInstance = createArmedRestartInstance([
    restartSensor(8002, 'Entrance door', entryDelay: true)
], 7);
$testValues[8002] = true;
$delayedInstance->ApplyChanges();
$delayedWrites = $delayedInstance->TestWrittenValues();
assertRestartRecovery(
    ($delayedWrites['State'] ?? null) === 3
    && ($delayedWrites['DelaySource'] ?? null) === 'Entrance door'
    && ($delayedWrites['DelayRemaining'] ?? null) === 7,
    'ApplyChanges must start the configured entry delay for a delayed sensor that became active while offline.'
);
assertRestartRecovery(
    ($delayedInstance->TestTimers()['EntryDelay']['interval'] ?? null) === 7000
    && ($delayedInstance->TestTimers()['DelayStatus']['interval'] ?? null) === 1000,
    'Restart-triggered entry delay must use the normal timer and countdown status.'
);

// A restored entry delay must not be restarted, but another immediate sensor must still escalate it.
$testValues[8002] = false;
$testValues[8003] = false;
$escalationInstance = createArmedRestartInstance([
    restartSensor(8002, 'Entrance door', entryDelay: true),
    restartSensor(8003, 'Living room motion')
], 10);
$testValues[8002] = true;
$escalationInstance->MessageSink(1, 8002, VM_UPDATE, [true, true, false]);
$entryDeadline = $escalationInstance->TestAttributes()['EntryDelayDeadline'] ?? 0;
assertRestartRecovery($entryDeadline > time(), 'Entry delay setup must persist a future deadline.');

$testValues[8003] = true;
$escalationInstance->TestClearWrittenValues();
$escalationInstance->ApplyChanges();
$escalationWrites = $escalationInstance->TestWrittenValues();
assertRestartRecovery(
    ($escalationWrites['State'] ?? null) === 4
    && ($escalationWrites['LastAlarmSource'] ?? null) === 'Living room motion',
    'An immediate sensor already active after restart must escalate a restored entry delay to Alarm.'
);
assertRestartRecovery(
    ($escalationInstance->TestTimers()['EntryDelay']['interval'] ?? null) === 0
    && ($escalationInstance->TestAttributes()['EntryDelayDeadline'] ?? null) === 0,
    'Escalating a restored entry delay must cancel its timer and persisted deadline.'
);

// Sensors assigned only to another mode must not alarm the current mode after restart.
$testValues[8004] = false;
$otherModeInstance = createArmedRestartInstance([
    restartSensor(8004, 'Home-only window', armHome: true, armAway: false)
]);
$testValues[8004] = true;
$otherModeInstance->ApplyChanges();
assertRestartRecovery(
    !array_key_exists('State', $otherModeInstance->TestWrittenValues()),
    'A sensor that is irrelevant for the active mode must not enter Alarm after restart.'
);

// An unreadable sensor is a system fault, but not a confirmed alarm signal.
$testValues[8006] = false;
$unreadableInstance = createArmedRestartInstance([
    restartSensor(8006, 'Temporarily unreadable window')
]);
unset($testValues[8006]);
$unreadableInstance->TestClearWrittenValues();
$unreadableInstance->ApplyChanges();
$unreadableWrites = $unreadableInstance->TestWrittenValues();
assertRestartRecovery(
    !array_key_exists('State', $unreadableWrites)
    && ($unreadableWrites['SystemFault'] ?? null) === true
    && ($unreadableWrites['ReadyAway'] ?? null) === false,
    'An unreadable sensor after restart must block arming as a system fault without entering Alarm.'
);

// A temporary bypass must remain effective across ApplyChanges.
$testValues[8005] = false;
$bypassInstance = new OpenHomeAlarm();
$bypassInstance->Create();
$bypassInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$bypassInstance->TestSetPropertyString(
    'Sensors',
    json_encode([restartSensor(8005, 'Bypassed window')], JSON_THROW_ON_ERROR)
);
assertRestartRecovery($bypassInstance->BypassSensor(8005) === true, 'Restart bypass setup must accept the sensor.');
assertRestartRecovery($bypassInstance->ArmAway() === true, 'A bypassed sensor must allow Away arming.');
$testValues[8005] = true;
$bypassInstance->TestClearWrittenValues();
$bypassInstance->ApplyChanges();
assertRestartRecovery(
    !array_key_exists('State', $bypassInstance->TestWrittenValues()),
    'A temporarily bypassed sensor must stay ignored after ApplyChanges.'
);

fwrite(STDOUT, "OpenHomeAlarm restart recovery checks passed.\n");
