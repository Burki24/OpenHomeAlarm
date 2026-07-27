<?php

declare(strict_types=1);

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
    ]
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

final class IPSList implements ArrayAccess
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
}

function IPS_VariableExists(int $variableID): bool
{
    global $testVariables;

    return array_key_exists($variableID, $testVariables);
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
    array_keys($instance->TestRegisteredVariables()) === [
        'Mode',
        'State',
        'DelayRemaining',
        'DelaySource',
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
        'LastAlarmTime'
    ],
    'The sensor model must keep the expected module status variables.'
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
        'AlwaysActive' => false,
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
        'AlwaysActive' => false,
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
        'AlwaysActive' => false,
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
    'AlwaysActive',
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
assertSensorModel(($columns['AlwaysActive']['add'] ?? null) === false, 'New sensors must not be 24/7 active by default.');
assertSensorModel(($columns['EntryDelay']['add'] ?? null) === false, 'New sensors must not use entry delay by default.');
assertSensorModel(
    ($columns['VariableID']['width'] ?? null) === 'auto',
    'Variable column must use the remaining list width so long Symcon paths stay readable.'
);
assertSensorModel(
    ($columns['Name']['width'] ?? null) === '220px'
        && ($columns['SensorType']['width'] ?? null) === '170px'
        && ($columns['TriggerValue']['width'] ?? null) === '125px',
    'Main sensor columns must use balanced fixed widths.'
);
assertSensorModel(
    ($columns['ArmHome']['width'] ?? null) === '90px'
        && ($columns['ArmAway']['width'] ?? null) === '95px'
        && ($columns['ArmNight']['width'] ?? null) === '75px'
        && ($columns['AlwaysActive']['width'] ?? null) === '70px'
        && ($columns['EntryDelay']['width'] ?? null) === '130px',
    'Mode and entry-delay columns must be wide enough for their captions.'
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
            'TriggerValueManual'    => 'ALARM'
        ],
        [
            'TriggerValueSelection' => 'true',
            'TriggerValueManual'    => 'true'
        ],
        [
            'TriggerValueSelection' => 'IDLE',
            'TriggerValueManual'    => '"IDLE"'
        ]
    ],
    'Generated List values must restore the persisted trigger value into non-persistent edit helper fields.'
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
    ($editFields['AlwaysActive']['type'] ?? null) === 'CheckBox',
    'Sensor editor must expose 24/7 monitoring as a checkbox.'
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
    '24/7 sensors trigger immediately in every system state; mode assignments and entry delay are ignored.',
    '24/7 sensors trigger immediately regardless of the current arming mode; mode assignments and entry delay are ignored.',
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
