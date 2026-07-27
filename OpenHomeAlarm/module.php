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
    private const PROPERTY_EXIT_DELAY_SECONDS = 'ExitDelaySeconds';
    private const PROPERTY_ENTRY_DELAY_SECONDS = 'EntryDelaySeconds';
    private const PROPERTY_ALARM_ACTION = 'AlarmAction';
    private const PROPERTY_DISARM_AFTER_ALARM_ACTION = 'DisarmAfterAlarmAction';
    private const PROPERTY_DISARM_CODE = 'DisarmCode';

    private const ATTRIBUTE_EXIT_DELAY_DEADLINE = 'ExitDelayDeadline';
    private const ATTRIBUTE_ENTRY_DELAY_DEADLINE = 'EntryDelayDeadline';
    private const ATTRIBUTE_PENDING_ALARM_SOURCE_ID = 'PendingAlarmSourceID';
    private const ATTRIBUTE_BYPASSED_SENSOR_IDS = 'BypassedSensorIDs';

    private const TIMER_EXIT_DELAY = 'ExitDelay';
    private const TIMER_ENTRY_DELAY = 'EntryDelay';
    private const TIMER_DELAY_STATUS = 'DelayStatus';
    private const IDENT_MODE = 'Mode';
    private const IDENT_STATE = 'State';
    private const IDENT_DELAY_REMAINING = 'DelayRemaining';
    private const IDENT_DELAY_SOURCE = 'DelaySource';
    private const IDENT_READY_TO_ARM = 'ReadyToArm';
    private const IDENT_READY_HOME = 'ReadyHome';
    private const IDENT_READY_AWAY = 'ReadyAway';
    private const IDENT_READY_NIGHT = 'ReadyNight';
    private const IDENT_BLOCKING_HOME_SENSORS = 'BlockingHomeSensors';
    private const IDENT_BLOCKING_AWAY_SENSORS = 'BlockingAwaySensors';
    private const IDENT_BLOCKING_NIGHT_SENSORS = 'BlockingNightSensors';
    private const IDENT_BYPASSED_SENSORS = 'BypassedSensors';
    private const IDENT_ALARM_MEMORY = 'AlarmMemory';
    private const IDENT_LAST_ALARM_SOURCE = 'LastAlarmSource';
    private const IDENT_LAST_ALARM_TIME = 'LastAlarmTime';

    public function Create(): void
    {
        parent::Create();

        $this->RegisterPropertyString(self::PROPERTY_SENSORS, '[]');
        $this->RegisterPropertyInteger(self::PROPERTY_EXIT_DELAY_SECONDS, 30);
        $this->RegisterPropertyInteger(self::PROPERTY_ENTRY_DELAY_SECONDS, 30);
        $this->RegisterPropertyString(self::PROPERTY_ALARM_ACTION, '');
        $this->RegisterPropertyString(self::PROPERTY_DISARM_AFTER_ALARM_ACTION, '');
        $this->RegisterPropertyString(self::PROPERTY_DISARM_CODE, '');

        $this->RegisterAttributeInteger(self::ATTRIBUTE_EXIT_DELAY_DEADLINE, 0);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_ENTRY_DELAY_DEADLINE, 0);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_PENDING_ALARM_SOURCE_ID, 0);
        $this->RegisterAttributeString(self::ATTRIBUTE_BYPASSED_SENSOR_IDS, '[]');

        $this->RegisterTimer(
            self::TIMER_EXIT_DELAY,
            0,
            'OHA_CompleteExitDelay($_IPS[\'TARGET\']);'
        );
        $this->RegisterTimer(
            self::TIMER_ENTRY_DELAY,
            0,
            'OHA_CompleteEntryDelay($_IPS[\'TARGET\']);'
        );
        $this->RegisterTimer(
            self::TIMER_DELAY_STATUS,
            0,
            'OHA_UpdateDelayStatus($_IPS[\'TARGET\']);'
        );

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
        $delayRemainingCreated = $this->RegisterVariableInteger(
            self::IDENT_DELAY_REMAINING,
            $this->Translate('Delay remaining'),
            $this->IntegerPresentation('s', 0),
            21
        );
        $delaySourceCreated = $this->RegisterVariableString(
            self::IDENT_DELAY_SOURCE,
            $this->Translate('Delay source'),
            $this->TextPresentation(),
            22
        );
        $readinessPresentation = $this->CreateReadinessPresentation();
        $readyCreated = $this->RegisterVariableBoolean(
            self::IDENT_READY_TO_ARM,
            $this->Translate('Ready to arm'),
            $readinessPresentation,
            30
        );
        $readyHomeCreated = $this->RegisterVariableBoolean(
            self::IDENT_READY_HOME,
            $this->Translate('Ready Home'),
            $readinessPresentation,
            31
        );
        $readyAwayCreated = $this->RegisterVariableBoolean(
            self::IDENT_READY_AWAY,
            $this->Translate('Ready Away'),
            $readinessPresentation,
            32
        );
        $readyNightCreated = $this->RegisterVariableBoolean(
            self::IDENT_READY_NIGHT,
            $this->Translate('Ready Night'),
            $readinessPresentation,
            33
        );
        $blockingHomeSensorsCreated = $this->RegisterVariableString(
            self::IDENT_BLOCKING_HOME_SENSORS,
            $this->Translate('Blocking sensors Home'),
            $this->TextPresentation(),
            34
        );
        $blockingAwaySensorsCreated = $this->RegisterVariableString(
            self::IDENT_BLOCKING_AWAY_SENSORS,
            $this->Translate('Blocking sensors Away'),
            $this->TextPresentation(),
            35
        );
        $blockingNightSensorsCreated = $this->RegisterVariableString(
            self::IDENT_BLOCKING_NIGHT_SENSORS,
            $this->Translate('Blocking sensors Night'),
            $this->TextPresentation(),
            36
        );
        $bypassedSensorsCreated = $this->RegisterVariableString(
            self::IDENT_BYPASSED_SENSORS,
            $this->Translate('Bypassed sensors'),
            $this->TextPresentation(),
            37
        );
        $alarmMemoryCreated = $this->RegisterVariableBoolean(
            self::IDENT_ALARM_MEMORY,
            $this->Translate('Alarm memory'),
            $this->OptionsPresentation([
                [
                    'Value'       => false,
                    'Caption'     => $this->Translate('No alarm stored'),
                    'IconActive'  => false,
                    'IconValue'   => '',
                    'ColorActive' => false,
                    'ColorValue'  => -1
                ],
                [
                    'Value'       => true,
                    'Caption'     => $this->Translate('Alarm stored'),
                    'IconActive'  => false,
                    'IconValue'   => '',
                    'ColorActive' => false,
                    'ColorValue'  => -1
                ]
            ]),
            40
        );
        $lastAlarmSourceCreated = $this->RegisterVariableString(
            self::IDENT_LAST_ALARM_SOURCE,
            $this->Translate('Last alarm source'),
            $this->TextPresentation(),
            50
        );
        $lastAlarmTimeCreated = $this->RegisterVariableString(
            self::IDENT_LAST_ALARM_TIME,
            $this->Translate('Last alarm time'),
            $this->TextPresentation(),
            60
        );

        if ($modeCreated) {
            $this->SetAlarmMode(self::MODE_NONE);
        }
        if ($stateCreated) {
            $this->SetAlarmState(self::STATE_DISARMED);
        }
        if ($delayRemainingCreated) {
            $this->SetDelayRemaining(0);
        }
        if ($delaySourceCreated) {
            $this->SetDelaySource('');
        }
        if ($readyCreated) {
            $this->SetReadyToArm(true);
        }
        if ($readyHomeCreated) {
            $this->SetReadyHome(true);
        }
        if ($readyAwayCreated) {
            $this->SetReadyAway(true);
        }
        if ($readyNightCreated) {
            $this->SetReadyNight(true);
        }
        if ($blockingHomeSensorsCreated) {
            $this->SetBlockingHomeSensors('');
        }
        if ($blockingAwaySensorsCreated) {
            $this->SetBlockingAwaySensors('');
        }
        if ($blockingNightSensorsCreated) {
            $this->SetBlockingNightSensors('');
        }
        if ($bypassedSensorsCreated) {
            $this->SetBypassedSensors('');
        }
        if ($alarmMemoryCreated) {
            $this->SetAlarmMemory(false);
        }
        if ($lastAlarmSourceCreated) {
            $this->SetLastAlarmSource('');
        }
        if ($lastAlarmTimeCreated) {
            $this->SetLastAlarmTime('');
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
        $this->NormalizeSensorBypasses($sensors);
        $this->SynchronizeSensorMessages($sensors);
        $this->UpdateReadinessFromSensors($sensors);
        $this->EvaluateAlwaysActiveSensors($sensors);
        $this->RestoreDelayTimers();
    }

    public function GetConfigurationForm(): string
    {
        $formJson = file_get_contents(__DIR__ . '/form.json');
        if ($formJson === false) {
            throw new RuntimeException('Unable to read configuration form.');
        }

        $form = json_decode($formJson, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($form)) {
            throw new UnexpectedValueException('Configuration form must decode to an array.');
        }

        if (isset($form['elements']) && is_array($form['elements'])) {
            foreach ($form['elements'] as &$element) {
                if (($element['type'] ?? null) !== 'List' || ($element['name'] ?? null) !== self::PROPERTY_SENSORS) {
                    continue;
                }

                $element['values'] = $this->CreateSensorListFormValues();
                break;
            }
            unset($element);
        }

        return json_encode(
            $form,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Reacts to updates of configured sensor variables.
     *
     * Sensor updates keep the readiness state current and, while armed, start the
     * configured entry delay or move the state to Alarm. Configured external
     * actions are executed centrally when the Alarm state is entered.
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

        $this->UpdateReadinessFromSensors($sensors);
        if ($this->HandleAlwaysActiveSensorUpdate($SenderID, $sensors)) {
            return;
        }

        $this->HandleSensorUpdateWhileArmed($SenderID, $sensors);
    }

    /**
     * Arms the system in Home mode when every sensor assigned to Home is ready.
     */
    public function ArmHome(): bool
    {
        return $this->ArmMode(self::MODE_HOME);
    }

    /**
     * Arms the system in Away mode when every sensor assigned to Away is ready.
     */
    public function ArmAway(): bool
    {
        return $this->ArmMode(self::MODE_AWAY);
    }

    /**
     * Arms the system in Night mode when every sensor assigned to Night is ready.
     */
    public function ArmNight(): bool
    {
        return $this->ArmMode(self::MODE_NIGHT);
    }

    /**
     * Disarms the system and clears the selected arming mode.
     */
    public function Disarm(): bool
    {
        $wasAlarm = $this->ReadAlarmState() === self::STATE_ALARM;

        $this->CancelDelayTimers();
        $this->SetAlarmState(self::STATE_DISARMED);
        $this->SetAlarmMode(self::MODE_NONE);
        $this->ClearSensorBypassesInternal();

        if ($wasAlarm) {
            $this->RunConfiguredAction(self::PROPERTY_DISARM_AFTER_ALARM_ACTION);
        }

        return true;
    }

    /**
     * Disarms the system after validating the optional numeric disarm code.
     *
     * An empty configured code keeps code validation disabled. This method is
     * intended for user-facing controls; trusted automations can continue to use
     * Disarm() directly.
     */
    public function DisarmWithCode(string $code): bool
    {
        $configuredCode = trim($this->ReadPropertyString(self::PROPERTY_DISARM_CODE));
        if ($configuredCode === '') {
            return $this->Disarm();
        }

        if (preg_match('/^[0-9]{4,8}$/', $configuredCode) !== 1) {
            $this->SendDebug(__FUNCTION__, 'Configured disarm code is invalid.', 0);

            return false;
        }

        if (!hash_equals($configuredCode, trim($code))) {
            $this->SendDebug(__FUNCTION__, 'Disarm code rejected.', 0);

            return false;
        }

        return $this->Disarm();
    }

    /**
     * Temporarily bypasses one configured arming sensor for the next arming cycle.
     *
     * Bypassing is only allowed while disarmed. 24/7 sensors cannot be bypassed.
     * The bypass survives ApplyChanges and restarts, but is cleared automatically
     * when the system is disarmed after an arming cycle.
     */
    public function BypassSensor(int $variableID): bool
    {
        if ($this->ReadAlarmState() !== self::STATE_DISARMED || $variableID <= 0) {
            return false;
        }

        $sensors = $this->ReadConfiguredSensors();
        $bypassable = false;
        foreach ($sensors as $sensor) {
            if (
                $sensor['Enabled']
                && !$sensor['AlwaysActive']
                && $sensor['VariableID'] === $variableID
                && $this->IsSensorUsedForArming($sensor)
            ) {
                $bypassable = true;
                break;
            }
        }

        if (!$bypassable) {
            return false;
        }

        $bypassedIDs = $this->ReadBypassedSensorIDs();
        $bypassedIDs[] = $variableID;
        $this->WriteBypassedSensorIDs($bypassedIDs);
        $this->UpdateBypassedSensorStatus($sensors);
        $this->UpdateReadinessFromSensors($sensors);

        return true;
    }

    /**
     * Removes one temporary sensor bypass while the system is disarmed.
     */
    public function RemoveSensorBypass(int $variableID): bool
    {
        if ($this->ReadAlarmState() !== self::STATE_DISARMED || $variableID <= 0) {
            return false;
        }

        $bypassedIDs = $this->ReadBypassedSensorIDs();
        if (!in_array($variableID, $bypassedIDs, true)) {
            return false;
        }

        $this->WriteBypassedSensorIDs(
            array_values(array_filter(
                $bypassedIDs,
                static fn (int $bypassedID): bool => $bypassedID !== $variableID
            ))
        );

        $sensors = $this->ReadConfiguredSensors();
        $this->UpdateBypassedSensorStatus($sensors);
        $this->UpdateReadinessFromSensors($sensors);

        return true;
    }

    /**
     * Clears every temporary sensor bypass while the system is disarmed.
     */
    public function ClearSensorBypasses(): bool
    {
        if ($this->ReadAlarmState() !== self::STATE_DISARMED) {
            return false;
        }

        $this->ClearSensorBypassesInternal();

        return true;
    }

    /**
     * Clears the stored alarm memory after the active alarm has ended.
     *
     * The memory deliberately survives disarming so the last alarm source remains
     * visible until it is acknowledged explicitly.
     */
    public function ClearAlarmMemory(): bool
    {
        if ($this->ReadAlarmState() === self::STATE_ALARM) {
            return false;
        }

        $this->SetAlarmMemory(false);
        $this->SetLastAlarmSource('');
        $this->SetLastAlarmTime('');

        return true;
    }

    /**
     * Completes a running exit delay. The system is armed only if the selected
     * mode is still ready at the end of the countdown.
     */
    public function CompleteExitDelay(): void
    {
        $this->StopDelayTimer(self::TIMER_EXIT_DELAY, self::ATTRIBUTE_EXIT_DELAY_DEADLINE);

        if ($this->ReadAlarmState() !== self::STATE_EXIT_DELAY) {
            $this->ClearDelayStatus();

            return;
        }

        $mode = $this->ReadAlarmMode();
        if (!in_array($mode, [self::MODE_HOME, self::MODE_AWAY, self::MODE_NIGHT], true)) {
            $this->Disarm();

            return;
        }

        $sensors = $this->ReadConfiguredSensors();
        $readiness = $this->UpdateReadinessFromSensors($sensors);
        if (!$this->IsModeReady($mode, $readiness)) {
            $this->Disarm();

            return;
        }

        $this->ClearDelayStatus();
        $this->SetAlarmState(self::STATE_ARMED);
    }

    /**
     * Completes a running entry delay and enters the Alarm state.
     */
    public function CompleteEntryDelay(): void
    {
        $pendingSourceID = $this->ReadAttributeInteger(self::ATTRIBUTE_PENDING_ALARM_SOURCE_ID);
        $this->StopDelayTimer(self::TIMER_ENTRY_DELAY, self::ATTRIBUTE_ENTRY_DELAY_DEADLINE);

        if ($this->ReadAlarmState() !== self::STATE_ENTRY_DELAY) {
            $this->ClearPendingAlarmSource();
            $this->ClearDelayStatus();

            return;
        }

        $sourceSensor = $this->FindAlarmSourceSensor($pendingSourceID, $this->ReadConfiguredSensors());
        $this->EnterAlarmState($sourceSensor, $pendingSourceID);
    }

    /**
     * Refreshes the user-facing countdown for a running entry or exit delay.
     *
     * The persisted deadline remains the source of truth. This one-second timer is
     * active only while a delay is running and therefore does not affect the actual
     * transition timing handled by the dedicated one-shot timers.
     */
    public function UpdateDelayStatus(): void
    {
        $state = $this->ReadAlarmState();
        $deadline = match ($state) {
            self::STATE_EXIT_DELAY  => $this->ReadAttributeInteger(self::ATTRIBUTE_EXIT_DELAY_DEADLINE),
            self::STATE_ENTRY_DELAY => $this->ReadAttributeInteger(self::ATTRIBUTE_ENTRY_DELAY_DEADLINE),
            default                 => 0
        };

        if ($deadline <= 0) {
            $this->ClearDelayStatus();

            return;
        }

        $remainingSeconds = max(0, $deadline - time());
        $this->SetDelayRemaining($remainingSeconds);
        $this->SetTimerInterval(self::TIMER_DELAY_STATUS, $remainingSeconds > 0 ? 1000 : 0);
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
                'name'    => 'AlwaysActive',
                'caption' => $this->Translate('24/7 active')
            ],
            [
                'type'    => 'CheckBox',
                'name'    => 'EntryDelay',
                'caption' => $this->Translate('Entry delay')
            ],
            [
                'type'    => 'Label',
                'caption' => $this->Translate('24/7 sensors trigger immediately in every system state; mode assignments and entry delay are ignored.')
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
    private function CreateReadinessPresentation(): array
    {
        return $this->OptionsPresentation([
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
        ]);
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
     * Supplies the non-persistent trigger editor fields with the stored trigger value.
     *
     * Symcon fills fields of an individual List edit form from same-named row values.
     * The helper columns are intentionally not persisted, so their values are restored
     * through the List values array whenever the configuration form is opened.
     *
     * @return list<array{TriggerValueSelection:string,TriggerValueManual:string}>
     */
    private function CreateSensorListFormValues(): array
    {
        try {
            $rows = json_decode($this->ReadPropertyString(self::PROPERTY_SENSORS), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($rows) || !array_is_list($rows)) {
            return [];
        }

        $values = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                $values[] = [
                    'TriggerValueSelection' => '',
                    'TriggerValueManual'    => ''
                ];
                continue;
            }

            $variableID = is_int($row['VariableID'] ?? null) ? $row['VariableID'] : 0;
            $triggerValue = is_string($row['TriggerValue'] ?? null) ? $row['TriggerValue'] : '1';
            $selectionValue = $triggerValue;

            if ($this->IsExistingVariable($variableID)) {
                $triggerOptions = $this->CreateTriggerValueOptions($variableID);
                if ($triggerOptions !== []) {
                    $selectionValue = $this->ResolveTriggerValueSelection($triggerValue, $triggerOptions);
                }
            }

            $values[] = [
                'TriggerValueSelection' => $selectionValue,
                'TriggerValueManual'    => $triggerValue
            ];
        }

        return $values;
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
     *     AlwaysActive: bool,
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
     *     AlwaysActive: bool,
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
            'AlwaysActive' => $this->ReadSensorBoolean($sensor, 'AlwaysActive', false),
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function SynchronizeSensorMessages(array $sensors): void
    {
        $wantedVariableIDs = [];
        foreach ($sensors as $sensor) {
            if (!$sensor['Enabled'] || !$this->IsSensorMonitored($sensor)) {
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function IsMonitoredSensorVariable(int $variableID, array $sensors): bool
    {
        foreach ($sensors as $sensor) {
            if (
                $sensor['Enabled']
                && $this->IsSensorMonitored($sensor)
                && $sensor['VariableID'] === $variableID
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluates and publishes the global and mode-specific arming readiness.
     *
     * ReadyToArm remains the conservative global summary: every monitored sensor
     * must be ready. ReadyHome, ReadyAway and ReadyNight only consider sensors
     * assigned to the respective mode plus every 24/7 sensor. The matching
     * blocking-sensor variables expose the concrete sensor names for diagnostics
     * and the later visualization.
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     *
     * @return array{global:bool,home:bool,away:bool,night:bool}
     */
    private function UpdateReadinessFromSensors(array $sensors): array
    {
        $status = $this->EvaluateReadinessStatus($sensors);
        $readiness = $status['readiness'];

        $this->SetReadyToArm($readiness['global']);
        $this->SetReadyHome($readiness['home']);
        $this->SetReadyAway($readiness['away']);
        $this->SetReadyNight($readiness['night']);
        $this->SetBlockingHomeSensors(implode(', ', $status['blockingHome']));
        $this->SetBlockingAwaySensors(implode(', ', $status['blockingAway']));
        $this->SetBlockingNightSensors(implode(', ', $status['blockingNight']));

        return $readiness;
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     *
     * @return array{global:bool,home:bool,away:bool,night:bool}
     */
    private function EvaluateReadiness(array $sensors): array
    {
        return $this->EvaluateReadinessStatus($sensors)['readiness'];
    }

    /**
     * Evaluates readiness and keeps the concrete blocking sensors grouped by mode.
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     *
     * @return array{
     *     readiness: array{global:bool,home:bool,away:bool,night:bool},
     *     blockingHome: list<string>,
     *     blockingAway: list<string>,
     *     blockingNight: list<string>
     * }
     */
    private function EvaluateReadinessStatus(array $sensors): array
    {
        $readiness = [
            'global' => true,
            'home'   => true,
            'away'   => true,
            'night'  => true
        ];
        $blockingHome = [];
        $blockingAway = [];
        $blockingNight = [];

        foreach ($sensors as $sensor) {
            if (!$sensor['Enabled'] || !$this->IsSensorMonitored($sensor)) {
                continue;
            }
            if ($this->IsSensorBypassed($sensor)) {
                continue;
            }

            $variableID = $sensor['VariableID'];
            if ($variableID === 0) {
                continue;
            }

            $sensorReady = $this->IsExistingVariable($variableID)
                && $this->GetSensorTriggerState($sensor) === false;

            if ($sensorReady) {
                continue;
            }

            $sensorName = $this->ResolveSensorDisplayName($sensor);
            $readiness['global'] = false;
            if ($sensor['AlwaysActive']) {
                $readiness['home'] = false;
                $readiness['away'] = false;
                $readiness['night'] = false;
                $blockingHome[] = $sensorName;
                $blockingAway[] = $sensorName;
                $blockingNight[] = $sensorName;

                continue;
            }

            if ($sensor['ArmHome']) {
                $readiness['home'] = false;
                $blockingHome[] = $sensorName;
            }
            if ($sensor['ArmAway']) {
                $readiness['away'] = false;
                $blockingAway[] = $sensorName;
            }
            if ($sensor['ArmNight']) {
                $readiness['night'] = false;
                $blockingNight[] = $sensorName;
            }
        }

        return [
            'readiness'     => $readiness,
            'blockingHome'  => array_values(array_unique($blockingHome)),
            'blockingAway'  => array_values(array_unique($blockingAway)),
            'blockingNight' => array_values(array_unique($blockingNight))
        ];
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * } $sensor
     */
    private function ResolveSensorDisplayName(array $sensor): string
    {
        if ($sensor['Name'] !== '') {
            return $sensor['Name'];
        }

        return sprintf($this->Translate('Variable #%d'), $sensor['VariableID']);
    }

    /**
     * Tries to arm one concrete mode. Only sensors assigned to the requested mode
     * plus all 24/7 sensors participate in this decision.
     *
     * @param int $mode One of MODE_HOME, MODE_AWAY or MODE_NIGHT.
     */
    private function ArmMode(int $mode): bool
    {
        if (!in_array($mode, [self::MODE_HOME, self::MODE_AWAY, self::MODE_NIGHT], true)) {
            throw new InvalidArgumentException('Unsupported arming target mode.');
        }

        $sensors = $this->ReadConfiguredSensors();
        $readiness = $this->UpdateReadinessFromSensors($sensors);

        if (!$this->IsModeReady($mode, $readiness)) {
            return false;
        }

        $this->CancelDelayTimers();
        $this->SetAlarmMode($mode);

        $exitDelaySeconds = $this->ReadDelaySeconds(self::PROPERTY_EXIT_DELAY_SECONDS);
        if ($exitDelaySeconds === 0) {
            $this->SetAlarmState(self::STATE_ARMED);

            return true;
        }

        $this->SetAlarmState(self::STATE_EXIT_DELAY);
        $this->StartDelayTimer(
            self::TIMER_EXIT_DELAY,
            self::ATTRIBUTE_EXIT_DELAY_DEADLINE,
            $exitDelaySeconds
        );

        return true;
    }

    /**
     * @param array{global:bool,home:bool,away:bool,night:bool} $readiness
     */
    private function IsModeReady(int $mode, array $readiness): bool
    {
        return match ($mode) {
            self::MODE_HOME  => $readiness['home'],
            self::MODE_AWAY  => $readiness['away'],
            self::MODE_NIGHT => $readiness['night'],
            default          => throw new InvalidArgumentException('Unsupported arming target mode.')
        };
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * } $sensor
     */
    private function IsSensorRelevantForMode(array $sensor, int $mode): bool
    {
        return match ($mode) {
            self::MODE_HOME  => $sensor['ArmHome'],
            self::MODE_AWAY  => $sensor['ArmAway'],
            self::MODE_NIGHT => $sensor['ArmNight'],
            default          => throw new InvalidArgumentException('Unsupported arming target mode.')
        };
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * } $sensor
     */
    private function IsSensorUsedForArming(array $sensor): bool
    {
        return $sensor['ArmHome'] || $sensor['ArmAway'] || $sensor['ArmNight'];
    }

    /**
     * Returns whether an enabled sensor needs VM_UPDATE monitoring at all.
     *
     * 24/7 sensors are monitored even when they are not assigned to an arming mode.
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * } $sensor
     */
    private function IsSensorMonitored(array $sensor): bool
    {
        return $sensor['AlwaysActive'] || $this->IsSensorUsedForArming($sensor);
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
     *     AlwaysActive: bool,
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

    /**
     * @return list<int>
     */
    private function ReadBypassedSensorIDs(): array
    {
        $encodedIDs = $this->ReadAttributeString(self::ATTRIBUTE_BYPASSED_SENSOR_IDS);

        try {
            $decodedIDs = json_decode($encodedIDs, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($decodedIDs) || !array_is_list($decodedIDs)) {
            return [];
        }

        $bypassedIDs = [];
        foreach ($decodedIDs as $variableID) {
            if (is_int($variableID) && $variableID > 0) {
                $bypassedIDs[] = $variableID;
            }
        }

        sort($bypassedIDs, SORT_NUMERIC);

        return array_values(array_unique($bypassedIDs));
    }

    /**
     * @param list<int> $variableIDs
     */
    private function WriteBypassedSensorIDs(array $variableIDs): void
    {
        $normalizedIDs = [];
        foreach ($variableIDs as $variableID) {
            if ($variableID > 0) {
                $normalizedIDs[] = $variableID;
            }
        }

        sort($normalizedIDs, SORT_NUMERIC);
        $normalizedIDs = array_values(array_unique($normalizedIDs));

        $this->WriteAttributeString(
            self::ATTRIBUTE_BYPASSED_SENSOR_IDS,
            json_encode($normalizedIDs, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Removes stale or no-longer-bypassable entries after configuration changes.
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function NormalizeSensorBypasses(array $sensors): void
    {
        $validIDs = [];
        foreach ($sensors as $sensor) {
            if (
                $sensor['Enabled']
                && !$sensor['AlwaysActive']
                && $sensor['VariableID'] > 0
                && $this->IsSensorUsedForArming($sensor)
            ) {
                $validIDs[] = $sensor['VariableID'];
            }
        }

        $currentIDs = $this->ReadBypassedSensorIDs();
        $normalizedIDs = array_values(array_intersect($currentIDs, array_values(array_unique($validIDs))));
        sort($normalizedIDs, SORT_NUMERIC);

        if ($normalizedIDs !== $currentIDs) {
            $this->WriteBypassedSensorIDs($normalizedIDs);
        }

        if ($currentIDs !== [] || $normalizedIDs !== []) {
            $this->UpdateBypassedSensorStatus($sensors);
        }
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * } $sensor
     */
    private function IsSensorBypassed(array $sensor): bool
    {
        if ($sensor['AlwaysActive'] || $sensor['VariableID'] <= 0) {
            return false;
        }

        return in_array($sensor['VariableID'], $this->ReadBypassedSensorIDs(), true);
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function UpdateBypassedSensorStatus(array $sensors): void
    {
        $bypassedIDs = $this->ReadBypassedSensorIDs();
        $names = [];

        foreach ($bypassedIDs as $variableID) {
            $matched = false;
            foreach ($sensors as $sensor) {
                if (
                    !$sensor['Enabled']
                    || $sensor['AlwaysActive']
                    || $sensor['VariableID'] !== $variableID
                    || !$this->IsSensorUsedForArming($sensor)
                ) {
                    continue;
                }

                $names[] = $this->ResolveSensorDisplayName($sensor);
                $matched = true;
            }

            if (!$matched) {
                $names[] = sprintf($this->Translate('Variable #%d'), $variableID);
            }
        }

        $this->SetBypassedSensors(implode(', ', array_values(array_unique($names))));
    }

    private function ClearSensorBypassesInternal(): void
    {
        if ($this->ReadBypassedSensorIDs() === []) {
            return;
        }

        $this->WriteBypassedSensorIDs([]);
        $this->SetBypassedSensors('');

        try {
            $this->UpdateReadinessFromSensors($this->ReadConfiguredSensors());
        } catch (\Throwable $exception) {
            $this->SendDebug(__FUNCTION__, 'Unable to refresh readiness after clearing sensor bypasses: ' . $exception->getMessage(), 0);
        }
    }

    /**
     * Evaluates already active 24/7 sensors after ApplyChanges or a restart.
     *
     * This closes the restart gap where no VM_UPDATE would arrive for a sensor that
     * was already triggered before the module became active again.
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function EvaluateAlwaysActiveSensors(array $sensors): void
    {
        if ($this->ReadAlarmState() === self::STATE_ALARM) {
            return;
        }

        foreach ($sensors as $sensor) {
            if (!$sensor['Enabled'] || !$sensor['AlwaysActive'] || $sensor['VariableID'] <= 0) {
                continue;
            }
            if (!$this->IsExistingVariable($sensor['VariableID'])) {
                continue;
            }
            if ($this->GetSensorTriggerState($sensor) !== true) {
                continue;
            }

            $this->EnterAlarmState($sensor, $sensor['VariableID']);

            return;
        }
    }

    /**
     * Handles one update of a 24/7 sensor independently of the arming mode.
     *
     * 24/7 sensors always trigger immediately. Entry-delay settings and mode
     * assignments are intentionally ignored for these sensors.
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function HandleAlwaysActiveSensorUpdate(int $variableID, array $sensors): bool
    {
        foreach ($sensors as $sensor) {
            if (
                !$sensor['Enabled']
                || !$sensor['AlwaysActive']
                || $sensor['VariableID'] !== $variableID
            ) {
                continue;
            }

            if ($this->GetSensorTriggerState($sensor) !== true) {
                continue;
            }

            $this->EnterAlarmState($sensor, $sensor['VariableID']);

            return true;
        }

        return false;
    }

    /**
     * Reacts to one monitored sensor update while the system is armed or already
     * counting down an entry delay.
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function HandleSensorUpdateWhileArmed(int $variableID, array $sensors): void
    {
        $state = $this->ReadAlarmState();
        if (!in_array($state, [self::STATE_ARMED, self::STATE_ENTRY_DELAY], true)) {
            return;
        }

        $mode = $this->ReadAlarmMode();
        if (!in_array($mode, [self::MODE_HOME, self::MODE_AWAY, self::MODE_NIGHT], true)) {
            return;
        }

        $entryDelaySensor = null;
        foreach ($sensors as $sensor) {
            if (
                !$sensor['Enabled']
                || $sensor['AlwaysActive']
                || $this->IsSensorBypassed($sensor)
                || $sensor['VariableID'] !== $variableID
                || !$this->IsSensorRelevantForMode($sensor, $mode)
            ) {
                continue;
            }

            $triggered = $this->GetSensorTriggerState($sensor);
            if ($triggered === false) {
                continue;
            }

            if ($triggered === null || !$sensor['EntryDelay']) {
                $this->EnterAlarmState($sensor, $sensor['VariableID']);

                return;
            }

            $entryDelaySensor ??= $sensor;
        }

        if ($entryDelaySensor === null || $state === self::STATE_ENTRY_DELAY) {
            return;
        }

        $entryDelaySeconds = $this->ReadDelaySeconds(self::PROPERTY_ENTRY_DELAY_SECONDS);
        if ($entryDelaySeconds === 0) {
            $this->EnterAlarmState($entryDelaySensor, $entryDelaySensor['VariableID']);

            return;
        }

        $this->WriteAttributeInteger(self::ATTRIBUTE_PENDING_ALARM_SOURCE_ID, $entryDelaySensor['VariableID']);
        $this->SetDelaySource($this->ResolveSensorDisplayName($entryDelaySensor));
        $this->SetAlarmState(self::STATE_ENTRY_DELAY);
        $this->StartDelayTimer(
            self::TIMER_ENTRY_DELAY,
            self::ATTRIBUTE_ENTRY_DELAY_DEADLINE,
            $entryDelaySeconds
        );
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }|null $sourceSensor
     */
    private function EnterAlarmState(?array $sourceSensor = null, int $fallbackVariableID = 0): void
    {
        if ($this->ReadAlarmState() === self::STATE_ALARM) {
            return;
        }

        $this->RememberAlarm($sourceSensor, $fallbackVariableID);
        $this->CancelDelayTimers();
        $this->SetAlarmState(self::STATE_ALARM);
        $this->RunConfiguredAction(self::PROPERTY_ALARM_ACTION);
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }|null $sourceSensor
     */
    private function RememberAlarm(?array $sourceSensor, int $fallbackVariableID): void
    {
        $timestamp = time();
        $sourceName = '';
        if ($sourceSensor !== null) {
            $sourceName = trim($sourceSensor['Name']);
            $fallbackVariableID = $sourceSensor['VariableID'];
        }
        if ($sourceName === '' && $fallbackVariableID > 0) {
            $sourceName = sprintf($this->Translate('Variable #%d'), $fallbackVariableID);
        }
        if ($sourceName === '') {
            $sourceName = $this->Translate('Unknown trigger');
        }

        $this->SetAlarmMemory(true);
        $this->SetLastAlarmSource($sourceName);
        $this->SetLastAlarmTime(date('d.m.Y H:i:s', $timestamp));
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }> $sensors
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
     *     AlwaysActive: bool,
     *     EntryDelay: bool
     * }|null
     */
    private function FindAlarmSourceSensor(int $variableID, array $sensors): ?array
    {
        if ($variableID <= 0) {
            return null;
        }

        $mode = $this->ReadAlarmMode();
        foreach ($sensors as $sensor) {
            if (
                $sensor['Enabled']
                && $sensor['VariableID'] === $variableID
                && $this->IsSensorRelevantForMode($sensor, $mode)
            ) {
                return $sensor;
            }
        }

        return null;
    }

    private function RunConfiguredAction(string $propertyName): bool
    {
        $encodedAction = trim($this->ReadPropertyString($propertyName));
        if ($encodedAction === '' || $encodedAction === '{}') {
            return true;
        }

        try {
            $action = json_decode($encodedAction, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->SendDebug(__FUNCTION__, 'Invalid configured action: ' . $exception->getMessage(), 0);

            return false;
        }

        if (!is_array($action)) {
            $this->SendDebug(__FUNCTION__, 'Configured action must decode to an object.', 0);

            return false;
        }

        $actionID = $action['actionID'] ?? null;
        $parameters = $action['parameters'] ?? null;
        if (!is_string($actionID) || $actionID === '' || !is_array($parameters)) {
            $this->SendDebug(__FUNCTION__, 'Configured action is missing actionID or parameters.', 0);

            return false;
        }

        try {
            $executed = IPS_RunAction($actionID, $parameters);
        } catch (Throwable $exception) {
            $this->SendDebug(__FUNCTION__, 'Configured action failed: ' . $exception->getMessage(), 0);

            return false;
        }

        if (!$executed) {
            $this->SendDebug(__FUNCTION__, 'Configured action could not be started.', 0);
        }

        return $executed;
    }

    private function ReadAlarmMode(): int
    {
        $mode = $this->GetValue(self::IDENT_MODE);
        if (!is_int($mode) || !in_array($mode, self::VALID_MODES, true)) {
            throw new UnexpectedValueException('Invalid alarm mode value.');
        }

        return $mode;
    }

    private function ReadAlarmState(): int
    {
        $state = $this->GetValue(self::IDENT_STATE);
        if (!is_int($state) || !in_array($state, self::VALID_STATES, true)) {
            throw new UnexpectedValueException('Invalid alarm state value.');
        }

        return $state;
    }

    private function ReadDelaySeconds(string $propertyName): int
    {
        return max(0, $this->ReadPropertyInteger($propertyName));
    }

    private function StartDelayTimer(string $timerName, string $deadlineAttribute, int $seconds): void
    {
        $this->WriteAttributeInteger($deadlineAttribute, time() + $seconds);
        $this->SetTimerInterval($timerName, $seconds * 1000);
        $this->SetDelayRemaining($seconds);
        $this->SetTimerInterval(self::TIMER_DELAY_STATUS, 1000);
    }

    private function StopDelayTimer(string $timerName, string $deadlineAttribute): void
    {
        $this->SetTimerInterval($timerName, 0);
        $this->WriteAttributeInteger($deadlineAttribute, 0);
    }

    private function CancelDelayTimers(): void
    {
        $this->StopDelayTimer(self::TIMER_EXIT_DELAY, self::ATTRIBUTE_EXIT_DELAY_DEADLINE);
        $this->StopDelayTimer(self::TIMER_ENTRY_DELAY, self::ATTRIBUTE_ENTRY_DELAY_DEADLINE);
        $this->ClearPendingAlarmSource();
        $this->ClearDelayStatus();
    }

    private function ClearPendingAlarmSource(): void
    {
        $this->WriteAttributeInteger(self::ATTRIBUTE_PENDING_ALARM_SOURCE_ID, 0);
    }

    private function ClearDelayStatus(): void
    {
        $this->SetTimerInterval(self::TIMER_DELAY_STATUS, 0);
        if ($this->GetValue(self::IDENT_DELAY_REMAINING) !== 0) {
            $this->SetDelayRemaining(0);
        }
        if ($this->GetValue(self::IDENT_DELAY_SOURCE) !== '') {
            $this->SetDelaySource('');
        }
    }

    /**
     * Restores a running delay after ApplyChanges or a Symcon restart. Registered
     * timers are stateless, therefore the persisted deadline is the source of truth.
     */
    private function RestoreDelayTimers(): void
    {
        $state = $this->ReadAlarmState();

        if ($state === self::STATE_EXIT_DELAY) {
            $this->StopDelayTimer(self::TIMER_ENTRY_DELAY, self::ATTRIBUTE_ENTRY_DELAY_DEADLINE);
            $this->SetDelaySource('');
            $this->RestoreDelayTimer(
                self::TIMER_EXIT_DELAY,
                self::ATTRIBUTE_EXIT_DELAY_DEADLINE,
                function (): void
                {
                    $this->CompleteExitDelay();
                }
            );
            if ($this->ReadAlarmState() === self::STATE_EXIT_DELAY) {
                $this->UpdateDelayStatus();
            }

            return;
        }

        if ($state === self::STATE_ENTRY_DELAY) {
            $this->StopDelayTimer(self::TIMER_EXIT_DELAY, self::ATTRIBUTE_EXIT_DELAY_DEADLINE);
            $pendingSourceID = $this->ReadAttributeInteger(self::ATTRIBUTE_PENDING_ALARM_SOURCE_ID);
            $sourceSensor = $this->FindAlarmSourceSensor($pendingSourceID, $this->ReadConfiguredSensors());
            $this->SetDelaySource(
                $sourceSensor !== null
                    ? $this->ResolveSensorDisplayName($sourceSensor)
                    : ($pendingSourceID > 0 ? sprintf($this->Translate('Variable #%d'), $pendingSourceID) : '')
            );
            $this->RestoreDelayTimer(
                self::TIMER_ENTRY_DELAY,
                self::ATTRIBUTE_ENTRY_DELAY_DEADLINE,
                function (): void
                {
                    $this->CompleteEntryDelay();
                }
            );
            if ($this->ReadAlarmState() === self::STATE_ENTRY_DELAY) {
                $this->UpdateDelayStatus();
            }

            return;
        }

        $this->CancelDelayTimers();
    }

    private function RestoreDelayTimer(string $timerName, string $deadlineAttribute, Closure $expiredCallback): void
    {
        $deadline = $this->ReadAttributeInteger($deadlineAttribute);
        if ($deadline <= 0) {
            $expiredCallback();

            return;
        }

        $remainingSeconds = $deadline - time();
        if ($remainingSeconds <= 0) {
            $expiredCallback();

            return;
        }

        $this->SetTimerInterval($timerName, $remainingSeconds * 1000);
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

    private function SetDelayRemaining(int $seconds): void
    {
        $this->SetValue(self::IDENT_DELAY_REMAINING, max(0, $seconds));
    }

    private function SetDelaySource(string $source): void
    {
        $this->SetValue(self::IDENT_DELAY_SOURCE, $source);
    }

    private function SetReadyToArm(bool $ready): void
    {
        $this->SetValue(self::IDENT_READY_TO_ARM, $ready);
    }

    private function SetReadyHome(bool $ready): void
    {
        $this->SetValue(self::IDENT_READY_HOME, $ready);
    }

    private function SetReadyAway(bool $ready): void
    {
        $this->SetValue(self::IDENT_READY_AWAY, $ready);
    }

    private function SetReadyNight(bool $ready): void
    {
        $this->SetValue(self::IDENT_READY_NIGHT, $ready);
    }

    private function SetBlockingHomeSensors(string $sensors): void
    {
        $this->SetValue(self::IDENT_BLOCKING_HOME_SENSORS, $sensors);
    }

    private function SetBlockingAwaySensors(string $sensors): void
    {
        $this->SetValue(self::IDENT_BLOCKING_AWAY_SENSORS, $sensors);
    }

    private function SetBlockingNightSensors(string $sensors): void
    {
        $this->SetValue(self::IDENT_BLOCKING_NIGHT_SENSORS, $sensors);
    }

    private function SetBypassedSensors(string $sensors): void
    {
        $this->SetValue(self::IDENT_BYPASSED_SENSORS, $sensors);
    }

    private function SetAlarmMemory(bool $active): void
    {
        $this->SetValue(self::IDENT_ALARM_MEMORY, $active);
    }

    private function SetLastAlarmSource(string $source): void
    {
        $this->SetValue(self::IDENT_LAST_ALARM_SOURCE, $source);
    }

    private function SetLastAlarmTime(string $time): void
    {
        $this->SetValue(self::IDENT_LAST_ALARM_TIME, $time);
    }
}
