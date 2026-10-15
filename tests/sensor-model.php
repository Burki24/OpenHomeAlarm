<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';

class IPSModuleStrict
{
    /** @var array<string,mixed> */
    private array $properties = [];

    /** @var array<string,int|string> */
    private array $attributes = [];

    /** @var array<string,array<string,mixed>> */
    private array $registeredVariables = [];

    /** @var array<string,mixed> */
    private array $writtenValues = [];

    /** @var list<array{field:string,parameter:string,value:mixed}> */
    private array $formUpdates = [];

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

    /** @return list<array{field:string,parameter:string,value:mixed}> */
    public function TestFormUpdates(): array
    {
        return $this->formUpdates;
    }

    public function TestClearFormUpdates(): void
    {
        $this->formUpdates = [];
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

    protected function ReadPropertyString(string $name): string
    {
        return $this->properties[$name] ?? '';
    }

    protected function ReadPropertyInteger(string $name): int
    {
        $value = $this->properties[$name] ?? 0;

        return is_int($value) ? $value : 0;
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

    protected function RegisterVariableString(string $ident, string $name, array $presentation, int $position): bool
    {
        $this->registeredVariables[$ident] = [
            'type'         => 'string',
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

    protected function UpdateFormField(string $field, string $parameter, mixed $value): bool
    {
        $this->formUpdates[] = [
            'field'     => $field,
            'parameter' => $parameter,
            'value'     => $value
        ];

        return true;
    }
}

/** @var array<int,array<string,mixed>> */
$testVariables = [
    12345 => [
        'VariableType'          => 0,
        'VariableCustomProfile' => '',
        'VariableProfile'       => ''
    ],
    23456 => [
        'VariableType'          => 3,
        'VariableCustomProfile' => '',
        'VariableProfile'       => ''
    ],
    34567 => [
        'VariableType'          => 1,
        'VariableCustomProfile' => '',
        'VariableProfile'       => ''
    ],
    45678 => [
        'VariableType'          => 3,
        'VariableCustomProfile' => '',
        'VariableProfile'       => ''
    ],
    56789 => [
        'VariableType'          => 1,
        'VariableCustomProfile' => '',
        'VariableProfile'       => 'Test.Legacy'
    ],
    67890 => [
        'VariableType'          => 0,
        'VariableCustomProfile' => '',
        'VariableProfile'       => ''
    ]
];

/** @var array<int,array<string,mixed>> */
$testObjects = [
    0     => ['ObjectType' => 0, 'ObjectName' => 'Symcon', 'ObjectIsHidden' => false, 'ParentID' => 0],
    8000  => ['ObjectType' => 0, 'ObjectName' => 'Security', 'ObjectIsHidden' => false, 'ParentID' => 0],
    12345 => ['ObjectType' => 2, 'ObjectName' => 'Door contact', 'ObjectIsHidden' => false, 'ParentID' => 8000],
    34567 => ['ObjectType' => 2, 'ObjectName' => 'Door state', 'ObjectIsHidden' => false, 'ParentID' => 8000],
    67890 => ['ObjectType' => 2, 'ObjectName' => 'Door garage', 'ObjectIsHidden' => false, 'ParentID' => 8000]
];

/** @var array<int,list<int>> */
$testObjectChildren = [
    0    => [8000],
    8000 => [12345, 34567, 67890]
];

/** @var array<int,array<string,mixed>> */
$testVariablePresentations = [
    12345 => [
        'OPTIONS' => json_encode([
            ['Value' => false, 'Caption' => 'Aus'],
            ['Value' => true, 'Caption' => 'An']
        ], JSON_THROW_ON_ERROR)
    ],
    23456 => [
        'OPTIONS' => json_encode([
            ['Value' => 'ALARM', 'Caption' => 'Alarm'],
            ['Value' => 'IDLE', 'Caption' => 'Standby']
        ], JSON_THROW_ON_ERROR)
    ],
    34567 => [
        'INTERVALS' => json_encode([
            [
                'IntervalMinValue' => 0,
                'IntervalMaxValue' => 0,
                'ConstantActive'   => true,
                'ConstantValue'    => 'Geschlossen'
            ],
            [
                'IntervalMinValue' => 1,
                'IntervalMaxValue' => 1,
                'ConstantActive'   => true,
                'ConstantValue'    => 'Offen'
            ]
        ], JSON_THROW_ON_ERROR)
    ],
    45678 => [],
    56789 => []
];

/** @var array<string,array<string,mixed>> */
$testVariableProfiles = [
    'Test.Legacy' => [
        'Associations' => [
            ['Value' => 0.0, 'Name' => 'Inaktiv'],
            ['Value' => 1.0, 'Name' => 'Aktiv']
        ]
    ]
];

final class IPSList implements ArrayAccess, JsonSerializable
{
    /**
     * @param array<string,mixed> $row
     */
    public function __construct(private array $row)
    {
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && array_key_exists($offset, $this->row);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return is_string($offset) ? ($this->row[$offset] ?? null) : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('Test IPSList is read-only.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Test IPSList is read-only.');
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return $this->row;
    }
}

function IPS_VariableExists(int $variableID): bool
{
    global $testVariables;

    return array_key_exists($variableID, $testVariables);
}

function IPS_ObjectExists(int $objectID): bool
{
    global $testObjects;

    return array_key_exists($objectID, $testObjects);
}

/** @return array<string,mixed> */
function IPS_GetObject(int $objectID): array
{
    global $testObjects;

    return $testObjects[$objectID] ?? [];
}

/** @return list<int> */
function IPS_GetChildrenIDs(int $objectID): array
{
    global $testObjectChildren;

    return $testObjectChildren[$objectID] ?? [];
}

/** @return array<string,mixed> */
function IPS_GetVariablePresentation(int $variableID): array
{
    global $testVariablePresentations;

    return $testVariablePresentations[$variableID] ?? [];
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
function IPS_GetVariableProfile(string $profileName): array
{
    global $testVariableProfiles;

    if (!array_key_exists($profileName, $testVariableProfiles)) {
        throw new RuntimeException('Unknown test profile.');
    }

    return $testVariableProfiles[$profileName];
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
    $instance->TestReadPropertyString('FaultInputs') === '[]',
    'FaultInputs property must default to an empty JSON list.'
);
assertSensorModel(
    array_keys($instance->TestRegisteredVariables()) === [
        'Mode',
        'State',
        'DelayRemaining',
        'DelaySource',
        'AlarmOutputActive',
        'SignalGeneratorActive',
        'ReadyToArm',
        'ReadyHome',
        'ReadyAway',
        'ReadyNight',
        'BlockingHomeSensors',
        'BlockingAwaySensors',
        'BlockingNightSensors',
        'BypassedSensors',
        'AlarmMemory',
        'LastAlarmSource',
        'LastAlarmTime',
        'SystemFault',
        'ActiveFaults',
        'BlockingFaults',
        'LastFaultSource',
        'LastFaultTime'
    ],
    'The sensor model must keep the expected module status variables.'
);

$configuredSensors = [
    [
        'Enabled'              => true,
        'PartitionID'          => 'main',
        'Name'                 => 'Haustür',
        'VariableID'           => 12345,
        'SensorType'           => 0,
        'TriggerCondition'     => 'equals',
        'TriggerValue'         => '1',
        'ArmHome'              => true,
        'ArmAway'              => true,
        'ArmNight'             => true,
        'AlwaysActive'         => false,
        'AllowAutomaticBypass' => false,
        'AllowPassage'         => false,
        'ExitDelay'            => true,
        'EntryDelay'           => true,
        'RetriggerAlarm'       => false
    ],
    [
        'Enabled'              => true,
        'PartitionID'          => 'main',
        'Name'                 => 'Flur Bewegung',
        'VariableID'           => 23456,
        'SensorType'           => 1,
        'TriggerCondition'     => 'equals',
        'TriggerValue'         => 'true',
        'ArmHome'              => false,
        'ArmAway'              => true,
        'ArmNight'             => false,
        'AlwaysActive'         => false,
        'AllowAutomaticBypass' => false,
        'AllowPassage'         => false,
        'ExitDelay'            => false,
        'EntryDelay'           => false,
        'RetriggerAlarm'       => false
    ]
];
$instance->TestSetPropertyString(
    'Sensors',
    json_encode($configuredSensors, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
);

$readConfiguredSensors = new ReflectionMethod(OpenHomeAlarm::class, 'ReadConfiguredSensors');
$normalizedSensors = $readConfiguredSensors->invoke($instance);
assertSensorModel($normalizedSensors === $configuredSensors, 'Valid sensor configuration must round-trip unchanged.');
$alwaysActiveConfiguration = new OpenHomeAlarm();
$alwaysActiveConfiguration->Create();
$alwaysActiveConfiguration->TestSetPropertyString('Sensors', json_encode([array_merge($configuredSensors[0], [
    'AlwaysActive'         => true,
    'AllowAutomaticBypass' => true,
    'AllowPassage'         => true
])], JSON_THROW_ON_ERROR));
$normalizedAlwaysActive = $readConfiguredSensors->invoke($alwaysActiveConfiguration)[0];
assertSensorModel(
    $normalizedAlwaysActive['AlwaysActive'] === true
    && $normalizedAlwaysActive['ArmHome'] === false
    && $normalizedAlwaysActive['ArmAway'] === false
    && $normalizedAlwaysActive['ArmNight'] === false
    && $normalizedAlwaysActive['AllowAutomaticBypass'] === false
    && $normalizedAlwaysActive['AllowPassage'] === false
    && $normalizedAlwaysActive['ExitDelay'] === false
    && $normalizedAlwaysActive['EntryDelay'] === false,
    'Legacy 24/7 sensors must ignore saved arming-mode and delay values.'
);

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
        'Enabled'              => true,
        'PartitionID'          => 'main',
        'Name'                 => '',
        'VariableID'           => 34567,
        'SensorType'           => 0,
        'TriggerCondition'     => 'equals',
        'TriggerValue'         => '1',
        'ArmHome'              => false,
        'ArmAway'              => true,
        'ArmNight'             => false,
        'AlwaysActive'         => false,
        'AllowAutomaticBypass' => false,
        'AllowPassage'         => false,
        'ExitDelay'            => false,
        'EntryDelay'           => false,
        'RetriggerAlarm'       => false
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
$sensorImport = null;
foreach ($form['elements'] ?? [] as $element) {
    if (($element['type'] ?? null) === 'PopupButton' && ($element['name'] ?? null) === 'SensorImport') {
        $sensorImport = $element;
    }
    if (($element['type'] ?? null) === 'List' && ($element['name'] ?? null) === 'Sensors') {
        $list = $element;
        break;
    }
}
assertSensorModel(is_array($sensorImport), 'Configuration form must offer the sensor search importer.');
assertSensorModel(
    str_contains((string) ($sensorImport['onClick'] ?? ''), 'OHA_PrepareSensorImport')
        && str_contains(
            (string) ($sensorImport['popup']['buttons'][0]['onClick'] ?? ''),
            'OHA_AddSensorCandidates'
        ),
    'Sensor importer actions must prepare current partitions and add selected candidates.'
);
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
    'PartitionNames',
    'VariableID',
    'SensorType',
    'TriggerCondition',
    'TriggerValue',
    'ArmHome',
    'ArmAway',
    'ArmNight',
    'AlwaysActive',
    'AllowAutomaticBypass',
    'AllowPassage',
    'ExitDelay',
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
assertSensorModel(
    ($columns['PartitionNames']['save'] ?? true) === false,
    'The readable alarm-area summary must not be persisted.'
);
assertSensorModel(($columns['TriggerValue']['add'] ?? null) === '1', 'New sensors must use trigger value 1 by default.');
assertSensorModel(
    ($columns['TriggerCondition']['add'] ?? null) === 'equals',
    'New sensors must use exact trigger-value evaluation by default.'
);
assertSensorModel(
    ($columns['TriggerCondition']['edit']['type'] ?? null) === 'Select'
        && ($columns['TriggerCondition']['edit']['options'] ?? null) === [
            ['caption' => 'Trigger at selected value', 'value' => 'equals'],
            ['caption' => 'Trigger when different from normal value', 'value' => 'not_equals']
        ],
    'Sensor evaluation must offer exact alarm-value and normal-value rules.'
);
assertSensorModel(($columns['ArmHome']['add'] ?? null) === false, 'New sensors must not be active in Home by default.');
assertSensorModel(($columns['ArmAway']['add'] ?? null) === true, 'New sensors must be active in Away by default.');
assertSensorModel(($columns['ArmNight']['add'] ?? null) === false, 'New sensors must not be active in Night by default.');
assertSensorModel(($columns['AlwaysActive']['add'] ?? null) === false, 'New sensors must not be 24/7 active by default.');
assertSensorModel(($columns['AllowAutomaticBypass']['add'] ?? null) === false, 'New sensors must not permit automatic bypasses by default.');
assertSensorModel(($columns['AllowPassage']['add'] ?? null) === false, 'New sensors must not permit temporary passage by default.');
assertSensorModel(($columns['ExitDelay']['add'] ?? null) === false, 'New sensors must not use the exit route by default.');
assertSensorModel(($columns['EntryDelay']['add'] ?? null) === false, 'New sensors must not use entry delay by default.');
assertSensorModel(
    ($columns['VariableID']['width'] ?? null) === 'auto',
    'Variable column must use the remaining list width so long Symcon paths stay readable.'
);
assertSensorModel(
    ($columns['Enabled']['width'] ?? null) === '55px'
        && ($columns['Name']['width'] ?? null) === '150px'
        && ($columns['PartitionNames']['width'] ?? null) === '110px'
        && ($columns['SensorType']['width'] ?? null) === '120px'
        && ($columns['TriggerCondition']['width'] ?? null) === '135px'
        && ($columns['TriggerValue']['width'] ?? null) === '70px',
    'Main sensor columns must use compact fixed widths.'
);
assertSensorModel(
    ($columns['ArmHome']['width'] ?? null) === '60px'
        && ($columns['ArmAway']['width'] ?? null) === '65px'
        && ($columns['ArmNight']['width'] ?? null) === '60px'
        && ($columns['AlwaysActive']['width'] ?? null) === '55px'
        && ($columns['AllowAutomaticBypass']['width'] ?? null) === '85px'
        && ($columns['AllowPassage']['width'] ?? null) === '80px'
        && ($columns['ExitDelay']['width'] ?? null) === '70px'
        && ($columns['EntryDelay']['width'] ?? null) === '70px',
    'Mode and special-behavior columns must remain compact.'
);
assertSensorModel(
    ($columns['Enabled']['caption'] ?? null) === 'Active'
        && ($columns['PartitionNames']['caption'] ?? null) === 'Areas'
        && ($columns['SensorType']['caption'] ?? null) === 'Type'
        && ($columns['TriggerCondition']['caption'] ?? null) === 'Rule'
        && ($columns['TriggerValue']['caption'] ?? null) === 'Value'
        && ($columns['AllowAutomaticBypass']['caption'] ?? null) === 'Auto bypass'
        && ($columns['AllowPassage']['caption'] ?? null) === 'Passage'
        && ($columns['ExitDelay']['caption'] ?? null) === 'Exit'
        && ($columns['EntryDelay']['caption'] ?? null) === 'Entry',
    'Long sensor-table headers must use compact captions instead of forcing horizontal scrolling.'
);

assertSensorModel(
    ($list['form'][0] ?? null) === 'return OHA_GetSensorEditForm($id, $Sensors);',
    'Sensors list must use the dynamic individual edit form.'
);

assertSensorModel(
    isset($columns['TriggerValueSelection']) && ($columns['TriggerValueSelection']['save'] ?? true) === false,
    'TriggerValueSelection must be a non-persistent helper column.'
);
assertSensorModel(
    isset($columns['TriggerValueManual']) && ($columns['TriggerValueManual']['save'] ?? true) === false,
    'TriggerValueManual must be a non-persistent helper column.'
);

$formValueInstance = new OpenHomeAlarm();
$formValueInstance->Create();
$formValueInstance->TestSetPropertyString(
    'Sensors',
    json_encode([
        [
            'VariableID'   => 23456,
            'TriggerValue' => 'ALARM'
        ],
        [
            'VariableID'   => 12345,
            'TriggerValue' => 'true'
        ],
        [
            'VariableID'   => 23456,
            'TriggerValue' => '"IDLE"'
        ]
    ], JSON_THROW_ON_ERROR)
);
$generatedForm = json_decode($formValueInstance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
$generatedSensorList = null;
foreach ($generatedForm['elements'] ?? [] as $element) {
    if (($element['type'] ?? null) === 'List' && ($element['name'] ?? null) === 'Sensors') {
        $generatedSensorList = $element;
        break;
    }
}
assertSensorModel(is_array($generatedSensorList), 'Generated configuration form must contain the Sensors list.');
assertSensorModel(
    ($generatedSensorList['values'] ?? null) === [
        [
            'TriggerValueSelection' => 'ALARM',
            'TriggerValueManual'    => 'ALARM',
            'TriggerCondition'      => 'equals',
            'PartitionNames'        => 'Main area'
        ],
        [
            'TriggerValueSelection' => 'true',
            'TriggerValueManual'    => 'true',
            'TriggerCondition'      => 'equals',
            'PartitionNames'        => 'Main area'
        ],
        [
            'TriggerValueSelection' => 'IDLE',
            'TriggerValueManual'    => '"IDLE"',
            'TriggerCondition'      => 'equals',
            'PartitionNames'        => 'Main area'
        ]
    ],
    'Generated List values must restore the persisted trigger value into non-persistent edit helper fields.'
);

$importInstance = new OpenHomeAlarm();
$importInstance->Create();
$reflection = new ReflectionClass(OpenHomeAlarm::class);
assertSensorModel(
    $reflection->getReflectionConstant('SENSOR_SEARCH_MAX_DEPTH')->getValue() === 100
        && $reflection->getReflectionConstant('SENSOR_SEARCH_MAX_OBJECTS')->getValue() === 100000
        && $reflection->getReflectionConstant('SENSOR_SEARCH_MAX_RESULTS')->getValue() === 10000,
    'Variable search must use the expanded helper limits for large Symcon object trees.'
);
$partitionDraft = json_encode([
    ['Enabled' => true, 'ID' => 'main', 'Name' => 'Main area', 'SilentByDefault' => false],
    ['Enabled' => true, 'ID' => 'garage', 'Name' => 'Garage', 'SilentByDefault' => false]
], JSON_THROW_ON_ERROR);
$importInstance->PrepareSensorImport($partitionDraft);
$partitionUpdate = $importInstance->TestFormUpdates()[0] ?? [];
$partitionRows = json_decode((string) ($partitionUpdate['value'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
assertSensorModel(
    ($partitionUpdate['field'] ?? null) === 'ImportPartitions'
        && ($partitionRows[0] ?? null) === ['Selected' => true, 'Name' => 'Main area', 'ID' => 'main']
        && ($partitionRows[1] ?? null) === ['Selected' => false, 'Name' => 'Garage', 'ID' => 'garage'],
    'Opening the sensor importer must use the current draft partitions and select main by default.'
);

$draftSensors = [[
    'Enabled'    => true,
    'Name'       => 'Existing door',
    'VariableID' => 12345
]];
$importInstance->TestClearFormUpdates();
$importInstance->SearchSensorCandidates(
    'door',
    'bool',
    8000,
    false,
    json_encode($draftSensors, JSON_THROW_ON_ERROR)
);
$candidateUpdates = $importInstance->TestFormUpdates();
$candidateRows = json_decode((string) ($candidateUpdates[0]['value'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
assertSensorModel(
    ($candidateUpdates[0]['field'] ?? null) === 'ImportCandidates'
        && count($candidateRows) === 1
        && ($candidateRows[0]['VariableID'] ?? null) === 67890
        && ($candidateRows[0]['Selected'] ?? null) === true
        && str_contains((string) ($candidateUpdates[1]['value'] ?? ''), '1 already'),
    'Sensor search must filter by name and type, exclude configured IDs and select fresh matches.'
);

$settings = [
    'Enabled'              => true,
    'SensorType'           => 0,
    'TriggerCondition'     => 'equals',
    'TriggerValue'         => '1',
    'ArmHome'              => true,
    'ArmAway'              => true,
    'ArmNight'             => true,
    'AlwaysActive'         => true,
    'AllowAutomaticBypass' => true,
    'AllowPassage'         => true,
    'ExitDelay'            => true,
    'EntryDelay'           => true,
    'RetriggerAlarm'       => false
];
$partitionRows[1]['Selected'] = true;
$importInstance->TestClearFormUpdates();
$addResult = $importInstance->AddSensorCandidates(
    'door',
    'bool',
    8000,
    false,
    json_encode($settings, JSON_THROW_ON_ERROR),
    json_encode($partitionRows, JSON_THROW_ON_ERROR),
    $partitionDraft,
    json_encode($draftSensors, JSON_THROW_ON_ERROR),
    json_encode($candidateRows, JSON_THROW_ON_ERROR)
);
$addedRows = json_decode(
    (string) ($importInstance->TestFormUpdates()[0]['value'] ?? ''),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$added = $addedRows[1] ?? [];
assertSensorModel(
    $addResult === ''
        && ($added['VariableID'] ?? null) === 67890
        && ($added['Name'] ?? null) === 'Door garage (Security)'
        && ($added['Partition_main'] ?? null) === true
        && ($added['Partition_garage'] ?? null) === true
        && ($added['AlwaysActive'] ?? null) === true
        && ($added['ArmHome'] ?? null) === false
        && ($added['ArmAway'] ?? null) === false
        && ($added['AllowAutomaticBypass'] ?? null) === false
        && ($added['AllowPassage'] ?? null) === false
        && ($added['ExitDelay'] ?? null) === false
        && ($added['EntryDelay'] ?? null) === false
        && ($added['TriggerValue'] ?? null) === 'true'
        && ($added['TriggerValueSelection'] ?? null) === 'true'
        && ($added['TriggerValueManual'] ?? null) === 'true',
    'Bulk-added sensors must close the popup, remain immediately editable, keep their ID, include the parent in their name, use every selected partition and normalize 24/7 settings.'
);

$forgedRows = $candidateRows;
$forgedRows[0]['VariableID'] = 34567;
$forgedResult = $importInstance->AddSensorCandidates(
    'door',
    'bool',
    8000,
    false,
    json_encode($settings, JSON_THROW_ON_ERROR),
    json_encode($partitionRows, JSON_THROW_ON_ERROR),
    $partitionDraft,
    json_encode($draftSensors, JSON_THROW_ON_ERROR),
    json_encode($forgedRows, JSON_THROW_ON_ERROR)
);
assertSensorModel(
    !str_starts_with($forgedResult, 'MESSAGE:'),
    'Bulk sensor import must reject stale or forged variable IDs after a fresh search.'
);

$importInstance->TestClearFormUpdates();
$importInstance->PrepareFaultInputImport($partitionDraft);
$faultPartitionUpdate = $importInstance->TestFormUpdates()[0] ?? [];
$faultPartitionOptions = json_decode(
    (string) ($faultPartitionUpdate['value'] ?? ''),
    true,
    512,
    JSON_THROW_ON_ERROR
);
assertSensorModel(
    ($faultPartitionUpdate['field'] ?? null) === 'FaultImportPartitionID'
        && ($faultPartitionUpdate['parameter'] ?? null) === 'options'
        && $faultPartitionOptions === [
            ['caption' => 'Main area', 'value' => 'main'],
            ['caption' => 'Garage', 'value' => 'garage']
        ],
    'Opening the fault-input importer must offer the current draft partitions.'
);

$draftFaultInputs = [[
    'Enabled'      => true,
    'Name'         => 'Existing door fault',
    'VariableID'   => 12345,
    'FaultType'    => 0,
    'TriggerValue' => 'true',
    'BlockArming'  => false,
    'TriggerAlarm' => false
]];
$importInstance->TestClearFormUpdates();
$importInstance->SearchFaultInputCandidates(
    'door',
    'bool',
    8000,
    false,
    json_encode($draftFaultInputs, JSON_THROW_ON_ERROR)
);
$faultCandidateUpdates = $importInstance->TestFormUpdates();
$faultCandidateRows = json_decode(
    (string) ($faultCandidateUpdates[0]['value'] ?? ''),
    true,
    512,
    JSON_THROW_ON_ERROR
);
assertSensorModel(
    ($faultCandidateUpdates[0]['field'] ?? null) === 'FaultImportCandidates'
        && count($faultCandidateRows) === 1
        && ($faultCandidateRows[0]['VariableID'] ?? null) === 67890
        && str_contains((string) ($faultCandidateUpdates[1]['value'] ?? ''), '1 already'),
    'Fault-input search must exclude configured IDs and return fresh matching variables.'
);

$faultSettings = [
    'Enabled'      => true,
    'PartitionID'  => 'garage',
    'FaultType'    => 2,
    'TriggerValue' => 'true',
    'BlockArming'  => true,
    'TriggerAlarm' => false
];
$importInstance->TestClearFormUpdates();
$faultAddResult = $importInstance->AddFaultInputCandidates(
    'door',
    'bool',
    8000,
    false,
    json_encode($faultSettings, JSON_THROW_ON_ERROR),
    $partitionDraft,
    json_encode($draftFaultInputs, JSON_THROW_ON_ERROR),
    json_encode($faultCandidateRows, JSON_THROW_ON_ERROR)
);
$addedFaultRows = json_decode(
    (string) ($importInstance->TestFormUpdates()[0]['value'] ?? ''),
    true,
    512,
    JSON_THROW_ON_ERROR
);
assertSensorModel(
    str_starts_with($faultAddResult, 'MESSAGE:')
        && ($addedFaultRows[1]['VariableID'] ?? null) === 67890
        && ($addedFaultRows[1]['Name'] ?? null) === 'Door garage'
        && ($addedFaultRows[1]['PartitionID'] ?? null) === 'garage'
        && ($addedFaultRows[1]['FaultType'] ?? null) === 2
        && ($addedFaultRows[1]['BlockArming'] ?? null) === true
        && ($addedFaultRows[1]['TriggerAlarm'] ?? null) === false
        && ($addedFaultRows[1]['TriggerValue'] ?? null) === 'true'
        && ($addedFaultRows[1]['TriggerValueSelection'] ?? null) === 'true'
        && ($addedFaultRows[1]['TriggerValueManual'] ?? null) === 'true',
    'Bulk-added fault inputs must remain immediately editable, keep their ID and name and apply the shared monitoring settings.'
);

$forgedFaultRows = $faultCandidateRows;
$forgedFaultRows[0]['VariableID'] = 34567;
$forgedFaultResult = $importInstance->AddFaultInputCandidates(
    'door',
    'bool',
    8000,
    false,
    json_encode($faultSettings, JSON_THROW_ON_ERROR),
    $partitionDraft,
    json_encode($draftFaultInputs, JSON_THROW_ON_ERROR),
    json_encode($forgedFaultRows, JSON_THROW_ON_ERROR)
);
assertSensorModel(
    !str_starts_with($forgedFaultResult, 'MESSAGE:'),
    'Bulk fault-input import must reject stale or forged variable IDs after a fresh search.'
);

$editForm = $instance->GetSensorEditForm(new IPSList([
    'VariableID'   => 12345,
    'TriggerValue' => 'true'
]));
$editFields = [];
foreach ($editForm as $field) {
    if (isset($field['name'])) {
        $editFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($editFields['VariableID']['type'] ?? null) === 'SelectVariable',
    'Sensor editor must use SelectVariable for VariableID.'
);
assertSensorModel(
    ($editFields['TriggerCondition']['type'] ?? null) === 'Select'
        && ($editFields['TriggerCondition']['value'] ?? null) === 'equals'
        && ($editFields['TriggerCondition']['onChange'] ?? null)
            === 'OHA_UpdateSensorTriggerConditionForm($id, $TriggerCondition);',
    'Legacy sensor rows must open with exact trigger-value evaluation.'
);
assertSensorModel(
    ($editFields['PartitionID']['type'] ?? null) === 'ValidationTextBox'
    && ($editFields['PartitionID']['value'] ?? null) === 'main'
    && ($editFields['PartitionID']['visible'] ?? true) === false
    && ($editFields['Partition_main']['type'] ?? null) === 'CheckBox'
    && ($editFields['Partition_main']['value'] ?? null) === true,
    'Sensor editor must select the default area and persist its assignment with a checkbox.'
);

$orphanedEditForm = $instance->GetSensorEditForm(new IPSList([
    'PartitionID'               => 'schuppen',
    'Partition_main'            => false,
    'Partition_schuppen'        => true,
    'VariableID'                => 12345,
    'TriggerValue'              => 'true'
]));
$orphanedEditFields = [];
foreach ($orphanedEditForm as $field) {
    if (isset($field['name'])) {
        $orphanedEditFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($orphanedEditFields['Partition_schuppen']['type'] ?? null) === 'CheckBox'
        && ($orphanedEditFields['Partition_schuppen']['value'] ?? null) === true
        && ($orphanedEditFields['Partition_schuppen']['enabled'] ?? false) === true,
    'Sensor editor must expose an orphaned partition assignment so the user can remove it.'
);
$modernOrphanedEditForm = $instance->GetSensorEditForm(new IPSList([
    'PartitionID'        => 'main',
    'Partition_main'     => true,
    'Partition_schuppen' => true,
    'VariableID'         => 12345,
    'TriggerValue'       => 'true'
]));
$modernOrphanedEditFields = [];
foreach ($modernOrphanedEditForm as $field) {
    if (isset($field['name'])) {
        $modernOrphanedEditFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($modernOrphanedEditFields['Partition_schuppen']['type'] ?? null) === 'CheckBox'
        && ($modernOrphanedEditFields['Partition_schuppen']['value'] ?? null) === true
        && ($modernOrphanedEditFields['Partition_schuppen']['enabled'] ?? false) === true,
    'Sensor editor must expose every orphaned modern multi-area assignment even when the legacy area remains main.'
);
assertSensorModel(
    ($editFields['AlwaysActive']['type'] ?? null) === 'CheckBox',
    'Sensor editor must expose 24/7 monitoring as a checkbox.'
);
assertSensorModel(
    ($editFields['AlwaysActive']['onChange'] ?? null) === 'OHA_UpdateSensorAlwaysActiveForm($id, $AlwaysActive);',
    'Changing the 24/7 setting must update the arming-mode and delay choices immediately.'
);
$alwaysActiveEditForm = $instance->GetSensorEditForm(new IPSList([
    'VariableID'   => 12345,
    'AlwaysActive' => true,
    'ArmHome'      => true,
    'ArmAway'      => true,
    'ArmNight'     => true,
    'ExitDelay'    => true,
    'EntryDelay'   => true
]));
$alwaysActiveEditFields = [];
foreach ($alwaysActiveEditForm as $field) {
    if (isset($field['name'])) {
        $alwaysActiveEditFields[$field['name']] = $field;
    }
}
foreach (['ArmHome', 'ArmAway', 'ArmNight', 'ExitDelay', 'EntryDelay', 'AllowAutomaticBypass', 'AllowPassage'] as $fieldName) {
    assertSensorModel(
        ($alwaysActiveEditFields[$fieldName]['enabled'] ?? true) === false
        && ($alwaysActiveEditFields[$fieldName]['value'] ?? true) === false,
        '24/7 sensors must disable and clear every irrelevant arming-mode and delay field.'
    );
}
$instance->TestClearFormUpdates();
$instance->UpdateSensorAlwaysActiveForm(true);
assertSensorModel(
    count($instance->TestFormUpdates()) === 14
    && array_column($instance->TestFormUpdates(), 'field') === ['ArmHome', 'ArmHome', 'ArmAway', 'ArmAway', 'ArmNight', 'ArmNight', 'ExitDelay', 'ExitDelay', 'EntryDelay', 'EntryDelay', 'AllowAutomaticBypass', 'AllowAutomaticBypass', 'AllowPassage', 'AllowPassage']
    && array_column($instance->TestFormUpdates(), 'value') === array_fill(0, 14, false),
    'Switching a sensor to 24/7 must immediately clear and disable every arming-mode and delay choice.'
);
assertSensorModel(
    ($editFields['ExitDelay']['type'] ?? null) === 'CheckBox',
    'Sensor editor must expose the exit route as a checkbox.'
);
assertSensorModel(
    ($editFields['VariableID']['onChange'] ?? null) === 'OHA_UpdateSensorTriggerValueForm($id, $VariableID, $TriggerValue);',
    'Changing the sensor variable must rebuild the trigger-value editor.'
);
assertSensorModel(
    ($editFields['TriggerValue']['type'] ?? null) === 'ValidationTextBox'
        && ($editFields['TriggerValue']['visible'] ?? true) === false,
    'TriggerValue must remain a hidden canonical string field.'
);
assertSensorModel(
    ($editFields['TriggerValueSelection']['type'] ?? null) === 'Select',
    'Discrete trigger values must use a consistent Select field.'
);
assertSensorModel(
    ($editFields['TriggerValueSelection']['options'] ?? null) === [
        ['caption' => 'Aus', 'value' => 'false'],
        ['caption' => 'An', 'value' => 'true']
    ],
    'Boolean presentation options must be exposed as selectable captions.'
);
assertSensorModel(
    ($editFields['TriggerValueSelection']['value'] ?? null) === 'true'
        && ($editFields['TriggerValueSelection']['visible'] ?? false) === true,
    'Boolean trigger selection must restore the persisted raw value.'
);
assertSensorModel(
    ($editFields['TriggerValueManual']['visible'] ?? true) === false,
    'Manual trigger input must be hidden when selectable states exist.'
);

$normalValueEditForm = $instance->GetSensorEditForm(new IPSList([
    'VariableID'       => 12345,
    'TriggerCondition' => 'not_equals',
    'TriggerValue'     => 'false'
]));
$normalValueFields = [];
foreach ($normalValueEditForm as $field) {
    if (isset($field['name'])) {
        $normalValueFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($normalValueFields['TriggerValueSelection']['caption'] ?? null) === 'Normal value'
        && ($normalValueFields['TriggerValueManual']['caption'] ?? null) === 'Normal value',
    'Normal-value evaluation must label the selected comparison state as the normal value.'
);
$instance->TestClearFormUpdates();
$instance->UpdateSensorTriggerConditionForm('not_equals');
assertSensorModel(
    $instance->TestFormUpdates() === [
        ['field' => 'TriggerValueSelection', 'parameter' => 'caption', 'value' => 'Normal value'],
        ['field' => 'TriggerValueManual', 'parameter' => 'caption', 'value' => 'Normal value'],
        [
            'field'     => 'TriggerValueHint',
            'parameter' => 'caption',
            'value'     => 'Select a variable to choose its normal value.'
        ],
        [
            'field'     => 'TriggerValueManualHint',
            'parameter' => 'caption',
            'value'     => 'This variable has no selectable states. Enter the raw normal value.'
        ]
    ],
    'Changing the evaluation rule must relabel the comparison value without reopening the editor.'
);

$stringEditForm = $instance->GetSensorEditForm(new IPSList([
    'VariableID'   => 23456,
    'TriggerValue' => 'ALARM'
]));
$stringFields = [];
foreach ($stringEditForm as $field) {
    if (isset($field['name'])) {
        $stringFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($stringFields['TriggerValueSelection']['options'] ?? null) === [
        ['caption' => 'Alarm', 'value' => 'ALARM'],
        ['caption' => 'Standby', 'value' => 'IDLE']
    ],
    'String presentation options must be exposed as a selectable list.'
);
assertSensorModel(
    ($stringFields['TriggerValueSelection']['value'] ?? null) === 'ALARM',
    'String trigger selection must preserve the raw String value.'
);

$legacySelectValueForm = $instance->GetSensorEditForm(new IPSList([
    'VariableID'   => 23456,
    'TriggerValue' => '"IDLE"'
]));
$legacySelectValueFields = [];
foreach ($legacySelectValueForm as $field) {
    if (isset($field['name'])) {
        $legacySelectValueFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($legacySelectValueFields['TriggerValueSelection']['value'] ?? null) === 'IDLE',
    'Previously JSON-encoded SelectValue strings must still resolve to their raw String option.'
);

$intervalEditForm = $instance->GetSensorEditForm(new IPSList([
    'VariableID'   => 34567,
    'TriggerValue' => '1'
]));
$intervalFields = [];
foreach ($intervalEditForm as $field) {
    if (isset($field['name'])) {
        $intervalFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($intervalFields['TriggerValueSelection']['options'] ?? null) === [
        ['caption' => 'Geschlossen', 'value' => '0'],
        ['caption' => 'Offen', 'value' => '1']
    ],
    'Discrete value-presentation intervals must become selectable trigger states.'
);

$legacyEditForm = $instance->GetSensorEditForm(new IPSList([
    'VariableID'   => 56789,
    'TriggerValue' => '1'
]));
$legacyFields = [];
foreach ($legacyEditForm as $field) {
    if (isset($field['name'])) {
        $legacyFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($legacyFields['TriggerValueSelection']['options'] ?? null) === [
        ['caption' => 'Inaktiv', 'value' => '0'],
        ['caption' => 'Aktiv', 'value' => '1']
    ],
    'Legacy profile associations must remain usable as trigger choices.'
);

$manualEditForm = $instance->GetSensorEditForm(new IPSList([
    'VariableID'   => 45678,
    'TriggerValue' => 'ALARM'
]));
$manualFields = [];
foreach ($manualEditForm as $field) {
    if (isset($field['name'])) {
        $manualFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($manualFields['TriggerValueSelection']['visible'] ?? true) === false,
    'Discrete trigger selector must be hidden when no states are defined.'
);
assertSensorModel(
    ($manualFields['TriggerValueManual']['visible'] ?? false) === true
        && ($manualFields['TriggerValueManual']['value'] ?? null) === 'ALARM',
    'Variables without discrete states must keep the manual raw-value fallback.'
);
assertSensorModel(
    ($manualFields['TriggerValueManualHint']['visible'] ?? false) === true,
    'Manual raw-value input must explain why no selection list is available.'
);

$emptyEditForm = $instance->GetSensorEditForm(new IPSList([
    'VariableID'   => 0,
    'TriggerValue' => '1'
]));
$emptyEditFields = [];
foreach ($emptyEditForm as $field) {
    if (isset($field['name'])) {
        $emptyEditFields[$field['name']] = $field;
    }
}
assertSensorModel(
    ($emptyEditFields['TriggerValueSelection']['visible'] ?? true) === false
        && ($emptyEditFields['TriggerValueManual']['visible'] ?? true) === false,
    'Trigger-value editors must stay hidden until a variable is selected.'
);
assertSensorModel(
    ($emptyEditFields['TriggerValueHint']['visible'] ?? false) === true,
    'Trigger-value hint must be shown until a variable is selected.'
);

$instance->TestClearFormUpdates();
$instance->UpdateSensorTriggerValueForm(23456, '1');
$expectedStringOptions = json_encode([
    ['caption' => 'Alarm', 'value' => 'ALARM'],
    ['caption' => 'Standby', 'value' => 'IDLE']
], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
assertSensorModel(
    $instance->TestFormUpdates() === [
        ['field' => 'TriggerValueSelection', 'parameter' => 'options', 'value' => $expectedStringOptions],
        ['field' => 'TriggerValueSelection', 'parameter' => 'value', 'value' => 'ALARM'],
        ['field' => 'TriggerValue', 'parameter' => 'value', 'value' => 'ALARM'],
        ['field' => 'TriggerValueSelection', 'parameter' => 'visible', 'value' => true],
        ['field' => 'TriggerValueManual', 'parameter' => 'visible', 'value' => false],
        ['field' => 'TriggerValueHint', 'parameter' => 'visible', 'value' => false],
        ['field' => 'TriggerValueManualHint', 'parameter' => 'visible', 'value' => false]
    ],
    'Selecting a String variable with options must rebuild a consistent dropdown and persist a valid raw value.'
);

$instance->TestClearFormUpdates();
$instance->UpdateSensorTriggerValueForm(45678, 'ALARM');
assertSensorModel(
    $instance->TestFormUpdates() === [
        ['field' => 'TriggerValueManual', 'parameter' => 'value', 'value' => 'ALARM'],
        ['field' => 'TriggerValueSelection', 'parameter' => 'visible', 'value' => false],
        ['field' => 'TriggerValueManual', 'parameter' => 'visible', 'value' => true],
        ['field' => 'TriggerValueHint', 'parameter' => 'visible', 'value' => false],
        ['field' => 'TriggerValueManualHint', 'parameter' => 'visible', 'value' => true]
    ],
    'Variables without discrete states must switch to the manual trigger-value input.'
);

$instance->TestClearFormUpdates();
$instance->UpdateSensorTriggerValueForm(99999, '1');
assertSensorModel(
    $instance->TestFormUpdates() === [
        ['field' => 'TriggerValueSelection', 'parameter' => 'visible', 'value' => false],
        ['field' => 'TriggerValueManual', 'parameter' => 'visible', 'value' => false],
        ['field' => 'TriggerValueHint', 'parameter' => 'visible', 'value' => true],
        ['field' => 'TriggerValueManualHint', 'parameter' => 'visible', 'value' => false]
    ],
    'Selecting an invalid variable must hide both trigger-value editors.'
);

$instance->TestClearFormUpdates();
$instance->SetSensorTriggerValue('IDLE');
assertSensorModel(
    $instance->TestFormUpdates() === [
        ['field' => 'TriggerValue', 'parameter' => 'value', 'value' => 'IDLE']
    ],
    'Visible trigger-value editors must update the canonical persisted TriggerValue field.'
);

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
    '24/7 active',
    'Allow temporary passage',
    'Exit route (Away only)',
    'Exit-route motion detectors may remain active through the end of a configured exit delay; all other sensors must be ready.',
    '24/7 sensors trigger immediately in every system state; mode assignments and entry/exit delay are ignored.',
    '24/7 sensors trigger immediately regardless of the current arming mode; mode assignments and entry/exit delay are ignored.',
    'Select a variable to choose its trigger value.',
    'Off',
    'On',
    'No selectable values',
    'This variable has no selectable states. Enter the raw trigger value.',
    'Selectable variable states are shown as a consistent list; variables without discrete states keep a raw-value input.'
] as $translationKey) {
    assertSensorModel(isset($translations[$translationKey]), 'Missing German translation for ' . $translationKey . '.');
}

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertSensorModel(
    !str_contains($moduleSource, "'type'       => 'SelectValue'")
        && !str_contains($moduleSource, "'type' => 'SelectValue'"),
    'A2 trigger editing must not fall back to the inconsistent SelectValue control.'
);

fwrite(STDOUT, "OpenHomeAlarm sensor model checks passed.\n");
