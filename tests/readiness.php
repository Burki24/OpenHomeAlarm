<?php

declare(strict_types=1);

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    6001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    6002 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    6003 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    6001 => false,
    6002 => false,
    6003 => false
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
    /** @var array<string,int|string> */
    private array $attributes = [];

    /** @var array<string,mixed> */
    private array $values = [];

    public function Create(): void
    {
    }

    public function Destroy(): void
    {
    }

    public function ApplyChanges(): void
    {
    }

    public function TestValue(string $ident): mixed
    {
        return $this->values[$ident] ?? null;
    }

    protected function RegisterPropertyString(string $name, string $default): void
    {
    }

    protected function RegisterPropertyInteger(string $name, int $default): void
    {
    }

    protected function ReadPropertyInteger(string $name): int
    {
        return 0;
    }

    protected function RegisterAttributeInteger(string $name, int $default): void
    {
    }

    protected function RegisterAttributeString(string $name, string $default): void
    {
        if (!array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $default;
        }
    }
    protected function ReadAttributeString(string $name): string
    {
        $value = $this->attributes[$name] ?? '';

        return is_string($value) ? $value : '';
    }

    protected function WriteAttributeString(string $name, string $value): void
    {
        $this->attributes[$name] = $value;
    }

    protected function RegisterTimer(string $name, int $interval, string $script): bool
    {
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

    protected function Translate(string $text): string
    {
        return $text;
    }
}

function assertReadiness(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function readinessSensor(
    int $variableID,
    bool $armHome = false,
    bool $armAway = false,
    bool $armNight = false,
    bool $alwaysActive = false,
    bool $enabled = true
): array {
    return [
        'Enabled'      => $enabled,
        'Name'         => 'Test ' . $variableID,
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
$evaluateReadiness = new ReflectionMethod(OpenHomeAlarm::class, 'EvaluateReadiness');

$sensors = [
    readinessSensor(6001, armHome: true),
    readinessSensor(6002, armAway: true, armNight: true),
    readinessSensor(6003, alwaysActive: true)
];

$readiness = $evaluateReadiness->invoke($instance, $sensors);
assertReadiness(
    $readiness === ['global' => true, 'home' => true, 'away' => true, 'night' => true],
    'Inactive sensors must leave every arming mode ready.'
);

global $testValues;
$testValues[6001] = true;
$readiness = $evaluateReadiness->invoke($instance, $sensors);
assertReadiness(
    $readiness === ['global' => false, 'home' => false, 'away' => true, 'night' => true],
    'A Home-only trigger must block Home without blocking Away or Night.'
);

$testValues[6001] = false;
$testValues[6002] = true;
$readiness = $evaluateReadiness->invoke($instance, $sensors);
assertReadiness(
    $readiness === ['global' => false, 'home' => true, 'away' => false, 'night' => false],
    'A sensor assigned to Away and Night must block exactly those two modes.'
);

$testValues[6002] = false;
$testValues[6003] = true;
$readiness = $evaluateReadiness->invoke($instance, $sensors);
assertReadiness(
    $readiness === ['global' => false, 'home' => false, 'away' => false, 'night' => false],
    'A triggered 24/7 sensor must block every arming mode.'
);

$testValues[6003] = false;
$readiness = $evaluateReadiness->invoke($instance, [readinessSensor(9999, armAway: true)]);
assertReadiness(
    $readiness === ['global' => false, 'home' => true, 'away' => false, 'night' => true],
    'A missing sensor must fail safe only for the modes to which it is assigned.'
);

$readiness = $evaluateReadiness->invoke($instance, [
    readinessSensor(6001, armHome: true, enabled: false),
    readinessSensor(0, armAway: true)
]);
assertReadiness(
    $readiness === ['global' => true, 'home' => true, 'away' => true, 'night' => true],
    'Disabled and incomplete sensor rows must not block readiness.'
);

$updateReadiness = new ReflectionMethod(OpenHomeAlarm::class, 'UpdateReadinessFromSensors');

$testValues[6001] = true;
$testValues[6002] = true;
$testValues[6003] = false;
$updateReadiness->invoke($instance, $sensors);
assertReadiness(
    $instance->TestValue('BlockingHomeSensors') === 'Test 6001',
    'The Home blocker variable must contain the blocking Home sensor name.'
);
assertReadiness(
    $instance->TestValue('BlockingAwaySensors') === 'Test 6002',
    'The Away blocker variable must contain the blocking Away sensor name.'
);
assertReadiness(
    $instance->TestValue('BlockingNightSensors') === 'Test 6002',
    'The Night blocker variable must contain the blocking Night sensor name.'
);

$testValues[6001] = false;
$testValues[6002] = false;
$testValues[6003] = true;
$updateReadiness->invoke($instance, $sensors);
assertReadiness(
    $instance->TestValue('BlockingHomeSensors') === 'Test 6003'
    && $instance->TestValue('BlockingAwaySensors') === 'Test 6003'
    && $instance->TestValue('BlockingNightSensors') === 'Test 6003',
    'A 24/7 blocker must be published for every arming mode.'
);

$testValues[6003] = false;
$unnamedMissingSensor = readinessSensor(9999, armAway: true);
$unnamedMissingSensor['Name'] = '';
$updateReadiness->invoke($instance, [$unnamedMissingSensor]);
assertReadiness(
    $instance->TestValue('BlockingAwaySensors') === 'Variable #9999',
    'Unnamed blockers must fall back to their Symcon variable ID.'
);
assertReadiness(
    $instance->TestValue('BlockingHomeSensors') === ''
    && $instance->TestValue('BlockingNightSensors') === '',
    'Blocker variables for unaffected modes must stay empty.'
);

fwrite(STDOUT, "OpenHomeAlarm mode readiness checks passed.\n");
