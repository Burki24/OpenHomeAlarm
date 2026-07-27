<?php

declare(strict_types=1);

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';

class IPSModuleStrict
{
    /** @var array<string,string> */
    private array $properties = [];

    /** @var array<string,array<string,mixed>> */
    private array $registeredVariables = [];

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

    public function TestReadPropertyString(string $name): string
    {
        return $this->properties[$name] ?? '';
    }

    /** @return array<string,array<string,mixed>> */
    public function TestRegisteredVariables(): array
    {
        return $this->registeredVariables;
    }

    /** @return array<string,mixed> */
    public function TestWrittenValues(): array
    {
        return $this->writtenValues;
    }

    protected function RegisterPropertyString(string $name, string $default): void
    {
        if (!array_key_exists($name, $this->properties)) {
            $this->properties[$name] = $default;
        }
    }

    protected function ReadPropertyString(string $name): string
    {
        return $this->properties[$name] ?? '';
    }

    protected function RegisterVariableInteger(string $ident, string $name, array $presentation, int $position): bool
    {
        $this->registeredVariables[$ident] = [
            'type'         => 'integer',
            'name'         => $name,
            'presentation' => $presentation,
            'position'     => $position
        ];

        return true;
    }

    protected function RegisterVariableBoolean(string $ident, string $name, array $presentation, int $position): bool
    {
        $this->registeredVariables[$ident] = [
            'type'         => 'boolean',
            'name'         => $name,
            'presentation' => $presentation,
            'position'     => $position
        ];

        return true;
    }

    protected function SetValue(string $ident, mixed $value): void
    {
        $this->writtenValues[$ident] = $value;
    }

    protected function Translate(string $text): string
    {
        return $text;
    }
}

function assertSensorModel(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

$instance = new OpenHomeAlarm();
$instance->Create();

assertSensorModel(
    $instance->TestReadPropertyString('Sensors') === '[]',
    'Sensors property must default to an empty JSON list.'
);
assertSensorModel(
    array_keys($instance->TestRegisteredVariables()) === ['Mode', 'State', 'ReadyToArm'],
    'A2 must not add operational status variables.'
);

$configuredSensors = [
    [
        'Enabled'      => true,
        'Name'         => 'Haustür',
        'VariableID'   => 12345,
        'SensorType'   => 0,
        'TriggerValue' => '1',
        'ArmHome'      => true,
        'ArmAway'      => true,
        'ArmNight'     => true,
        'EntryDelay'   => true
    ],
    [
        'Enabled'      => true,
        'Name'         => 'Flur Bewegung',
        'VariableID'   => 23456,
        'SensorType'   => 1,
        'TriggerValue' => 'true',
        'ArmHome'      => false,
        'ArmAway'      => true,
        'ArmNight'     => false,
        'EntryDelay'   => false
    ]
];
$instance->TestSetPropertyString(
    'Sensors',
    json_encode($configuredSensors, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
);

$readConfiguredSensors = new ReflectionMethod(OpenHomeAlarm::class, 'ReadConfiguredSensors');
$normalizedSensors = $readConfiguredSensors->invoke($instance);
assertSensorModel($normalizedSensors === $configuredSensors, 'Valid sensor configuration must round-trip unchanged.');

$minimalInstance = new OpenHomeAlarm();
$minimalInstance->Create();
$minimalInstance->TestSetPropertyString(
    'Sensors',
    json_encode([
        [
            'VariableID' => 34567
        ]
    ], JSON_THROW_ON_ERROR)
);
$minimalSensors = $readConfiguredSensors->invoke($minimalInstance);
assertSensorModel(
    $minimalSensors === [[
        'Enabled'      => true,
        'Name'         => '',
        'VariableID'   => 34567,
        'SensorType'   => 0,
        'TriggerValue' => '1',
        'ArmHome'      => false,
        'ArmAway'      => true,
        'ArmNight'     => false,
        'EntryDelay'   => false
    ]],
    'Missing optional sensor fields must receive stable defaults.'
);

$invalidTypeInstance = new OpenHomeAlarm();
$invalidTypeInstance->Create();
$invalidTypeInstance->TestSetPropertyString(
    'Sensors',
    json_encode([
        [
            'VariableID' => 45678,
            'SensorType' => 99
        ]
    ], JSON_THROW_ON_ERROR)
);
try {
    $readConfiguredSensors->invoke($invalidTypeInstance);
    throw new RuntimeException('Unsupported sensor types must be rejected.');
} catch (UnexpectedValueException) {
}

$invalidJsonInstance = new OpenHomeAlarm();
$invalidJsonInstance->Create();
$invalidJsonInstance->TestSetPropertyString('Sensors', '{invalid');
try {
    $readConfiguredSensors->invoke($invalidJsonInstance);
    throw new RuntimeException('Invalid sensor JSON must be rejected.');
} catch (UnexpectedValueException) {
}

$form = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$list = null;
foreach ($form['elements'] ?? [] as $element) {
    if (($element['type'] ?? null) === 'List' && ($element['name'] ?? null) === 'Sensors') {
        $list = $element;
        break;
    }
}
assertSensorModel(is_array($list), 'Configuration form must contain the Sensors list.');
assertSensorModel(($list['add'] ?? false) === true, 'Sensors list must allow adding entries.');
assertSensorModel(($list['delete'] ?? false) === true, 'Sensors list must allow deleting entries.');

$columns = [];
foreach ($list['columns'] ?? [] as $column) {
    if (isset($column['name'])) {
        $columns[$column['name']] = $column;
    }
}
foreach ([
    'Enabled',
    'Name',
    'VariableID',
    'SensorType',
    'TriggerValue',
    'ArmHome',
    'ArmAway',
    'ArmNight',
    'EntryDelay'
] as $columnName) {
    assertSensorModel(isset($columns[$columnName]), 'Missing Sensors column ' . $columnName . '.');
}
assertSensorModel(
    ($columns['VariableID']['edit']['type'] ?? null) === 'SelectVariable',
    'VariableID must use SelectVariable.'
);
assertSensorModel(
    ($columns['SensorType']['edit']['type'] ?? null) === 'Select',
    'SensorType must use Select.'
);
assertSensorModel(
    array_column($columns['SensorType']['edit']['options'] ?? [], 'value') === [0, 1, 2, 3, 4, 5, 6],
    'Sensor type values must remain stable.'
);
assertSensorModel(($columns['Enabled']['add'] ?? null) === true, 'New sensors must be enabled by default.');
assertSensorModel(($columns['TriggerValue']['add'] ?? null) === '1', 'New sensors must use trigger value 1 by default.');
assertSensorModel(($columns['ArmHome']['add'] ?? null) === false, 'New sensors must not be active in Home by default.');
assertSensorModel(($columns['ArmAway']['add'] ?? null) === true, 'New sensors must be active in Away by default.');
assertSensorModel(($columns['ArmNight']['add'] ?? null) === false, 'New sensors must not be active in Night by default.');
assertSensorModel(($columns['EntryDelay']['add'] ?? null) === false, 'New sensors must not use entry delay by default.');

$locale = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$translations = $locale['translations']['de'] ?? [];
foreach ([
    'Sensors and triggers',
    'Enabled',
    'Variable',
    'Sensor type',
    'Trigger value',
    'Opening contact',
    'Motion detector',
    'Glass break detector',
    'Smoke detector',
    'Water detector',
    'Panic trigger',
    'Other trigger',
    'The trigger value is stored as text. Type-aware evaluation is added with the sensor evaluation logic.'
] as $translationKey) {
    assertSensorModel(isset($translations[$translationKey]), 'Missing German translation for ' . $translationKey . '.');
}

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertSensorModel(
    !str_contains($moduleSource, 'RegisterMessage('),
    'A2 must not subscribe to sensor changes yet.'
);
assertSensorModel(
    !str_contains($moduleSource, 'MessageSink('),
    'A2 must not evaluate sensor messages yet.'
);

fwrite(STDOUT, "OpenHomeAlarm sensor model checks passed.\n");
