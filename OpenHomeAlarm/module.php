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

        // Validate the persisted sensor configuration before it is used by runtime logic.
        $this->ReadConfiguredSensors();
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
        $hasVariable = $this->IsExistingVariable($variableID);

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
                'onChange' => 'OHA_UpdateSensorTriggerValueForm($id, $VariableID);'
            ],
            [
                'type'    => 'Select',
                'name'    => 'SensorType',
                'caption' => $this->Translate('Sensor type'),
                'options' => $this->CreateSensorTypeOptions()
            ],
            [
                'type'       => 'SelectValue',
                'name'       => 'TriggerValue',
                'caption'    => $this->Translate('Trigger value'),
                'variableID' => $hasVariable ? $variableID : 1,
                'visible'    => $hasVariable
            ],
            [
                'type'    => 'Label',
                'name'    => 'TriggerValueHint',
                'caption' => $this->Translate('Select a variable to choose its trigger value.'),
                'visible' => !$hasVariable
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
     * Rebinds the trigger-value selector when another Symcon variable is chosen in the sensor editor.
     */
    public function UpdateSensorTriggerValueForm(int $variableID): void
    {
        $hasVariable = $this->IsExistingVariable($variableID);

        if ($hasVariable) {
            $this->UpdateFormField('TriggerValue', 'variableID', $variableID);
        }

        $this->UpdateFormField('TriggerValue', 'visible', $hasVariable);
        $this->UpdateFormField('TriggerValueHint', 'visible', !$hasVariable);
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
