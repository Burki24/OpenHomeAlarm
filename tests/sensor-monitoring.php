<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    1001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    1002 => ['VariableType' => 3, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    1003 => ['VariableType' => 1, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    1004 => ['VariableType' => 2, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    1005 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    1001 => false,
    1002 => 'IDLE',
    1003 => 0,
    1004 => 1.5,
    1005 => true
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
    private array $currentValues = [];

    /** @var array<int,list<int>> */
    private array $messages = [];

    /** @var array<string,mixed> */
    private array $writtenValues = [];

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

    /** @return array<int,list<int>> */
    public function TestMessages(): array
    {
        ksort($this->messages);

        return $this->messages;
    }

    /** @return array<string,array{interval:int,script:string}> */
    public function TestTimers(): array
    {
        return $this->timers;
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

    protected function SendDebug(string $message, string $data, int $format): void
    {
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

    protected function ReadPropertyString(string $name): string
    {
        return $this->properties[$name] ?? '';
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
}

function assertSensorMonitoring(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function sensor(
    int $variableID,
    string $triggerValue,
    bool $enabled = true,
    bool $armHome = false,
    bool $armAway = true,
    bool $armNight = false
): array {
    return [
        'Enabled'      => $enabled,
        'Name'         => 'Test ' . $variableID,
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => $triggerValue,
        'ArmHome'      => $armHome,
        'ArmAway'      => $armAway,
        'ArmNight'     => $armNight,
        'EntryDelay'   => false
    ];
}

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestClearWrittenValues();

$sensors = [
    sensor(1001, 'true'),
    sensor(1002, 'ALARM', armHome: true, armAway: false),
    sensor(1003, '1', enabled: false),
    sensor(1004, '2.5', armNight: true),
    sensor(1005, 'true', armAway: false), // enabled but assigned to no arming mode
    sensor(0, 'true')                     // incomplete list row: deliberately ignored
];
$instance->TestSetPropertyString('Sensors', json_encode($sensors, JSON_THROW_ON_ERROR));
$instance->ApplyChanges();

assertSensorMonitoring(
    $instance->TestMessages() === [
        0    => [IPS_KERNELSTARTED],
        1001 => [VM_UPDATE, OM_UNREGISTER],
        1002 => [VM_UPDATE, OM_UNREGISTER],
        1004 => [VM_UPDATE, OM_UNREGISTER]
    ],
    'A3 must subscribe exactly once to enabled, existing sensor variables used by at least one arming mode.'
);
assertSensorMonitoring(
    ($instance->TestWrittenValues()['ReadyToArm'] ?? null) === true,
    'Inactive monitored sensors must leave the system ready to arm.'
);

// Boolean trigger.
global $testValues;
$testValues[1001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(1, 1001, VM_UPDATE, [true, true, false]);
assertSensorMonitoring(
    $instance->TestWrittenValues() === [
        'ReadyToArm'           => false,
        'ReadyHome'            => true,
        'ReadyAway'            => false,
        'ReadyNight'           => true,
        'BlockingHomeSensors'  => '',
        'BlockingAwaySensors'  => 'Test 1001',
        'BlockingNightSensors' => ''
    ],
    'A matching Boolean trigger value must make the system not ready to arm.'
);

$testValues[1001] = false;
$instance->TestClearWrittenValues();
$instance->MessageSink(2, 1001, VM_UPDATE, [false, true, true]);
assertSensorMonitoring(
    $instance->TestWrittenValues() === [
        'ReadyToArm'           => true,
        'ReadyHome'            => true,
        'ReadyAway'            => true,
        'ReadyNight'           => true,
        'BlockingHomeSensors'  => '',
        'BlockingAwaySensors'  => '',
        'BlockingNightSensors' => ''
    ],
    'Clearing a Boolean trigger must restore readiness when all other sensors are inactive.'
);

// String trigger.
$testValues[1002] = 'ALARM';
$instance->TestClearWrittenValues();
$instance->MessageSink(3, 1002, VM_UPDATE, ['ALARM', true, 'IDLE']);
assertSensorMonitoring(
    $instance->TestWrittenValues() === [
        'ReadyToArm'           => false,
        'ReadyHome'            => false,
        'ReadyAway'            => true,
        'ReadyNight'           => true,
        'BlockingHomeSensors'  => 'Test 1002',
        'BlockingAwaySensors'  => '',
        'BlockingNightSensors' => ''
    ],
    'String trigger values must be compared as raw Strings.'
);
$testValues[1002] = 'IDLE';

// Float trigger.
$testValues[1004] = 2.5;
$instance->TestClearWrittenValues();
$instance->MessageSink(4, 1004, VM_UPDATE, [2.5, true, 1.5]);
assertSensorMonitoring(
    $instance->TestWrittenValues() === [
        'ReadyToArm'           => false,
        'ReadyHome'            => true,
        'ReadyAway'            => false,
        'ReadyNight'           => false,
        'BlockingHomeSensors'  => '',
        'BlockingAwaySensors'  => 'Test 1004',
        'BlockingNightSensors' => 'Test 1004'
    ],
    'Float trigger values must be evaluated according to the Symcon variable type.'
);
$testValues[1004] = 1.5;

// A VM_UPDATE from a variable which is not monitored must be ignored.
$instance->TestClearWrittenValues();
$instance->MessageSink(5, 1003, VM_UPDATE, [1, true, 0]);
assertSensorMonitoring(
    $instance->TestWrittenValues() === [],
    'Updates from disabled or otherwise unmonitored sensors must be ignored.'
);

// Reconfiguration must remove stale subscriptions and add new ones.
$reconfiguredSensors = [
    sensor(1002, 'ALARM'),
    sensor(1003, '1')
];
$testValues[1002] = 'IDLE';
$testValues[1003] = 0;
$instance->TestSetPropertyString('Sensors', json_encode($reconfiguredSensors, JSON_THROW_ON_ERROR));
$instance->TestClearWrittenValues();
$instance->ApplyChanges();
assertSensorMonitoring(
    $instance->TestMessages() === [
        0    => [IPS_KERNELSTARTED],
        1002 => [VM_UPDATE, OM_UNREGISTER],
        1003 => [VM_UPDATE, OM_UNREGISTER]
    ],
    'ApplyChanges must unregister removed sensor variables and subscribe newly configured variables.'
);
assertSensorMonitoring(
    ($instance->TestWrittenValues()['ReadyToArm'] ?? null) === true,
    'Reconfigured inactive sensors must be evaluated immediately during ApplyChanges.'
);

// Missing configured variables fail safe, but are not subscribed.
$missingSensor = [sensor(9999, 'true')];
$instance->TestSetPropertyString('Sensors', json_encode($missingSensor, JSON_THROW_ON_ERROR));
$instance->TestClearWrittenValues();
$instance->ApplyChanges();
assertSensorMonitoring(
    $instance->TestMessages() === [0 => [IPS_KERNELSTARTED]],
    'Missing sensor variables must not receive VM_UPDATE subscriptions.'
);
assertSensorMonitoring(
    ($instance->TestWrittenValues()['ReadyToArm'] ?? null) === false
    && ($instance->TestWrittenValues()['ReadyHome'] ?? null) === true
    && ($instance->TestWrittenValues()['ReadyAway'] ?? null) === false
    && ($instance->TestWrittenValues()['ReadyNight'] ?? null) === true
    && ($instance->TestWrittenValues()['BlockingHomeSensors'] ?? null) === ''
    && ($instance->TestWrittenValues()['BlockingAwaySensors'] ?? null) === 'Test 9999'
    && ($instance->TestWrittenValues()['BlockingNightSensors'] ?? null) === '',
    'A missing configured sensor variable must fail safe to not ready.'
);
assertSensorMonitoring(
    ($instance->TestWrittenValues()['SystemFault'] ?? null) === true
    && ($instance->TestWrittenValues()['ActiveFaults'] ?? null) === 'Sensor unavailable: Test 9999',
    'A missing configured sensor variable must also create a visible system fault.'
);

// A3 must not modify mode/state or trigger alarm transitions.
$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
$messageSinkStart = strpos($moduleSource, 'public function MessageSink');
$messageSinkEnd = strpos($moduleSource, 'public function ArmHome');
assertSensorMonitoring($messageSinkStart !== false && $messageSinkEnd !== false, 'MessageSink must exist in A3.');
$messageSinkSource = substr($moduleSource, $messageSinkStart, $messageSinkEnd - $messageSinkStart);
assertSensorMonitoring(
    !str_contains($messageSinkSource, 'SetAlarmMode(') && !str_contains($messageSinkSource, 'SetAlarmState('),
    'A3 sensor monitoring must not perform arming or alarm state transitions.'
);

// Configured sensor availability is checked both immediately and periodically.
$integrity = new OpenHomeAlarm();
$integrity->Create();
$integrity->TestSetPropertyInteger('SensorIntegrityIntervalSeconds', 30);
$integrity->TestSetPropertyString(
    'Sensors',
    json_encode([sensor(1001, 'true')], JSON_THROW_ON_ERROR)
);
$testVariables[1001] = ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''];
$testValues[1001] = false;
$integrity->ApplyChanges();
assertSensorMonitoring(
    ($integrity->TestTimers()['SensorIntegrity']['interval'] ?? null) === 30000
    && ($integrity->TestTimers()['SensorIntegrity']['script'] ?? null)
        === 'OHA_CheckSensorIntegrity($_IPS[\'TARGET\']);',
    'The configurable sensor-integrity timer must run in milliseconds.'
);

unset($testVariables[1001], $testValues[1001]);
$integrity->TestClearWrittenValues();
$integrity->MessageSink(10, 1001, OM_UNREGISTER, []);
$integrityWritten = $integrity->TestWrittenValues();
assertSensorMonitoring(
    ($integrityWritten['SystemFault'] ?? null) === true
    && ($integrityWritten['ActiveFaults'] ?? null) === 'Sensor unavailable: Test 1001'
    && ($integrityWritten['ReadyAway'] ?? null) === false,
    'Removing a monitored sensor variable must immediately publish a system fault and block its arming mode.'
);
assertSensorMonitoring(
    !array_key_exists('State', $integrityWritten),
    'Sensor loss alone must never be treated as a confirmed alarm signal.'
);
$controlState = json_decode($integrity->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertSensorMonitoring(
    ($controlState['Faults']['Items'][0]['Kind'] ?? null) === 'sensor'
    && ($controlState['Faults']['Items'][0]['Reason'] ?? null) === 'unavailable'
    && ($controlState['Faults']['Items'][0]['Name'] ?? null) === 'Test 1001',
    'The public control state must expose unavailable sensors to the Tile.'
);
$historyAfterLoss = json_decode($integrity->GetEventHistory(), true, 512, JSON_THROW_ON_ERROR);
assertSensorMonitoring(
    ($historyAfterLoss[0]['Event'] ?? null) === 'fault_activated'
    && ($historyAfterLoss[0]['Source'] ?? null) === 'Sensor unavailable: Test 1001',
    'A newly unavailable sensor must create one persistent fault event.'
);

$integrity->CheckSensorIntegrity();
$historyAfterRepeatedCheck = json_decode($integrity->GetEventHistory(), true, 512, JSON_THROW_ON_ERROR);
assertSensorMonitoring(
    count($historyAfterRepeatedCheck) === count($historyAfterLoss),
    'Repeated integrity checks must not duplicate an already active sensor-loss event.'
);

$testVariables[1001] = ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''];
$testValues[1001] = false;
$integrity->TestClearWrittenValues();
$integrity->CheckSensorIntegrity();
$integrityWritten = $integrity->TestWrittenValues();
assertSensorMonitoring(
    ($integrityWritten['SystemFault'] ?? null) === false
    && ($integrityWritten['ActiveFaults'] ?? null) === ''
    && ($integrityWritten['ReadyAway'] ?? null) === true,
    'The periodic integrity check must clear the system fault when the sensor becomes readable again.'
);
$historyAfterRecovery = json_decode($integrity->GetEventHistory(), true, 512, JSON_THROW_ON_ERROR);
assertSensorMonitoring(
    ($historyAfterRecovery[0]['Event'] ?? null) === 'fault_cleared'
    && ($historyAfterRecovery[0]['Source'] ?? null) === 'Sensor unavailable: Test 1001',
    'Restoring sensor availability must create one fault-cleared event.'
);
assertSensorMonitoring(
    ($integrity->TestMessages()[1001] ?? null) === [VM_UPDATE, OM_UNREGISTER],
    'The integrity check must restore both subscriptions when a configured variable becomes readable again.'
);

$form = json_decode($integrity->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
assertSensorMonitoring(
    str_contains(
        json_encode($form, JSON_THROW_ON_ERROR),
        '"name":"SensorIntegrityIntervalSeconds"'
    ),
    'The configuration form must expose the sensor-integrity interval.'
);

fwrite(STDOUT, "OpenHomeAlarm sensor monitoring checks passed.\n");
