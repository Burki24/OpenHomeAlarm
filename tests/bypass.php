<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    7001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    7002 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    7003 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    7001 => true,
    7002 => false,
    7003 => false
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

    /** @var array<string,mixed> */
    private array $values = [];

    /** @var array<int,list<int>> */
    private array $messages = [];

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

    public function TestValue(string $ident): mixed
    {
        return $this->values[$ident] ?? null;
    }

    public function TestAttributeString(string $name): string
    {
        $value = $this->attributes[$name] ?? '';

        return is_string($value) ? $value : '';
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
        $this->values[$ident] = $value;
    }

    protected function GetValue(string $ident): mixed
    {
        return $this->values[$ident] ?? null;
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
        unset($this->messages[$senderID]);

        return true;
    }

    /** @return array<int,list<int>> */
    protected function GetMessageList(): array
    {
        return $this->messages;
    }

    protected function Translate(string $text): string
    {
        return $text;
    }

    protected function SendDebug(string $message, mixed $data, int $format): void
    {
    }
}

function assertBypass(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function bypassSensor(
    int $variableID,
    string $name,
    bool $armHome = false,
    bool $armAway = false,
    bool $armNight = false,
    bool $alwaysActive = false,
    bool $enabled = true
): array {
    return [
        'Enabled'      => $enabled,
        'Name'         => $name,
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => 'true',
        'ArmHome'      => $armHome,
        'ArmAway'      => $armAway,
        'ArmNight'     => $armNight,
        'AlwaysActive' => $alwaysActive,
        'EntryDelay'   => false
    ];
}

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 0);

$sensors = [
    bypassSensor(7001, 'Haustür', armHome: true, armAway: true),
    bypassSensor(7002, 'Rauchmelder', alwaysActive: true),
    bypassSensor(7003, 'Küchenfenster', armAway: true),
    bypassSensor(9999, 'Defekter Nachtkontakt', armNight: true)
];
$instance->TestSetPropertyString('Sensors', json_encode($sensors, JSON_THROW_ON_ERROR));
$instance->ApplyChanges();

assertBypass($instance->TestValue('ReadyHome') === false, 'The triggered Home sensor must initially block Home.');
assertBypass($instance->TestValue('ReadyAway') === false, 'The triggered shared sensor must initially block Away.');
assertBypass($instance->TestValue('ReadyNight') === false, 'A missing Night sensor must fail safe before bypassing.');

assertBypass($instance->BypassSensor(7001) === true, 'A normal arming sensor must be bypassable while disarmed.');
assertBypass($instance->TestValue('BypassedSensors') === 'Haustür', 'The bypass status must expose the bypassed sensor name.');
assertBypass($instance->TestValue('ReadyHome') === true, 'Bypassing the only Home blocker must make Home ready.');
assertBypass($instance->TestValue('ReadyAway') === true, 'Bypassing the shared blocker must make Away ready.');
assertBypass(
    json_decode($instance->TestAttributeString('BypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [7001],
    'The bypassed variable ID must be stored persistently.'
);

assertBypass($instance->BypassSensor(9999) === true, 'A missing configured arming sensor must remain explicitly bypassable.');
assertBypass($instance->TestValue('ReadyNight') === true, 'Bypassing a missing Night sensor must make Night ready.');
assertBypass(
    $instance->TestValue('BypassedSensors') === 'Haustür, Defekter Nachtkontakt',
    'Multiple bypasses must be published in deterministic sensor order.'
);

assertBypass($instance->BypassSensor(7002) === false, 'A 24/7 sensor must never be bypassable.');
assertBypass($instance->ArmHome() === true, 'Home arming must succeed with its blocker bypassed.');
assertBypass($instance->TestValue('State') === 2, 'The system must enter Armed after successful bypass-assisted arming.');
assertBypass($instance->BypassSensor(7003) === false, 'New bypasses must be rejected while armed.');
assertBypass($instance->RemoveSensorBypass(7001) === false, 'Existing bypasses must be immutable while armed.');
assertBypass($instance->ClearSensorBypasses() === false, 'All bypasses must not be clearable while armed.');

$instance->MessageSink(1, 7001, VM_UPDATE, [true, true, 0]);
assertBypass($instance->TestValue('State') === 2, 'A bypassed sensor update must not trigger an alarm while armed.');

assertBypass($instance->Disarm() === true, 'Disarming must always succeed.');
assertBypass($instance->TestValue('State') === 0 && $instance->TestValue('Mode') === 0, 'Disarming must return to None/Disarmed.');
assertBypass($instance->TestValue('BypassedSensors') === '', 'Disarming must clear every temporary bypass.');
assertBypass(
    json_decode($instance->TestAttributeString('BypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'The persistent bypass list must be empty after disarming.'
);
assertBypass($instance->TestValue('ReadyHome') === false, 'After bypass cleanup, the still-triggered Home sensor must block Home again.');
assertBypass($instance->TestValue('ReadyNight') === false, 'After bypass cleanup, the missing Night sensor must block Night again.');

assertBypass($instance->BypassSensor(7003) === true, 'Another normal sensor must be bypassable while disarmed.');
assertBypass($instance->RemoveSensorBypass(7003) === true, 'One bypass must be removable while disarmed.');
assertBypass($instance->TestValue('BypassedSensors') === '', 'Removing the last bypass must clear the status text.');

assertBypass($instance->BypassSensor(7003) === true, 'The sensor must be bypassable again for the clear-all test.');
assertBypass($instance->ClearSensorBypasses() === true, 'ClearSensorBypasses must succeed while disarmed.');
assertBypass($instance->TestValue('BypassedSensors') === '', 'ClearSensorBypasses must clear the published bypass list.');

$instance->BypassSensor(7003);
$reconfiguredSensors = [
    bypassSensor(7001, 'Haustür', armHome: true, armAway: true),
    bypassSensor(7002, 'Rauchmelder', alwaysActive: true),
    bypassSensor(7003, 'Küchenfenster', armAway: true, alwaysActive: true)
];
$instance->TestSetPropertyString('Sensors', json_encode($reconfiguredSensors, JSON_THROW_ON_ERROR));
$instance->ApplyChanges();
assertBypass(
    json_decode($instance->TestAttributeString('BypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'ApplyChanges must remove a bypass when that sensor becomes 24/7 active.'
);
assertBypass($instance->TestValue('BypassedSensors') === '', 'Stale bypass status must be cleared after reconfiguration.');

fwrite(STDOUT, "OpenHomeAlarm temporary sensor bypass checks passed.\n");
