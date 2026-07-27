<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/helper/VariablePresentationHelper.php';

class OpenHomeAlarm extends IPSModuleStrict
{
    use \Burki24\SymconModuleHelper\VariablePresentationHelper;

    private const MODE_NONE = 0;
    private const MODE_HOME = 1;
    private const MODE_AWAY = 2;
    private const MODE_NIGHT = 3;

    private const STATE_DISARMED = 0;
    private const STATE_EXIT_DELAY = 1;
    private const STATE_ARMED = 2;
    private const STATE_ENTRY_DELAY = 3;
    private const STATE_ALARM = 4;

    private const SENSOR_TYPE_OPENING = 0;
    private const SENSOR_TYPE_MOTION = 1;
    private const SENSOR_TYPE_GLASS_BREAK = 2;
    private const SENSOR_TYPE_SMOKE = 3;
    private const SENSOR_TYPE_WATER = 4;
    private const SENSOR_TYPE_PANIC = 5;
    private const SENSOR_TYPE_OTHER = 6;

    private const VALID_MODES = [
        self::MODE_NONE,
        self::MODE_HOME,
        self::MODE_AWAY,
        self::MODE_NIGHT
    ];

    private const VALID_STATES = [
        self::STATE_DISARMED,
        self::STATE_EXIT_DELAY,
        self::STATE_ARMED,
        self::STATE_ENTRY_DELAY,
        self::STATE_ALARM
    ];

    private const VALID_SENSOR_TYPES = [
        self::SENSOR_TYPE_OPENING,
        self::SENSOR_TYPE_MOTION,
        self::SENSOR_TYPE_GLASS_BREAK,
        self::SENSOR_TYPE_SMOKE,
        self::SENSOR_TYPE_WATER,
        self::SENSOR_TYPE_PANIC,
        self::SENSOR_TYPE_OTHER
    ];

    private const PROPERTY_SENSORS = 'Sensors';
    private const IDENT_MODE = 'Mode';
    private const IDENT_STATE = 'State';
    private const IDENT_READY_TO_ARM = 'ReadyToArm';

    public function Create(): void
    {
        parent::Create();

        $this->RegisterPropertyString(self::PROPERTY_SENSORS, '[]');

        $modeCreated = $this->RegisterVariableInteger(
            self::IDENT_MODE,
            $this->Translate('Mode'),
            $this->CreateModePresentation(),
            10
        );
        $stateCreated = $this->RegisterVariableInteger(
            self::IDENT_STATE,
            $this->Translate('State'),
            $this->CreateStatePresentation(),
            20
        );
        $readyCreated = $this->RegisterVariableBoolean(
            self::IDENT_READY_TO_ARM,
            $this->Translate('Ready to arm'),
            $this->OptionsPresentation([
                [
                    'Value'       => false,
                    'Caption'     => $this->Translate('Not ready'),
                    'IconActive'  => false,
                    'IconValue'   => '',
                    'ColorActive' => false,
                    'ColorValue'  => -1
                ],
                [
                    'Value'       => true,
                    'Caption'     => $this->Translate('Ready'),
                    'IconActive'  => false,
                    'IconValue'   => '',
                    'ColorActive' => false,
                    'ColorValue'  => -1
                ]
            ]),
            30
        );

        if ($modeCreated) {
            $this->SetAlarmMode(self::MODE_NONE);
        }
        if ($stateCreated) {
            $this->SetAlarmState(self::STATE_DISARMED);
        }
        if ($readyCreated) {
            $this->SetReadyToArm(true);
        }
    }

    public function Destroy(): void
    {
        parent::Destroy();
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        $sensors = $this->ReadConfiguredSensors();
        $this->SynchronizeSensorMessages($sensors);
        $this->UpdateReadyToArmFromSensors($sensors);
    }

    /**
     * Reacts to updates of configured sensor variables.
     *
     * A3 only observes and evaluates sensor states. Arming/disarming and alarm
     * transitions deliberately remain part of the following development step.
     */
    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        if ($Message !== VM_UPDATE) {
            return;
        }

        $sensors = $this->ReadConfiguredSensors();
        if (!$this->IsMonitoredSensorVariable($SenderID, $sensors)) {
            return;
        }

        $this->UpdateReadyToArmFromSensors($sensors);
    }

    /**
     * Builds the individual editor for one sensor list entry.
     *
     * Symcon supplies the selected List row as an IPSList object. The object supports
     * array-style access, while the array variant keeps the method easy to regression-test.
     *
     * @param mixed $sensor Current list row supplied by the Symcon configuration form.
     *
     * @return list<array<string,mixed>>
     */
    public function GetSensorEditForm(mixed $sensor): array
    {
        $variableID = $this->ReadSensorEditInteger($sensor, 'VariableID', 0);
        $triggerValue = $this->ReadSensorEditString($sensor, 'TriggerValue', '1');
        $hasVariable = $this->IsExistingVariable($variableID);
        $triggerOptions = $hasVariable ? $this->CreateTriggerValueOptions($variableID) : [];
        $hasTriggerOptions = $triggerOptions !== [];
        $selectedTriggerValue = $hasTriggerOptions
            ? $this->ResolveTriggerValueSelection($triggerValue, $triggerOptions)
            : $triggerValue;

        return [
            [
                'type'    => 'CheckBox',
                'name'    => 'Enabled',
                'caption' => $this->Translate('Enabled')
            ],
            [
                'type'    => 'ValidationTextBox',
                'name'    => 'Name',
                'caption' => $this->Translate('Name')
            ],
            [
                'type'     => 'SelectVariable',
                'name'     => 'VariableID',
                'caption'  => $this->Translate('Variable'),
                'onChange' => 'OHA_UpdateSensorTriggerValueForm($id, $VariableID, $TriggerValue);'
            ],
            [
                'type'    => 'Select',
                'name'    => 'SensorType',
                'caption' => $this->Translate('Sensor type'),
                'options' => $this->CreateSensorTypeOptions()
            ],
            [
                'type'    => 'ValidationTextBox',
                'name'    => 'TriggerValue',
                'visible' => false
            ],
            [
                'type'     => 'Select',
                'name'     => 'TriggerValueSelection',
                'caption'  => $this->Translate('Trigger value'),
                'options'  => $hasTriggerOptions ? $triggerOptions : $this->CreateEmptyTriggerValueOptions(),
                'value'    => $selectedTriggerValue,
                'visible'  => $hasVariable && $hasTriggerOptions,
                'onChange' => 'OHA_SetSensorTriggerValue($id, $TriggerValueSelection);'
            ],
            [
                'type'     => 'ValidationTextBox',
                'name'     => 'TriggerValueManual',
                'caption'  => $this->Translate('Trigger value'),
                'value'    => $triggerValue,
                'visible'  => $hasVariable && !$hasTriggerOptions,
                'onChange' => 'OHA_SetSensorTriggerValue($id, $TriggerValueManual);'
            ],
            [
                'type'    => 'Label',
                'name'    => 'TriggerValueHint',
                'caption' => $this->Translate('Select a variable to choose its trigger value.'),
                'visible' => !$hasVariable
            ],
            [
                'type'    => 'Label',
                'name'    => 'TriggerValueManualHint',
                'caption' => $this->Translate('This variable has no selectable states. Enter the raw trigger value.'),
                'visible' => $hasVariable && !$hasTriggerOptions
            ],
            [
                'type'    => 'CheckBox',
                'name'    => 'ArmHome',
                'caption' => $this->Translate('Home')
            ],
            [
                'type'    => 'CheckBox',
                'name'    => 'ArmAway',
                'caption' => $this->Translate('Away')
            ],
            [
                'type'    => 'CheckBox',
                'name'    => 'ArmNight',
                'caption' => $this->Translate('Night')
            ],
            [
                'type'    => 'CheckBox',
                'name'    => 'EntryDelay',
                'caption' => $this->Translate('Entry delay')
            ]
        ];
    }

    /**
     * Rebuilds the trigger-value choices when another Symcon variable is chosen.
     *
     * A native Select is used for variables which expose discrete presentation values.
     * This keeps Boolean, String and numeric enumerations visually consistent.
     */
    public function UpdateSensorTriggerValueForm(int $variableID, string $triggerValue): void
    {
        $hasVariable = $this->IsExistingVariable($variableID);
        $triggerOptions = $hasVariable ? $this->CreateTriggerValueOptions($variableID) : [];
        $hasTriggerOptions = $triggerOptions !== [];

        if ($hasTriggerOptions) {
            $selectedTriggerValue = $this->ResolveTriggerValueSelection($triggerValue, $triggerOptions);

            $this->UpdateFormField(
                'TriggerValueSelection',
                'options',
                json_encode($triggerOptions, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
            $this->UpdateFormField('TriggerValueSelection', 'value', $selectedTriggerValue);
            $this->UpdateFormField('TriggerValue', 'value', $selectedTriggerValue);
        } elseif ($hasVariable) {
            $this->UpdateFormField('TriggerValueManual', 'value', $triggerValue);
        }

        $this->UpdateFormField('TriggerValueSelection', 'visible', $hasVariable && $hasTriggerOptions);
        $this->UpdateFormField('TriggerValueManual', 'visible', $hasVariable && !$hasTriggerOptions);
        $this->UpdateFormField('TriggerValueHint', 'visible', !$hasVariable);
        $this->UpdateFormField('TriggerValueManualHint', 'visible', $hasVariable && !$hasTriggerOptions);
    }

    /**
     * Copies the visible trigger-value editor into the persisted TriggerValue field.
     */
    public function SetSensorTriggerValue(string $triggerValue): void
    {
        $this->UpdateFormField('TriggerValue', 'value', $triggerValue);
    }

    /**
     * @return array<string,mixed>
     */
    private function CreateModePresentation(): array
    {
        return $this->ValuePresentation(
            min: self::MODE_NONE,
            max: self::MODE_NIGHT,
            intervals: [
                $this->CreateConstantInterval(self::MODE_NONE, $this->Translate('No arming mode')),
                $this->CreateConstantInterval(self::MODE_HOME, $this->Translate('Home')),
                $this->CreateConstantInterval(self::MODE_AWAY, $this->Translate('Away')),
                $this->CreateConstantInterval(self::MODE_NIGHT, $this->Translate('Night'))
            ]
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function CreateStatePresentation(): array
    {
        return $this->ValuePresentation(
            min: self::STATE_DISARMED,
            max: self::STATE_ALARM,
            intervals: [
                $this->CreateConstantInterval(self::STATE_DISARMED, $this->Translate('Disarmed')),
                $this->CreateConstantInterval(self::STATE_EXIT_DELAY, $this->Translate('Exit delay')),
                $this->CreateConstantInterval(self::STATE_ARMED, $this->Translate('Armed')),
                $this->CreateConstantInterval(self::STATE_ENTRY_DELAY, $this->Translate('Entry delay')),
                $this->CreateConstantInterval(self::STATE_ALARM, $this->Translate('Alarm'))
            ]
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function CreateConstantInterval(int $value, string $caption): array
    {
        return [
            'IntervalMinValue' => $value,
            'IntervalMaxValue' => $value,
            'ConstantActive'   => true,
            'ConstantValue'    => $caption,
            'ConversionFactor' => 1,
            'PrefixActive'     => false,
            'PrefixValue'      => '',
            'SuffixActive'     => false,
            'SuffixValue'      => '',
            'DigitsActive'     => false,
            'DigitsValue'      => 0,
            'IconActive'       => false,
            'IconValue'        => '',
            'ColorActive'      => false,
            'ColorValue'       => -1
        ];
    }

    /**
     * @return list<array{caption:string,value:int}>
     */
    private function CreateSensorTypeOptions(): array
    {
        return [
            ['caption' => $this->Translate('Opening contact'), 'value' => self::SENSOR_TYPE_OPENING],
            ['caption' => $this->Translate('Motion detector'), 'value' => self::SENSOR_TYPE_MOTION],
            ['caption' => $this->Translate('Glass break detector'), 'value' => self::SENSOR_TYPE_GLASS_BREAK],
            ['caption' => $this->Translate('Smoke detector'), 'value' => self::SENSOR_TYPE_SMOKE],
            ['caption' => $this->Translate('Water detector'), 'value' => self::SENSOR_TYPE_WATER],
            ['caption' => $this->Translate('Panic trigger'), 'value' => self::SENSOR_TYPE_PANIC],
            ['caption' => $this->Translate('Other trigger'), 'value' => self::SENSOR_TYPE_OTHER]
        ];
    }

    /**
     * Reads an integer from a List edit row. Symcon exposes the row as an IPSList
     * object with array-style access instead of a native PHP array.
     */
    private function ReadSensorEditInteger(mixed $sensor, string $key, int $default): int
    {
        if (!is_array($sensor) && !is_object($sensor)) {
            throw new UnexpectedValueException('Sensor edit row must support array-style access.');
        }

        $value = $sensor[$key] ?? $default;
        if (!is_int($value)) {
            throw new UnexpectedValueException(sprintf('Sensor edit field %s must be integer.', $key));
        }

        return $value;
    }

    private function IsExistingVariable(int $variableID): bool
    {
        return $variableID > 0 && IPS_VariableExists($variableID);
    }

    /**
     * Reads a string from a List edit row. Symcon exposes the row as an IPSList
     * object with array-style access instead of a native PHP array.
     */
    private function ReadSensorEditString(mixed $sensor, string $key, string $default): string
    {
        if (!is_array($sensor) && !is_object($sensor)) {
            throw new UnexpectedValueException('Sensor edit row must support array-style access.');
        }

        $value = $sensor[$key] ?? $default;
        if (!is_string($value)) {
            throw new UnexpectedValueException(sprintf('Sensor edit field %s must be string.', $key));
        }

        return $value;
    }

    /**
     * @return list<array{caption:string,value:string}>
     */
    private function CreateTriggerValueOptions(int $variableID): array
    {
        if (!$this->IsExistingVariable($variableID)) {
            return [];
        }

        try {
            $presentation = IPS_GetVariablePresentation($variableID);
        } catch (\Throwable) {
            $presentation = [];
        }

        $options = $this->CreateTriggerOptionsFromPresentationOptions($presentation['OPTIONS'] ?? null);
        if ($options !== []) {
            return $options;
        }

        $options = $this->CreateTriggerOptionsFromPresentationIntervals($presentation['INTERVALS'] ?? null);
        if ($options !== []) {
            return $options;
        }

        $variable = $this->GetSymconVariable($variableID);
        if ($variable === null) {
            return [];
        }

        $options = $this->CreateTriggerOptionsFromVariableProfile($variable);
        if ($options !== []) {
            return $options;
        }

        if (($variable['VariableType'] ?? null) === 0) {
            return [
                ['caption' => $this->Translate('Off'), 'value' => 'false'],
                ['caption' => $this->Translate('On'), 'value' => 'true']
            ];
        }

        return [];
    }

    /**
     * @return list<array{caption:string,value:string}>
     */
    private function CreateEmptyTriggerValueOptions(): array
    {
        return [
            ['caption' => $this->Translate('No selectable values'), 'value' => '']
        ];
    }

    /**
     * @param mixed $encodedOptions
     *
     * @return list<array{caption:string,value:string}>
     */
    private function CreateTriggerOptionsFromPresentationOptions(mixed $encodedOptions): array
    {
        $presentationOptions = $this->DecodePresentationList($encodedOptions);
        $options = [];

        foreach ($presentationOptions as $option) {
            if (!is_array($option) || !array_key_exists('Value', $option)) {
                continue;
            }

            $value = $this->TriggerValueToStorageString($option['Value']);
            if ($value === null) {
                continue;
            }

            $caption = $option['Caption'] ?? $value;
            if (!is_string($caption) || $caption === '') {
                $caption = $value;
            }

            $options[$value] = [
                'caption' => $caption,
                'value'   => $value
            ];
        }

        return array_values($options);
    }

    /**
     * @param mixed $encodedIntervals
     *
     * @return list<array{caption:string,value:string}>
     */
    private function CreateTriggerOptionsFromPresentationIntervals(mixed $encodedIntervals): array
    {
        $presentationIntervals = $this->DecodePresentationList($encodedIntervals);
        $options = [];

        foreach ($presentationIntervals as $interval) {
            if (!is_array($interval)
                || ($interval['ConstantActive'] ?? false) !== true
                || !array_key_exists('IntervalMinValue', $interval)
                || !array_key_exists('IntervalMaxValue', $interval)
                || ($interval['IntervalMinValue'] !== $interval['IntervalMaxValue'])) {
                continue;
            }

            $value = $this->TriggerValueToStorageString($interval['IntervalMinValue']);
            if ($value === null) {
                continue;
            }

            $caption = $interval['ConstantValue'] ?? $value;
            if (!is_string($caption) || $caption === '') {
                $caption = $value;
            }

            $options[$value] = [
                'caption' => $caption,
                'value'   => $value
            ];
        }

        return array_values($options);
    }

    /**
     * @param array<string,mixed> $variable
     *
     * @return list<array{caption:string,value:string}>
     */
    private function CreateTriggerOptionsFromVariableProfile(array $variable): array
    {
        $profileName = '';
        if (isset($variable['VariableCustomProfile']) && is_string($variable['VariableCustomProfile'])) {
            $profileName = $variable['VariableCustomProfile'];
        }
        if ($profileName === '' && isset($variable['VariableProfile']) && is_string($variable['VariableProfile'])) {
            $profileName = $variable['VariableProfile'];
        }
        if ($profileName === '') {
            return [];
        }

        try {
            $profile = IPS_GetVariableProfile($profileName);
        } catch (\Throwable) {
            return [];
        }

        $associations = $profile['Associations'] ?? [];
        if (!is_array($associations)) {
            return [];
        }

        $options = [];
        foreach ($associations as $association) {
            if (!is_array($association) || !array_key_exists('Value', $association)) {
                continue;
            }

            $value = $this->TriggerValueToStorageString($association['Value'], $variable['VariableType'] ?? null);
            if ($value === null) {
                continue;
            }

            $caption = $association['Name'] ?? $value;
            if (!is_string($caption) || $caption === '') {
                $caption = $value;
            }

            $options[$value] = [
                'caption' => $caption,
                'value'   => $value
            ];
        }

        return array_values($options);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function GetSymconVariable(int $variableID): ?array
    {
        try {
            $variable = IPS_GetVariable($variableID);
        } catch (\Throwable) {
            return null;
        }

        return is_array($variable) ? $variable : null;
    }

    /**
     * @return list<mixed>
     */
    private function DecodePresentationList(mixed $value): array
    {
        if (is_array($value)) {
            return array_is_list($value) ? $value : [];
        }
        if (!is_string($value) || $value === '') {
            return [];
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($decoded) && array_is_list($decoded) ? $decoded : [];
    }

    private function TriggerValueToStorageString(mixed $value, ?int $variableType = null): ?string
    {
        if ($variableType === 0 && (is_bool($value) || is_int($value) || is_float($value))) {
            return (bool) $value ? 'true' : 'false';
        }
        if ($variableType === 1 && (is_int($value) || is_float($value))) {
            return (string) (int) $value;
        }
        if ($variableType === 2 && (is_int($value) || is_float($value))) {
            return json_encode((float) $value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        }
        if ($variableType === 3 && is_scalar($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        }
        if (is_string($value)) {
            return $value;
        }

        return null;
    }

    /**
     * @param list<array{caption:string,value:string}> $options
     */
    private function ResolveTriggerValueSelection(string $triggerValue, array $options): string
    {
        $values = array_column($options, 'value');
        if (in_array($triggerValue, $values, true)) {
            return $triggerValue;
        }

        try {
            $legacyValue = json_decode($triggerValue, true, 512, JSON_THROW_ON_ERROR);
            $normalizedLegacyValue = $this->TriggerValueToStorageString($legacyValue);
            if ($normalizedLegacyValue !== null && in_array($normalizedLegacyValue, $values, true)) {
                return $normalizedLegacyValue;
            }
        } catch (JsonException) {
        }

        if (in_array('true', $values, true)) {
            return 'true';
        }
        if (in_array('1', $values, true)) {
            return '1';
        }

        return $options[0]['value'];
    }

    /**
     * @return list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     SensorType: int,
     *     TriggerValue: string,
     *     ArmHome: bool,
     *     ArmAway: bool,
     *     ArmNight: bool,
     *     EntryDelay: bool
     * }>
     */
    private function ReadConfiguredSensors(): array
    {
        try {
            $sensors = json_decode(
                $this->ReadPropertyString(self::PROPERTY_SENSORS),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Invalid sensor configuration JSON.', 0, $exception);
        }

        if (!is_array($sensors) || !array_is_list($sensors)) {
            throw new UnexpectedValueException('Sensor configuration must be a list.');
        }

        $normalizedSensors = [];
        foreach ($sensors as $sensor) {
            if (!is_array($sensor)) {
                throw new UnexpectedValueException('Every sensor configuration must be an object.');
            }

            $normalizedSensors[] = $this->NormalizeSensor($sensor);
        }

        return $normalizedSensors;
    }

    /**
     * @param array<string,mixed> $sensor
     *
     * @return array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     SensorType: int,
     *     TriggerValue: string,
     *     ArmHome: bool,
     *     ArmAway: bool,
     *     ArmNight: bool,
     *     EntryDelay: bool
     * }
     */
    private function NormalizeSensor(array $sensor): array
    {
        $variableID = $this->ReadSensorInteger($sensor, 'VariableID', 0);
        if ($variableID < 0) {
            throw new UnexpectedValueException('Sensor VariableID must not be negative.');
        }

        $sensorType = $this->ReadSensorInteger($sensor, 'SensorType', self::SENSOR_TYPE_OPENING);
        if (!in_array($sensorType, self::VALID_SENSOR_TYPES, true)) {
            throw new UnexpectedValueException('Unsupported sensor type.');
        }

        return [
            'Enabled'      => $this->ReadSensorBoolean($sensor, 'Enabled', true),
            'Name'         => trim($this->ReadSensorString($sensor, 'Name', '')),
            'VariableID'   => $variableID,
            'SensorType'   => $sensorType,
            'TriggerValue' => $this->ReadSensorString($sensor, 'TriggerValue', '1'),
            'ArmHome'      => $this->ReadSensorBoolean($sensor, 'ArmHome', false),
            'ArmAway'      => $this->ReadSensorBoolean($sensor, 'ArmAway', true),
            'ArmNight'     => $this->ReadSensorBoolean($sensor, 'ArmNight', false),
            'EntryDelay'   => $this->ReadSensorBoolean($sensor, 'EntryDelay', false)
        ];
    }

    /**
     * @param array<string,mixed> $sensor
     */
    private function ReadSensorBoolean(array $sensor, string $key, bool $default): bool
    {
        $value = $sensor[$key] ?? $default;
        if (!is_bool($value)) {
            throw new UnexpectedValueException(sprintf('Sensor field %s must be boolean.', $key));
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $sensor
     */
    private function ReadSensorInteger(array $sensor, string $key, int $default): int
    {
        $value = $sensor[$key] ?? $default;
        if (!is_int($value)) {
            throw new UnexpectedValueException(sprintf('Sensor field %s must be integer.', $key));
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $sensor
     */
    private function ReadSensorString(array $sensor, string $key, string $default): string
    {
        $value = $sensor[$key] ?? $default;
        if (!is_string($value)) {
            throw new UnexpectedValueException(sprintf('Sensor field %s must be string.', $key));
        }

        return $value;
    }


    /**
     * Keeps VM_UPDATE subscriptions in sync with the enabled sensor configuration.
     *
     * @param list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     SensorType: int,
     *     TriggerValue: string,
     *     ArmHome: bool,
     *     ArmAway: bool,
     *     ArmNight: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function SynchronizeSensorMessages(array $sensors): void
    {
        $wantedVariableIDs = [];
        foreach ($sensors as $sensor) {
            if (!$sensor['Enabled'] || !$this->IsSensorUsedForArming($sensor)) {
                continue;
            }

            $variableID = $sensor['VariableID'];
            if ($variableID > 0 && $this->IsExistingVariable($variableID)) {
                $wantedVariableIDs[$variableID] = true;
            }
        }

        $registeredVariableIDs = [];
        foreach ($this->GetMessageList() as $senderID => $messages) {
            if (!is_array($messages) || !in_array(VM_UPDATE, $messages, true)) {
                continue;
            }

            $registeredVariableIDs[(int) $senderID] = true;
        }

        foreach (array_diff_key($registeredVariableIDs, $wantedVariableIDs) as $variableID => $_) {
            $this->UnregisterMessage((int) $variableID, VM_UPDATE);
        }

        foreach (array_diff_key($wantedVariableIDs, $registeredVariableIDs) as $variableID => $_) {
            $this->RegisterMessage((int) $variableID, VM_UPDATE);
        }
    }

    /**
     * @param list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     SensorType: int,
     *     TriggerValue: string,
     *     ArmHome: bool,
     *     ArmAway: bool,
     *     ArmNight: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function IsMonitoredSensorVariable(int $variableID, array $sensors): bool
    {
        foreach ($sensors as $sensor) {
            if (
                $sensor['Enabled']
                && $this->IsSensorUsedForArming($sensor)
                && $sensor['VariableID'] === $variableID
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Updates the global A3 readiness state from all enabled sensors which are used
     * by at least one arming mode. A4 will later refine this check for the requested
     * target mode while performing the actual arming operation.
     *
     * @param list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     SensorType: int,
     *     TriggerValue: string,
     *     ArmHome: bool,
     *     ArmAway: bool,
     *     ArmNight: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function UpdateReadyToArmFromSensors(array $sensors): void
    {
        foreach ($sensors as $sensor) {
            if (!$sensor['Enabled'] || !$this->IsSensorUsedForArming($sensor)) {
                continue;
            }

            $variableID = $sensor['VariableID'];
            if ($variableID === 0) {
                continue;
            }
            if (!$this->IsExistingVariable($variableID)) {
                $this->SetReadyToArm(false);

                return;
            }

            $triggered = $this->GetSensorTriggerState($sensor);
            if ($triggered !== false) {
                // Fail safe: a triggered sensor and an unreadable/invalid sensor state
                // both prevent the system from being reported as ready to arm.
                $this->SetReadyToArm(false);

                return;
            }
        }

        $this->SetReadyToArm(true);
    }

    /**
     * @param array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     SensorType: int,
     *     TriggerValue: string,
     *     ArmHome: bool,
     *     ArmAway: bool,
     *     ArmNight: bool,
     *     EntryDelay: bool
     * } $sensor
     */
    private function IsSensorUsedForArming(array $sensor): bool
    {
        return $sensor['ArmHome'] || $sensor['ArmAway'] || $sensor['ArmNight'];
    }

    /**
     * Returns true when the current variable value equals the configured trigger
     * value, false when it does not, and null when the state cannot be evaluated.
     *
     * @param array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     SensorType: int,
     *     TriggerValue: string,
     *     ArmHome: bool,
     *     ArmAway: bool,
     *     ArmNight: bool,
     *     EntryDelay: bool
     * } $sensor
     */
    private function GetSensorTriggerState(array $sensor): ?bool
    {
        $variable = $this->GetSymconVariable($sensor['VariableID']);
        if ($variable === null || !isset($variable['VariableType']) || !is_int($variable['VariableType'])) {
            return null;
        }

        try {
            $currentValue = GetValue($sensor['VariableID']);
        } catch (\Throwable) {
            return null;
        }

        return $this->TriggerValueMatchesCurrentValue(
            $variable['VariableType'],
            $sensor['TriggerValue'],
            $currentValue
        );
    }

    private function TriggerValueMatchesCurrentValue(int $variableType, string $triggerValue, mixed $currentValue): ?bool
    {
        switch ($variableType) {
            case 0:
                if (!is_bool($currentValue)) {
                    return null;
                }

                $normalizedTrigger = strtolower(trim($triggerValue));
                if (in_array($normalizedTrigger, ['true', '1'], true)) {
                    return $currentValue === true;
                }
                if (in_array($normalizedTrigger, ['false', '0'], true)) {
                    return $currentValue === false;
                }

                return null;

            case 1:
                if (!is_int($currentValue) || preg_match('/^-?\d+$/', trim($triggerValue)) !== 1) {
                    return null;
                }

                return $currentValue === (int) $triggerValue;

            case 2:
                if (!is_float($currentValue) || !is_numeric(trim($triggerValue))) {
                    return null;
                }

                return $currentValue === (float) $triggerValue;

            case 3:
                if (!is_string($currentValue)) {
                    return null;
                }

                return $currentValue === $triggerValue;
        }

        return null;
    }

    private function SetAlarmMode(int $mode): void
    {
        if (!in_array($mode, self::VALID_MODES, true)) {
            throw new InvalidArgumentException('Unsupported alarm mode.');
        }

        $this->SetValue(self::IDENT_MODE, $mode);
    }

    private function SetAlarmState(int $state): void
    {
        if (!in_array($state, self::VALID_STATES, true)) {
            throw new InvalidArgumentException('Unsupported alarm state.');
        }

        $this->SetValue(self::IDENT_STATE, $state);
    }

    private function SetReadyToArm(bool $ready): void
    {
        $this->SetValue(self::IDENT_READY_TO_ARM, $ready);
    }
}
