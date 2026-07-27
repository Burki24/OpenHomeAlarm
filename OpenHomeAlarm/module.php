<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/helper/VariablePresentationHelper.php';

class OpenHomeAlarm extends IPSModuleStrict
{
    use \Burki24\SymconModuleHelper\VariablePresentationHelper;

    private const CONTROL_API_VERSION = 1;

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

    private const FAULT_TYPE_TAMPER = 0;
    private const FAULT_TYPE_POWER = 1;
    private const FAULT_TYPE_COMMUNICATION = 2;
    private const FAULT_TYPE_DEVICE = 3;
    private const FAULT_TYPE_OTHER = 4;

    private const EVENT_ARM_REJECTED = 'arm_rejected';
    private const EVENT_ARM_CANCELLED = 'arm_cancelled';
    private const EVENT_EXIT_DELAY_STARTED = 'exit_delay_started';
    private const EVENT_ARMED = 'armed';
    private const EVENT_ENTRY_DELAY_STARTED = 'entry_delay_started';
    private const EVENT_ALARM = 'alarm';
    private const EVENT_ALARM_OUTPUT_RESET = 'alarm_output_reset';
    private const EVENT_DISARMED = 'disarmed';
    private const EVENT_DISARM_CODE_REJECTED = 'disarm_code_rejected';
    private const EVENT_SENSOR_BYPASSED = 'sensor_bypassed';
    private const EVENT_SENSOR_BYPASS_REMOVED = 'sensor_bypass_removed';
    private const EVENT_SENSOR_BYPASSES_CLEARED = 'sensor_bypasses_cleared';
    private const EVENT_ALARM_MEMORY_CLEARED = 'alarm_memory_cleared';
    private const EVENT_FAULT_ACTIVATED = 'fault_activated';
    private const EVENT_FAULT_CLEARED = 'fault_cleared';

    private const EVENT_HISTORY_LIMIT = 100;

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

    private const VALID_FAULT_TYPES = [
        self::FAULT_TYPE_TAMPER,
        self::FAULT_TYPE_POWER,
        self::FAULT_TYPE_COMMUNICATION,
        self::FAULT_TYPE_DEVICE,
        self::FAULT_TYPE_OTHER
    ];

    private const PROPERTY_SENSORS = 'Sensors';
    private const PROPERTY_FAULT_INPUTS = 'FaultInputs';
    private const PROPERTY_EXIT_DELAY_SECONDS = 'ExitDelaySeconds';
    private const PROPERTY_ENTRY_DELAY_SECONDS = 'EntryDelaySeconds';
    private const PROPERTY_ALARM_DURATION_SECONDS = 'AlarmDurationSeconds';
    private const PROPERTY_ALARM_ACTION = 'AlarmAction';
    private const PROPERTY_ALARM_RESET_ACTION = 'AlarmResetAction';
    private const PROPERTY_DISARM_AFTER_ALARM_ACTION = 'DisarmAfterAlarmAction';
    private const PROPERTY_FAULT_ACTION = 'FaultAction';
    private const PROPERTY_FAULT_CLEARED_ACTION = 'FaultClearedAction';
    private const PROPERTY_DISARM_CODE = 'DisarmCode';

    private const ATTRIBUTE_EXIT_DELAY_DEADLINE = 'ExitDelayDeadline';
    private const ATTRIBUTE_ENTRY_DELAY_DEADLINE = 'EntryDelayDeadline';
    private const ATTRIBUTE_ALARM_DURATION_DEADLINE = 'AlarmDurationDeadline';
    private const ATTRIBUTE_ALARM_OUTPUT_ACTIVE = 'AlarmOutputActive';
    private const ATTRIBUTE_PENDING_ALARM_SOURCE_ID = 'PendingAlarmSourceID';
    private const ATTRIBUTE_BYPASSED_SENSOR_IDS = 'BypassedSensorIDs';
    private const ATTRIBUTE_ACTIVE_FAULT_VARIABLE_IDS = 'ActiveFaultVariableIDs';
    private const ATTRIBUTE_EVENT_HISTORY = 'EventHistory';

    private const TIMER_EXIT_DELAY = 'ExitDelay';
    private const TIMER_ENTRY_DELAY = 'EntryDelay';
    private const TIMER_DELAY_STATUS = 'DelayStatus';
    private const TIMER_ALARM_DURATION = 'AlarmDuration';
    private const IDENT_MODE = 'Mode';
    private const IDENT_STATE = 'State';
    private const IDENT_DELAY_REMAINING = 'DelayRemaining';
    private const IDENT_DELAY_SOURCE = 'DelaySource';
    private const IDENT_ALARM_OUTPUT_ACTIVE = 'AlarmOutputActive';
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
    private const IDENT_SYSTEM_FAULT = 'SystemFault';
    private const IDENT_ACTIVE_FAULTS = 'ActiveFaults';
    private const IDENT_BLOCKING_FAULTS = 'BlockingFaults';
    private const IDENT_LAST_FAULT_SOURCE = 'LastFaultSource';
    private const IDENT_LAST_FAULT_TIME = 'LastFaultTime';

    public function Create(): void
    {
        parent::Create();

        $this->RegisterPropertyString(self::PROPERTY_SENSORS, '[]');
        $this->RegisterPropertyString(self::PROPERTY_FAULT_INPUTS, '[]');
        $this->RegisterPropertyInteger(self::PROPERTY_EXIT_DELAY_SECONDS, 30);
        $this->RegisterPropertyInteger(self::PROPERTY_ENTRY_DELAY_SECONDS, 30);
        $this->RegisterPropertyInteger(self::PROPERTY_ALARM_DURATION_SECONDS, 0);
        $this->RegisterPropertyString(self::PROPERTY_ALARM_ACTION, '');
        $this->RegisterPropertyString(self::PROPERTY_ALARM_RESET_ACTION, '');
        $this->RegisterPropertyString(self::PROPERTY_DISARM_AFTER_ALARM_ACTION, '');
        $this->RegisterPropertyString(self::PROPERTY_FAULT_ACTION, '');
        $this->RegisterPropertyString(self::PROPERTY_FAULT_CLEARED_ACTION, '');
        $this->RegisterPropertyString(self::PROPERTY_DISARM_CODE, '');

        $this->RegisterAttributeInteger(self::ATTRIBUTE_EXIT_DELAY_DEADLINE, 0);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_ENTRY_DELAY_DEADLINE, 0);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_ALARM_DURATION_DEADLINE, 0);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_ALARM_OUTPUT_ACTIVE, 0);
        $this->RegisterAttributeInteger(self::ATTRIBUTE_PENDING_ALARM_SOURCE_ID, 0);
        $this->RegisterAttributeString(self::ATTRIBUTE_BYPASSED_SENSOR_IDS, '[]');
        $this->RegisterAttributeString(self::ATTRIBUTE_ACTIVE_FAULT_VARIABLE_IDS, '[]');
        $this->RegisterAttributeString(self::ATTRIBUTE_EVENT_HISTORY, '[]');

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
        $this->RegisterTimer(
            self::TIMER_ALARM_DURATION,
            0,
            'OHA_CompleteAlarmDuration($_IPS[\'TARGET\']);'
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
        $alarmOutputActiveCreated = $this->RegisterVariableBoolean(
            self::IDENT_ALARM_OUTPUT_ACTIVE,
            $this->Translate('Alarm output active'),
            $this->OptionsPresentation([
                [
                    'Value'       => false,
                    'Caption'     => $this->Translate('Alarm output inactive'),
                    'IconActive'  => false,
                    'IconValue'   => '',
                    'ColorActive' => false,
                    'ColorValue'  => -1
                ],
                [
                    'Value'       => true,
                    'Caption'     => $this->Translate('Alarm output active'),
                    'IconActive'  => false,
                    'IconValue'   => '',
                    'ColorActive' => false,
                    'ColorValue'  => -1
                ]
            ]),
            23
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
        $systemFaultCreated = $this->RegisterVariableBoolean(
            self::IDENT_SYSTEM_FAULT,
            $this->Translate('System fault'),
            $this->OptionsPresentation([
                [
                    'Value'       => false,
                    'Caption'     => $this->Translate('No system fault'),
                    'IconActive'  => false,
                    'IconValue'   => '',
                    'ColorActive' => false,
                    'ColorValue'  => -1
                ],
                [
                    'Value'       => true,
                    'Caption'     => $this->Translate('System fault active'),
                    'IconActive'  => false,
                    'IconValue'   => '',
                    'ColorActive' => false,
                    'ColorValue'  => -1
                ]
            ]),
            70
        );
        $activeFaultsCreated = $this->RegisterVariableString(
            self::IDENT_ACTIVE_FAULTS,
            $this->Translate('Active faults'),
            $this->TextPresentation(),
            71
        );
        $blockingFaultsCreated = $this->RegisterVariableString(
            self::IDENT_BLOCKING_FAULTS,
            $this->Translate('Blocking faults'),
            $this->TextPresentation(),
            72
        );
        $lastFaultSourceCreated = $this->RegisterVariableString(
            self::IDENT_LAST_FAULT_SOURCE,
            $this->Translate('Last fault source'),
            $this->TextPresentation(),
            73
        );
        $lastFaultTimeCreated = $this->RegisterVariableString(
            self::IDENT_LAST_FAULT_TIME,
            $this->Translate('Last fault time'),
            $this->TextPresentation(),
            74
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
        if ($alarmOutputActiveCreated) {
            $this->SetValue(self::IDENT_ALARM_OUTPUT_ACTIVE, false);
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
        if ($systemFaultCreated) {
            $this->SetSystemFault(false);
        }
        if ($activeFaultsCreated) {
            $this->SetActiveFaults('');
        }
        if ($blockingFaultsCreated) {
            $this->SetBlockingFaults('');
        }
        if ($lastFaultSourceCreated) {
            $this->SetLastFaultSource('');
        }
        if ($lastFaultTimeCreated) {
            $this->SetLastFaultTime('');
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
        $faultInputs = $this->ReadConfiguredFaultInputs();
        $this->NormalizeSensorBypasses($sensors);
        $this->SynchronizeSensorMessages($sensors, $faultInputs);
        $this->EvaluateFaultInputs($faultInputs);
        $this->UpdateReadinessFromSensors($sensors);
        $this->EvaluateAlwaysActiveSensors($sensors);
        $this->RestoreDelayTimers();
        $this->EvaluateArmedSensorsAfterApplyChanges($sensors);
        $this->RestoreAlarmDurationTimer();
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
            $this->PopulateConfigurationListValues($form['elements']);
        }

        return json_encode(
            $form,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Returns the complete user-facing control state for visualizations and other
     * clients. The JSON payload is intentionally independent from variable
     * presentations so a client does not need to reproduce alarm logic.
     */
    public function GetControlState(): string
    {
        $sensors = $this->ReadConfiguredSensors();
        $faultInputs = $this->ReadConfiguredFaultInputs();
        $sensorReadiness = $this->EvaluateReadinessStatus($sensors);
        $readiness = $this->ApplyFaultBlockingToReadiness($sensorReadiness['readiness'], $faultInputs);
        $state = $this->ReadAlarmState();
        $mode = $this->ReadAlarmMode();
        $isDisarmed = $state === self::STATE_DISARMED;
        $alarmMemory = $this->GetValue(self::IDENT_ALARM_MEMORY) === true;
        $alarmOutputActive = $this->GetValue(self::IDENT_ALARM_OUTPUT_ACTIVE) === true;

        $payload = [
            'ApiVersion' => self::CONTROL_API_VERSION,
            'Mode'       => [
                'Value' => $mode,
                'Name'  => $this->ControlModeName($mode)
            ],
            'State'      => [
                'Value' => $state,
                'Name'  => $this->ControlStateName($state)
            ],
            'Capabilities' => [
                'CodeRequired'        => trim($this->ReadPropertyString(self::PROPERTY_DISARM_CODE)) !== '',
                'CanDisarm'           => !$isDisarmed || $mode !== self::MODE_NONE,
                'CanManageBypasses'   => $isDisarmed,
                'CanResetAlarmOutput' => $state === self::STATE_ALARM && $alarmOutputActive,
                'CanClearAlarmMemory' => $alarmMemory && $state !== self::STATE_ALARM
            ],
            'Modes' => [
                'home'  => $this->BuildControlModeStatus(self::MODE_HOME, $readiness['home'], $isDisarmed, $sensors, $faultInputs),
                'away'  => $this->BuildControlModeStatus(self::MODE_AWAY, $readiness['away'], $isDisarmed, $sensors, $faultInputs),
                'night' => $this->BuildControlModeStatus(self::MODE_NIGHT, $readiness['night'], $isDisarmed, $sensors, $faultInputs)
            ],
            'Delay' => [
                'Remaining' => max(0, (int) $this->GetValue(self::IDENT_DELAY_REMAINING)),
                'Source'    => (string) $this->GetValue(self::IDENT_DELAY_SOURCE)
            ],
            'Alarm' => [
                'OutputActive' => $alarmOutputActive,
                'MemoryActive' => $alarmMemory,
                'LastSource'   => (string) $this->GetValue(self::IDENT_LAST_ALARM_SOURCE),
                'LastTime'     => (string) $this->GetValue(self::IDENT_LAST_ALARM_TIME)
            ],
            'Faults' => [
                'Active'      => $this->GetValue(self::IDENT_SYSTEM_FAULT) === true,
                'Items'       => $this->BuildControlFaultDetails($faultInputs, false),
                'Blocking'    => $this->BuildControlFaultDetails($faultInputs, true),
                'LastSource'  => (string) $this->GetValue(self::IDENT_LAST_FAULT_SOURCE),
                'LastTime'    => (string) $this->GetValue(self::IDENT_LAST_FAULT_TIME)
            ],
            'BypassedSensors' => $this->BuildControlBypassedSensorDetails($sensors)
        ];

        return json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Arms one user-facing mode through the stable control API.
     *
     * Supported mode names are home, away and night. Invalid names are rejected
     * without changing the current alarm state.
     */
    public function Arm(string $mode): bool
    {
        $modeValue = match (strtolower(trim($mode))) {
            'home'  => self::MODE_HOME,
            'away'  => self::MODE_AWAY,
            'night' => self::MODE_NIGHT,
            default => null
        };

        if ($modeValue === null) {
            return false;
        }

        return $this->ArmMode($modeValue);
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
        $faultInputs = $this->ReadConfiguredFaultInputs();
        $isSensorVariable = $this->IsMonitoredSensorVariable($SenderID, $sensors);
        $isFaultVariable = $this->IsMonitoredFaultVariable($SenderID, $faultInputs);
        if (!$isSensorVariable && !$isFaultVariable) {
            return;
        }

        if ($isFaultVariable) {
            $this->EvaluateFaultInputs($faultInputs);
        }
        $this->UpdateReadinessFromSensors($sensors);
        if ($this->ReadAlarmState() === self::STATE_ALARM || !$isSensorVariable) {
            return;
        }
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
        return $this->Arm('home');
    }

    /**
     * Arms the system in Away mode when every sensor assigned to Away is ready.
     */
    public function ArmAway(): bool
    {
        return $this->Arm('away');
    }

    /**
     * Arms the system in Night mode when every sensor assigned to Night is ready.
     */
    public function ArmNight(): bool
    {
        return $this->Arm('night');
    }

    /**
     * Disarms the system and clears the selected arming mode.
     */
    public function Disarm(): bool
    {
        $previousState = $this->ReadAlarmState();
        $previousMode = $this->ReadAlarmMode();
        $wasAlarm = $previousState === self::STATE_ALARM;
        $hadActiveState = $previousState !== self::STATE_DISARMED || $previousMode !== self::MODE_NONE;

        $this->CancelDelayTimers();
        if ($wasAlarm) {
            $this->ResetAlarmOutput();
        } else {
            $this->StopAlarmDurationTimer();
            $this->WriteAttributeInteger(self::ATTRIBUTE_ALARM_OUTPUT_ACTIVE, 0);
            if ($this->GetValue(self::IDENT_ALARM_OUTPUT_ACTIVE) === true) {
                $this->SetValue(self::IDENT_ALARM_OUTPUT_ACTIVE, false);
            }
        }
        $this->SetAlarmState(self::STATE_DISARMED);
        $this->SetAlarmMode(self::MODE_NONE);
        $this->ClearSensorBypassesInternal();

        if ($wasAlarm) {
            $this->RunConfiguredAction(self::PROPERTY_DISARM_AFTER_ALARM_ACTION);
        }
        if ($hadActiveState) {
            $this->AppendEvent(self::EVENT_DISARMED);
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
            $this->AppendEvent(self::EVENT_DISARM_CODE_REJECTED);

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
        $this->AppendEvent(
            self::EVENT_SENSOR_BYPASSED,
            $this->ResolveSensorNameByVariableID($variableID, $sensors)
        );

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
        $this->AppendEvent(
            self::EVENT_SENSOR_BYPASS_REMOVED,
            $this->ResolveSensorNameByVariableID($variableID, $sensors)
        );

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

        $hadBypasses = $this->ReadBypassedSensorIDs() !== [];
        $this->ClearSensorBypassesInternal();
        if ($hadBypasses) {
            $this->AppendEvent(self::EVENT_SENSOR_BYPASSES_CLEARED);
        }

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

        $hadAlarmMemory = $this->GetValue(self::IDENT_ALARM_MEMORY) === true;
        $this->SetAlarmMemory(false);
        $this->SetLastAlarmSource('');
        $this->SetLastAlarmTime('');
        if ($hadAlarmMemory) {
            $this->AppendEvent(self::EVENT_ALARM_MEMORY_CLEARED);
        }

        return true;
    }

    /**
     * Returns the persistent security-event history as JSON, newest event first.
     */
    public function GetEventHistory(): string
    {
        return json_encode(
            $this->ReadEventHistory(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Clears the persistent security-event history.
     */
    public function ClearEventHistory(): bool
    {
        $this->WriteAttributeString(self::ATTRIBUTE_EVENT_HISTORY, '[]');

        return true;
    }

    /**
     * Stops the active alarm output without disarming the system.
     *
     * The Alarm state and alarm memory remain latched until the user disarms or
     * acknowledges them separately. The configured reset action is executed at
     * most once per alarm cycle.
     */
    public function ResetAlarmOutput(): bool
    {
        if (
            $this->ReadAlarmState() !== self::STATE_ALARM
            || $this->ReadAttributeInteger(self::ATTRIBUTE_ALARM_OUTPUT_ACTIVE) !== 1
        ) {
            return false;
        }

        $this->StopAlarmDurationTimer();
        $actionSucceeded = $this->RunConfiguredAction(self::PROPERTY_ALARM_RESET_ACTION);
        $this->SetAlarmOutputActive(false);
        $this->AppendEvent(self::EVENT_ALARM_OUTPUT_RESET);

        return $actionSucceeded;
    }

    /**
     * Completes the configured alarm duration and resets only the alarm output.
     */
    public function CompleteAlarmDuration(): void
    {
        if (
            $this->ReadAlarmState() !== self::STATE_ALARM
            || $this->ReadAttributeInteger(self::ATTRIBUTE_ALARM_OUTPUT_ACTIVE) !== 1
        ) {
            $this->StopAlarmDurationTimer();
            if (
                $this->ReadAttributeInteger(self::ATTRIBUTE_ALARM_OUTPUT_ACTIVE) !== 0
                || $this->GetValue(self::IDENT_ALARM_OUTPUT_ACTIVE) === true
            ) {
                $this->SetAlarmOutputActive(false);
            }

            return;
        }

        $this->ResetAlarmOutput();
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
        $this->UpdateReadinessFromSensors($sensors);
        $faultInputs = $this->ReadConfiguredFaultInputs();
        $strictReadiness = $this->ApplyFaultBlockingToReadiness(
            $this->EvaluateReadinessStatus($sensors, true)['readiness'],
            $faultInputs
        );
        if (!$this->IsModeReady($mode, $strictReadiness)) {
            $this->AppendEvent(
                self::EVENT_ARM_CANCELLED,
                $this->ResolveArmingBlockersForMode($mode, $sensors, $faultInputs, true),
                $mode
            );
            $this->Disarm();

            return;
        }

        $this->ClearDelayStatus();
        $this->SetAlarmState(self::STATE_ARMED);
        $this->AppendEvent(self::EVENT_ARMED);
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
                'name'    => 'ExitDelay',
                'caption' => $this->Translate('Exit route')
            ],
            [
                'type'    => 'CheckBox',
                'name'    => 'EntryDelay',
                'caption' => $this->Translate('Entry delay')
            ],
            [
                'type'    => 'Label',
                'caption' => $this->Translate('Exit-route sensors may be open when arming starts if an exit delay is configured, but must be ready when the countdown ends.')
            ],
            [
                'type'    => 'Label',
                'caption' => $this->Translate('24/7 sensors trigger immediately in every system state; mode assignments and entry/exit delay are ignored.')
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
     * Builds the individual editor for one 24/7 fault or tamper input.
     *
     * Fault inputs are independent from arming-mode sensor assignments. Their
     * trigger value is selected from the underlying Symcon variable presentation
     * in exactly the same way as normal alarm sensors.
     *
     * @param mixed $faultInput Current list row supplied by the Symcon configuration form.
     *
     * @return list<array<string,mixed>>
     */
    public function GetFaultInputEditForm(mixed $faultInput): array
    {
        $variableID = $this->ReadSensorEditInteger($faultInput, 'VariableID', 0);
        $triggerValue = $this->ReadSensorEditString($faultInput, 'TriggerValue', '1');
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
                'onChange' => 'OHA_UpdateFaultTriggerValueForm($id, $VariableID, $TriggerValue);'
            ],
            [
                'type'    => 'Select',
                'name'    => 'FaultType',
                'caption' => $this->Translate('Fault type'),
                'options' => $this->CreateFaultTypeOptions()
            ],
            [
                'type'    => 'ValidationTextBox',
                'name'    => 'TriggerValue',
                'visible' => false
            ],
            [
                'type'     => 'Select',
                'name'     => 'TriggerValueSelection',
                'caption'  => $this->Translate('Fault value'),
                'options'  => $hasTriggerOptions ? $triggerOptions : $this->CreateEmptyTriggerValueOptions(),
                'value'    => $selectedTriggerValue,
                'visible'  => $hasVariable && $hasTriggerOptions,
                'onChange' => 'OHA_SetFaultTriggerValue($id, $TriggerValueSelection);'
            ],
            [
                'type'     => 'ValidationTextBox',
                'name'     => 'TriggerValueManual',
                'caption'  => $this->Translate('Fault value'),
                'value'    => $triggerValue,
                'visible'  => $hasVariable && !$hasTriggerOptions,
                'onChange' => 'OHA_SetFaultTriggerValue($id, $TriggerValueManual);'
            ],
            [
                'type'    => 'Label',
                'name'    => 'TriggerValueHint',
                'caption' => $this->Translate('Select a variable to choose its fault value.'),
                'visible' => !$hasVariable
            ],
            [
                'type'    => 'Label',
                'name'    => 'TriggerValueManualHint',
                'caption' => $this->Translate('This variable has no selectable states. Enter the raw fault value.'),
                'visible' => $hasVariable && !$hasTriggerOptions
            ],
            [
                'type'    => 'CheckBox',
                'name'    => 'BlockArming',
                'caption' => $this->Translate('Block arming')
            ],
            [
                'type'    => 'CheckBox',
                'name'    => 'TriggerAlarm',
                'caption' => $this->Translate('Trigger alarm (24/7)')
            ],
            [
                'type'    => 'Label',
                'caption' => $this->Translate('Fault inputs are monitored continuously. Alarm triggering is immediate and independent of the selected arming mode.')
            ]
        ];
    }

    /**
     * Rebuilds the fault-value choices when another Symcon variable is chosen.
     */
    public function UpdateFaultTriggerValueForm(int $variableID, string $triggerValue): void
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
     * Copies the visible fault-value editor into the persisted TriggerValue field.
     */
    public function SetFaultTriggerValue(string $triggerValue): void
    {
        $this->UpdateFormField('TriggerValue', 'value', $triggerValue);
    }

    /**
     * Injects non-persistent edit-helper values into Lists at any nesting level.
     *
     * @param list<array<string,mixed>> $elements
     */
    private function PopulateConfigurationListValues(array &$elements): void
    {
        foreach ($elements as &$element) {
            if (!is_array($element)) {
                continue;
            }

            if (($element['type'] ?? null) === 'List') {
                if (($element['name'] ?? null) === self::PROPERTY_SENSORS) {
                    $element['values'] = $this->CreateSensorListFormValues();
                } elseif (($element['name'] ?? null) === self::PROPERTY_FAULT_INPUTS) {
                    $element['values'] = $this->CreateFaultListFormValues();
                }
            }

            if (isset($element['items']) && is_array($element['items'])) {
                $this->PopulateConfigurationListValues($element['items']);
            }
        }
        unset($element);
    }

    private function ControlModeName(int $mode): string
    {
        return match ($mode) {
            self::MODE_NONE  => 'none',
            self::MODE_HOME  => 'home',
            self::MODE_AWAY  => 'away',
            self::MODE_NIGHT => 'night',
            default          => 'unknown'
        };
    }

    private function ControlStateName(int $state): string
    {
        return match ($state) {
            self::STATE_DISARMED    => 'disarmed',
            self::STATE_EXIT_DELAY  => 'exit_delay',
            self::STATE_ARMED       => 'armed',
            self::STATE_ENTRY_DELAY => 'entry_delay',
            self::STATE_ALARM       => 'alarm',
            default                 => 'unknown'
        };
    }

    /**
     * @param list<array<string,mixed>> $sensors
     * @param list<array<string,mixed>> $faultInputs
     *
     * @return array{Value:int,Ready:bool,CanArm:bool,Blockers:list<array<string,mixed>>}
     */
    private function BuildControlModeStatus(
        int $mode,
        bool $ready,
        bool $isDisarmed,
        array $sensors,
        array $faultInputs
    ): array {
        return [
            'Value'    => $mode,
            'Ready'    => $ready,
            'CanArm'   => $isDisarmed && $ready,
            'Blockers' => array_merge(
                $this->BuildControlSensorBlockerDetails($mode, $sensors),
                $this->BuildControlFaultDetails($faultInputs, true)
            )
        ];
    }

    /**
     * @param list<array<string,mixed>> $sensors
     *
     * @return list<array{Kind:string,VariableID:int,Name:string,Reason:string,Bypassable:bool}>
     */
    private function BuildControlSensorBlockerDetails(int $mode, array $sensors): array
    {
        $allowActiveExitRoute = $this->ReadDelaySeconds(self::PROPERTY_EXIT_DELAY_SECONDS) > 0;
        $details = [];

        foreach ($sensors as $sensor) {
            if (!$sensor['Enabled'] || !$this->IsSensorMonitored($sensor) || $this->IsSensorBypassed($sensor)) {
                continue;
            }
            if ($sensor['VariableID'] <= 0) {
                continue;
            }
            if (!$sensor['AlwaysActive'] && !$this->IsSensorRelevantForMode($sensor, $mode)) {
                continue;
            }

            $triggerState = $this->IsExistingVariable($sensor['VariableID'])
                ? $this->GetSensorTriggerState($sensor)
                : null;
            if ($triggerState === false) {
                continue;
            }
            if (
                $allowActiveExitRoute
                && !$sensor['AlwaysActive']
                && $sensor['ExitDelay']
                && $triggerState === true
            ) {
                continue;
            }

            $details[] = [
                'Kind'       => 'sensor',
                'VariableID' => $sensor['VariableID'],
                'Name'       => $this->ResolveSensorDisplayName($sensor),
                'Reason'     => $triggerState === true ? 'triggered' : 'unavailable',
                'Bypassable' => !$sensor['AlwaysActive'] && $this->IsSensorUsedForArming($sensor)
            ];
        }

        return $details;
    }

    /**
     * @param list<array<string,mixed>> $faultInputs
     *
     * @return list<array{Kind:string,VariableID:int,Name:string,FaultType:int,Reason:string,BlockArming:bool,TriggerAlarm:bool,Bypassable:bool}>
     */
    private function BuildControlFaultDetails(array $faultInputs, bool $blockingOnly): array
    {
        $details = [];
        foreach ($faultInputs as $faultInput) {
            if (!$faultInput['Enabled'] || $faultInput['VariableID'] <= 0) {
                continue;
            }
            if ($blockingOnly && !$faultInput['BlockArming']) {
                continue;
            }

            $triggerState = $this->GetFaultTriggerState($faultInput);
            if ($triggerState === false) {
                continue;
            }

            $details[] = [
                'Kind'         => 'fault',
                'VariableID'   => $faultInput['VariableID'],
                'Name'         => $this->ResolveFaultDisplayName($faultInput),
                'FaultType'    => $faultInput['FaultType'],
                'Reason'       => $triggerState === true ? 'active' : 'unavailable',
                'BlockArming'  => $faultInput['BlockArming'],
                'TriggerAlarm' => $faultInput['TriggerAlarm'],
                'Bypassable'   => false
            ];
        }

        return $details;
    }

    /**
     * @param list<array<string,mixed>> $sensors
     *
     * @return list<array{VariableID:int,Name:string}>
     */
    private function BuildControlBypassedSensorDetails(array $sensors): array
    {
        $bypassedIDs = $this->ReadBypassedSensorIDs();
        $details = [];
        foreach ($bypassedIDs as $variableID) {
            $details[] = [
                'VariableID' => $variableID,
                'Name'       => $this->ResolveSensorNameByVariableID($variableID, $sensors)
            ];
        }

        return $details;
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
     * @return list<array{caption:string,value:int}>
     */
    private function CreateFaultTypeOptions(): array
    {
        return [
            ['caption' => $this->Translate('Tamper'), 'value' => self::FAULT_TYPE_TAMPER],
            ['caption' => $this->Translate('Battery / power'), 'value' => self::FAULT_TYPE_POWER],
            ['caption' => $this->Translate('Communication'), 'value' => self::FAULT_TYPE_COMMUNICATION],
            ['caption' => $this->Translate('Device fault'), 'value' => self::FAULT_TYPE_DEVICE],
            ['caption' => $this->Translate('Other fault'), 'value' => self::FAULT_TYPE_OTHER]
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
     * Supplies the non-persistent fault-value editor fields with the stored fault value.
     *
     * @return list<array{TriggerValueSelection:string,TriggerValueManual:string}>
     */
    private function CreateFaultListFormValues(): array
    {
        try {
            $rows = json_decode($this->ReadPropertyString(self::PROPERTY_FAULT_INPUTS), true, 512, JSON_THROW_ON_ERROR);
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
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * }>
     */
    private function ReadConfiguredFaultInputs(): array
    {
        $encodedFaultInputs = trim($this->ReadPropertyString(self::PROPERTY_FAULT_INPUTS));
        if ($encodedFaultInputs === '') {
            return [];
        }

        try {
            $faultInputs = json_decode($encodedFaultInputs, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Invalid fault input configuration JSON.', 0, $exception);
        }

        if (!is_array($faultInputs) || !array_is_list($faultInputs)) {
            throw new UnexpectedValueException('Fault input configuration must be a list.');
        }

        $normalizedFaultInputs = [];
        $usedVariableIDs = [];
        foreach ($faultInputs as $faultInput) {
            if (!is_array($faultInput)) {
                throw new UnexpectedValueException('Every fault input configuration must be an object.');
            }

            $normalized = $this->NormalizeFaultInput($faultInput);
            if ($normalized['VariableID'] > 0) {
                if (isset($usedVariableIDs[$normalized['VariableID']])) {
                    throw new UnexpectedValueException('A fault input variable can only be configured once.');
                }
                $usedVariableIDs[$normalized['VariableID']] = true;
            }
            $normalizedFaultInputs[] = $normalized;
        }

        return $normalizedFaultInputs;
    }

    /**
     * @param array<string,mixed> $faultInput
     *
     * @return array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * }
     */
    private function NormalizeFaultInput(array $faultInput): array
    {
        $variableID = $this->ReadFaultInteger($faultInput, 'VariableID', 0);
        if ($variableID < 0) {
            throw new UnexpectedValueException('Fault input VariableID must not be negative.');
        }

        $faultType = $this->ReadFaultInteger($faultInput, 'FaultType', self::FAULT_TYPE_TAMPER);
        if (!in_array($faultType, self::VALID_FAULT_TYPES, true)) {
            throw new UnexpectedValueException('Unsupported fault input type.');
        }

        return [
            'Enabled'      => $this->ReadFaultBoolean($faultInput, 'Enabled', true),
            'Name'         => trim($this->ReadFaultString($faultInput, 'Name', '')),
            'VariableID'   => $variableID,
            'FaultType'    => $faultType,
            'TriggerValue' => $this->ReadFaultString($faultInput, 'TriggerValue', '1'),
            'BlockArming'  => $this->ReadFaultBoolean($faultInput, 'BlockArming', false),
            'TriggerAlarm' => $this->ReadFaultBoolean($faultInput, 'TriggerAlarm', false)
        ];
    }

    /**
     * @param array<string,mixed> $faultInput
     */
    private function ReadFaultBoolean(array $faultInput, string $key, bool $default): bool
    {
        $value = $faultInput[$key] ?? $default;
        if (!is_bool($value)) {
            throw new UnexpectedValueException(sprintf('Fault input field %s must be boolean.', $key));
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $faultInput
     */
    private function ReadFaultInteger(array $faultInput, string $key, int $default): int
    {
        $value = $faultInput[$key] ?? $default;
        if (!is_int($value)) {
            throw new UnexpectedValueException(sprintf('Fault input field %s must be integer.', $key));
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $faultInput
     */
    private function ReadFaultString(array $faultInput, string $key, string $default): string
    {
        $value = $faultInput[$key] ?? $default;
        if (!is_string($value)) {
            throw new UnexpectedValueException(sprintf('Fault input field %s must be string.', $key));
        }

        return $value;
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
            'ExitDelay'    => $this->ReadSensorBoolean($sensor, 'ExitDelay', false),
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
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function SynchronizeSensorMessages(array $sensors, array $faultInputs = []): void
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
        foreach ($faultInputs as $faultInput) {
            if (!$faultInput['Enabled']) {
                continue;
            }

            $variableID = $faultInput['VariableID'];
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
     *     ExitDelay: bool,
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
     * @param list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * }> $faultInputs
     */
    private function IsMonitoredFaultVariable(int $variableID, array $faultInputs): bool
    {
        foreach ($faultInputs as $faultInput) {
            if ($faultInput['Enabled'] && $faultInput['VariableID'] === $variableID) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reads the current state of one fault input.
     *
     * True means the configured fault value is active, false means healthy and
     * null means that the configured variable is missing or cannot be evaluated.
     *
     * @param array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * } $faultInput
     */
    private function GetFaultTriggerState(array $faultInput): ?bool
    {
        $variableID = $faultInput['VariableID'];
        if ($variableID <= 0 || !$this->IsExistingVariable($variableID)) {
            return null;
        }

        $variable = $this->GetSymconVariable($variableID);
        if ($variable === null || !isset($variable['VariableType']) || !is_int($variable['VariableType'])) {
            return null;
        }

        try {
            $currentValue = GetValue($variableID);
        } catch (\Throwable) {
            return null;
        }

        return $this->TriggerValueMatchesCurrentValue(
            $variable['VariableType'],
            $faultInput['TriggerValue'],
            $currentValue
        );
    }

    /**
     * Returns a user-facing name for a fault input.
     *
     * @param array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * } $faultInput
     */
    private function ResolveFaultDisplayName(array $faultInput): string
    {
        if ($faultInput['Name'] !== '') {
            return $faultInput['Name'];
        }

        return sprintf($this->Translate('Variable #%d'), $faultInput['VariableID']);
    }

    /**
     * @param list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * }> $faultInputs
     */
    private function ResolveFaultNameByVariableID(int $variableID, array $faultInputs): string
    {
        foreach ($faultInputs as $faultInput) {
            if ($faultInput['VariableID'] === $variableID) {
                return $this->ResolveFaultDisplayName($faultInput);
            }
        }

        return sprintf($this->Translate('Variable #%d'), $variableID);
    }

    /**
     * @return list<int>
     */
    private function ReadActiveFaultVariableIDs(): array
    {
        $encodedIDs = $this->ReadAttributeString(self::ATTRIBUTE_ACTIVE_FAULT_VARIABLE_IDS);

        try {
            $decodedIDs = json_decode($encodedIDs, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($decodedIDs) || !array_is_list($decodedIDs)) {
            return [];
        }

        $variableIDs = [];
        foreach ($decodedIDs as $variableID) {
            if (is_int($variableID) && $variableID > 0) {
                $variableIDs[$variableID] = true;
            }
        }

        $result = array_map('intval', array_keys($variableIDs));
        sort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * @param list<int> $variableIDs
     */
    private function WriteActiveFaultVariableIDs(array $variableIDs): void
    {
        $normalized = [];
        foreach ($variableIDs as $variableID) {
            if ($variableID > 0) {
                $normalized[$variableID] = true;
            }
        }

        $result = array_map('intval', array_keys($normalized));
        sort($result, SORT_NUMERIC);

        $this->WriteAttributeString(
            self::ATTRIBUTE_ACTIVE_FAULT_VARIABLE_IDS,
            json_encode($result, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Re-evaluates all configured 24/7 fault and tamper inputs.
     *
     * A missing or unreadable configured variable is treated as an active system
     * fault for status and optional arming blocking. It does not trigger the main
     * Alarm state unless the configured fault value can positively be evaluated.
     * Transition tracking is persisted so ApplyChanges or a restart does not emit
     * duplicate fault events for an already active condition.
     *
     * @param list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * }> $faultInputs
     */
    private function EvaluateFaultInputs(array $faultInputs): void
    {
        $previousActiveIDs = $this->ReadActiveFaultVariableIDs();
        if ($faultInputs === [] && $previousActiveIDs === []) {
            return;
        }

        $currentActiveIDs = [];
        $activeNames = [];
        $newlyActiveInputs = [];

        foreach ($faultInputs as $faultInput) {
            if (!$faultInput['Enabled'] || $faultInput['VariableID'] <= 0) {
                continue;
            }

            $triggerState = $this->GetFaultTriggerState($faultInput);
            if ($triggerState === false) {
                continue;
            }

            $variableID = $faultInput['VariableID'];
            $currentActiveIDs[] = $variableID;
            $activeNames[] = $this->ResolveFaultDisplayName($faultInput);
            if (!in_array($variableID, $previousActiveIDs, true)) {
                $newlyActiveInputs[] = [$faultInput, $triggerState];
            }
        }

        $currentActiveIDs = array_values(array_unique($currentActiveIDs));
        sort($currentActiveIDs, SORT_NUMERIC);
        $activeNames = array_values(array_unique($activeNames));
        $clearedIDs = array_values(array_diff($previousActiveIDs, $currentActiveIDs));

        $this->WriteActiveFaultVariableIDs($currentActiveIDs);
        $this->SetSystemFault($currentActiveIDs !== []);
        $this->SetActiveFaults(implode(', ', $activeNames));
        $this->SetBlockingFaults(implode(', ', $this->ResolveBlockingFaultNames($faultInputs)));

        foreach ($newlyActiveInputs as [$faultInput, $triggerState]) {
            $sourceName = $this->ResolveFaultDisplayName($faultInput);
            $this->SetLastFaultSource($sourceName);
            $this->SetLastFaultTime(date('d.m.Y H:i:s'));
            $this->AppendEvent(self::EVENT_FAULT_ACTIVATED, $sourceName);
            $this->RunConfiguredAction(self::PROPERTY_FAULT_ACTION);

            if ($triggerState === true && $faultInput['TriggerAlarm']) {
                $this->EnterAlarmState(null, $faultInput['VariableID'], $sourceName);
            }
        }

        foreach ($clearedIDs as $variableID) {
            $sourceName = $this->ResolveFaultNameByVariableID($variableID, $faultInputs);
            $this->AppendEvent(self::EVENT_FAULT_CLEARED, $sourceName);
            $this->RunConfiguredAction(self::PROPERTY_FAULT_CLEARED_ACTION);
        }
    }

    /**
     * @param list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * }> $faultInputs
     *
     * @return list<string>
     */
    private function ResolveBlockingFaultNames(array $faultInputs): array
    {
        $blockingFaults = [];
        foreach ($faultInputs as $faultInput) {
            if (
                !$faultInput['Enabled']
                || !$faultInput['BlockArming']
                || $faultInput['VariableID'] <= 0
            ) {
                continue;
            }

            if ($this->GetFaultTriggerState($faultInput) === false) {
                continue;
            }

            $blockingFaults[] = $this->ResolveFaultDisplayName($faultInput);
        }

        return array_values(array_unique($blockingFaults));
    }

    /**
     * @param array{global:bool,home:bool,away:bool,night:bool} $readiness
     * @param list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * }> $faultInputs
     *
     * @return array{global:bool,home:bool,away:bool,night:bool}
     */
    private function ApplyFaultBlockingToReadiness(array $readiness, array $faultInputs): array
    {
        if ($this->ResolveBlockingFaultNames($faultInputs) === []) {
            return $readiness;
        }

        return [
            'global' => false,
            'home'   => false,
            'away'   => false,
            'night'  => false
        ];
    }

    /**
     * Evaluates and publishes the global and mode-specific arming readiness.
     *
     * ReadyToArm remains the global summary for starting an arming cycle.
     * ReadyHome, ReadyAway and ReadyNight only consider sensors assigned to the
     * respective mode plus every 24/7 sensor. When a positive
     * exit delay is configured, sensors marked as exit route may still be active
     * while arming is initiated; they must be ready when the countdown ends. The
     * matching blocking-sensor variables expose the concrete sensor names for
     * diagnostics and the later visualization.
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
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }> $sensors
     *
     * @return array{global:bool,home:bool,away:bool,night:bool}
     */
    private function UpdateReadinessFromSensors(array $sensors): array
    {
        $status = $this->EvaluateReadinessStatus($sensors);
        $faultInputs = $this->ReadConfiguredFaultInputs();
        $readiness = $this->ApplyFaultBlockingToReadiness($status['readiness'], $faultInputs);

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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
    private function EvaluateReadinessStatus(array $sensors, bool $strict = false): array
    {
        $allowActiveExitRoute = !$strict && $this->ReadDelaySeconds(self::PROPERTY_EXIT_DELAY_SECONDS) > 0;
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

            $triggerState = $this->IsExistingVariable($variableID)
                ? $this->GetSensorTriggerState($sensor)
                : null;

            if ($triggerState === false) {
                continue;
            }

            if (
                $allowActiveExitRoute
                && !$sensor['AlwaysActive']
                && $sensor['ExitDelay']
                && $triggerState === true
            ) {
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function ResolveSensorNameByVariableID(int $variableID, array $sensors): string
    {
        foreach ($sensors as $sensor) {
            if ($sensor['VariableID'] === $variableID) {
                return $this->ResolveSensorDisplayName($sensor);
            }
        }

        return sprintf($this->Translate('Variable #%d'), $variableID);
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
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function ResolveBlockingSensorsForMode(int $mode, array $sensors, bool $strict = false): string
    {
        $status = $this->EvaluateReadinessStatus($sensors, $strict);
        $blockingSensors = match ($mode) {
            self::MODE_HOME  => $status['blockingHome'],
            self::MODE_AWAY  => $status['blockingAway'],
            self::MODE_NIGHT => $status['blockingNight'],
            default          => throw new InvalidArgumentException('Unsupported arming target mode.')
        };

        return implode(', ', $blockingSensors);
    }

    /**
     * Combines mode-specific sensor blockers with active system faults that are
     * configured to block arming.
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
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }> $sensors
     * @param list<array{
     *     Enabled: bool,
     *     Name: string,
     *     VariableID: int,
     *     FaultType: int,
     *     TriggerValue: string,
     *     BlockArming: bool,
     *     TriggerAlarm: bool
     * }> $faultInputs
     */
    private function ResolveArmingBlockersForMode(
        int $mode,
        array $sensors,
        array $faultInputs,
        bool $strict = false
    ): string {
        $blockers = [];
        $sensorBlockers = $this->ResolveBlockingSensorsForMode($mode, $sensors, $strict);
        if ($sensorBlockers !== '') {
            $blockers[] = $sensorBlockers;
        }

        $faultBlockers = $this->ResolveBlockingFaultNames($faultInputs);
        if ($faultBlockers !== []) {
            $blockers[] = implode(', ', $faultBlockers);
        }

        return implode(', ', $blockers);
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
        if ($this->ReadAlarmState() !== self::STATE_DISARMED) {
            return false;
        }

        $sensors = $this->ReadConfiguredSensors();
        $faultInputs = $this->ReadConfiguredFaultInputs();
        $readiness = $this->UpdateReadinessFromSensors($sensors);

        if (!$this->IsModeReady($mode, $readiness)) {
            $this->AppendEvent(
                self::EVENT_ARM_REJECTED,
                $this->ResolveArmingBlockersForMode($mode, $sensors, $faultInputs),
                $mode
            );

            return false;
        }

        $this->CancelDelayTimers();
        $this->SetAlarmMode($mode);

        $exitDelaySeconds = $this->ReadDelaySeconds(self::PROPERTY_EXIT_DELAY_SECONDS);
        if ($exitDelaySeconds === 0) {
            $this->SetAlarmState(self::STATE_ARMED);
            $this->AppendEvent(self::EVENT_ARMED);

            return true;
        }

        $this->SetAlarmState(self::STATE_EXIT_DELAY);
        $this->StartDelayTimer(
            self::TIMER_EXIT_DELAY,
            self::ATTRIBUTE_EXIT_DELAY_DEADLINE,
            $exitDelaySeconds
        );
        $this->AppendEvent(self::EVENT_EXIT_DELAY_STARTED);

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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
     * Re-evaluates armed sensors after ApplyChanges or a Symcon restart.
     *
     * A sensor may have changed while the module was not running. In that case no
     * VM_UPDATE is delivered after startup because the variable already contains
     * its new value. Replaying the current state through the regular armed-sensor
     * handler closes this gap without introducing a second alarm path.
     *
     * Existing entry-delay state is preserved. A currently active immediate sensor
     * still escalates such a restored countdown to Alarm, while a newly detected
     * delayed sensor starts the configured entry delay from the current time.
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
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }> $sensors
     */
    private function EvaluateArmedSensorsAfterApplyChanges(array $sensors): void
    {
        if (!in_array($this->ReadAlarmState(), [self::STATE_ARMED, self::STATE_ENTRY_DELAY], true)) {
            return;
        }

        $variableIDs = [];
        foreach ($sensors as $sensor) {
            if (
                !$sensor['Enabled']
                || $sensor['AlwaysActive']
                || $sensor['VariableID'] <= 0
                || !$this->IsSensorUsedForArming($sensor)
            ) {
                continue;
            }

            $variableIDs[$sensor['VariableID']] = true;
        }

        foreach (array_keys($variableIDs) as $variableID) {
            $this->HandleSensorUpdateWhileArmed((int) $variableID, $sensors);
            if ($this->ReadAlarmState() === self::STATE_ALARM) {
                return;
            }
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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
        $this->AppendEvent(
            self::EVENT_ENTRY_DELAY_STARTED,
            $this->ResolveSensorDisplayName($entryDelaySensor)
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
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }|null $sourceSensor
     */
    private function EnterAlarmState(
        ?array $sourceSensor = null,
        int $fallbackVariableID = 0,
        string $explicitSourceName = ''
    ): void {
        if ($this->ReadAlarmState() === self::STATE_ALARM) {
            return;
        }

        $this->RememberAlarm($sourceSensor, $fallbackVariableID, $explicitSourceName);
        $this->CancelDelayTimers();
        $this->SetAlarmState(self::STATE_ALARM);
        $this->SetAlarmOutputActive(true);
        $this->StartAlarmDurationTimer();
        $this->AppendEvent(
            self::EVENT_ALARM,
            $this->ResolveAlarmEventSource($sourceSensor, $fallbackVariableID, $explicitSourceName)
        );
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
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }|null $sourceSensor
     */
    private function RememberAlarm(
        ?array $sourceSensor,
        int $fallbackVariableID,
        string $explicitSourceName = ''
    ): void {
        $timestamp = time();
        $sourceName = $this->ResolveAlarmEventSource($sourceSensor, $fallbackVariableID, $explicitSourceName);

        $this->SetAlarmMemory(true);
        $this->SetLastAlarmSource($sourceName);
        $this->SetLastAlarmTime(date('d.m.Y H:i:s', $timestamp));
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
     *     ExitDelay: bool,
     *     EntryDelay: bool
     * }|null $sourceSensor
     */
    private function ResolveAlarmEventSource(
        ?array $sourceSensor,
        int $fallbackVariableID,
        string $explicitSourceName = ''
    ): string {
        $sourceName = trim($explicitSourceName);
        if ($sourceName === '' && $sourceSensor !== null) {
            $sourceName = trim($sourceSensor['Name']);
            $fallbackVariableID = $sourceSensor['VariableID'];
        }
        if ($sourceName === '' && $fallbackVariableID > 0) {
            $sourceName = sprintf($this->Translate('Variable #%d'), $fallbackVariableID);
        }
        if ($sourceName === '') {
            $sourceName = $this->Translate('Unknown trigger');
        }

        return $sourceName;
    }

    /**
     * @return list<array{Time:int,Event:string,Mode:int,State:int,Source:string}>
     */
    private function ReadEventHistory(): array
    {
        $encodedHistory = $this->ReadAttributeString(self::ATTRIBUTE_EVENT_HISTORY);

        try {
            $decodedHistory = json_decode($encodedHistory, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($decodedHistory) || !array_is_list($decodedHistory)) {
            return [];
        }

        $history = [];
        foreach ($decodedHistory as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $time = $entry['Time'] ?? null;
            $event = $entry['Event'] ?? null;
            $mode = $entry['Mode'] ?? null;
            $state = $entry['State'] ?? null;
            $source = $entry['Source'] ?? null;
            if (
                !is_int($time)
                || !is_string($event)
                || !is_int($mode)
                || !in_array($mode, self::VALID_MODES, true)
                || !is_int($state)
                || !in_array($state, self::VALID_STATES, true)
                || !is_string($source)
            ) {
                continue;
            }

            $history[] = [
                'Time'   => $time,
                'Event'  => $event,
                'Mode'   => $mode,
                'State'  => $state,
                'Source' => $source
            ];
        }

        return array_slice($history, 0, self::EVENT_HISTORY_LIMIT);
    }

    private function AppendEvent(
        string $event,
        string $source = '',
        ?int $mode = null,
        ?int $state = null
    ): void {
        $history = $this->ReadEventHistory();
        array_unshift($history, [
            'Time'   => time(),
            'Event'  => $event,
            'Mode'   => $mode ?? $this->ReadAlarmMode(),
            'State'  => $state ?? $this->ReadAlarmState(),
            'Source' => $source
        ]);

        $this->WriteAttributeString(
            self::ATTRIBUTE_EVENT_HISTORY,
            json_encode(
                array_slice($history, 0, self::EVENT_HISTORY_LIMIT),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );
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
     *     ExitDelay: bool,
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
     *     ExitDelay: bool,
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

    private function StartAlarmDurationTimer(): void
    {
        $seconds = $this->ReadDelaySeconds(self::PROPERTY_ALARM_DURATION_SECONDS);
        if ($seconds <= 0) {
            $this->StopAlarmDurationTimer();

            return;
        }

        $this->WriteAttributeInteger(self::ATTRIBUTE_ALARM_DURATION_DEADLINE, time() + $seconds);
        $this->SetTimerInterval(self::TIMER_ALARM_DURATION, $seconds * 1000);
    }

    private function StopAlarmDurationTimer(): void
    {
        $this->SetTimerInterval(self::TIMER_ALARM_DURATION, 0);
        $this->WriteAttributeInteger(self::ATTRIBUTE_ALARM_DURATION_DEADLINE, 0);
    }

    /**
     * Restores the alarm-output timeout after ApplyChanges or a Symcon restart.
     */
    private function RestoreAlarmDurationTimer(): void
    {
        if (
            $this->ReadAlarmState() !== self::STATE_ALARM
            || $this->ReadAttributeInteger(self::ATTRIBUTE_ALARM_OUTPUT_ACTIVE) !== 1
        ) {
            $this->StopAlarmDurationTimer();
            if (
                $this->ReadAttributeInteger(self::ATTRIBUTE_ALARM_OUTPUT_ACTIVE) !== 0
                || $this->GetValue(self::IDENT_ALARM_OUTPUT_ACTIVE) === true
            ) {
                $this->SetAlarmOutputActive(false);
            }

            return;
        }

        $this->SetAlarmOutputActive(true);
        $seconds = $this->ReadDelaySeconds(self::PROPERTY_ALARM_DURATION_SECONDS);
        if ($seconds <= 0) {
            $this->StopAlarmDurationTimer();

            return;
        }

        $deadline = $this->ReadAttributeInteger(self::ATTRIBUTE_ALARM_DURATION_DEADLINE);
        if ($deadline <= 0) {
            $this->StartAlarmDurationTimer();

            return;
        }

        $remainingSeconds = $deadline - time();
        if ($remainingSeconds <= 0) {
            $this->CompleteAlarmDuration();

            return;
        }

        $this->SetTimerInterval(self::TIMER_ALARM_DURATION, $remainingSeconds * 1000);
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

    private function SetAlarmOutputActive(bool $active): void
    {
        $this->WriteAttributeInteger(self::ATTRIBUTE_ALARM_OUTPUT_ACTIVE, $active ? 1 : 0);
        $this->SetValue(self::IDENT_ALARM_OUTPUT_ACTIVE, $active);
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

    private function SetSystemFault(bool $active): void
    {
        $this->SetValue(self::IDENT_SYSTEM_FAULT, $active);
    }

    private function SetActiveFaults(string $faults): void
    {
        $this->SetValue(self::IDENT_ACTIVE_FAULTS, $faults);
    }

    private function SetBlockingFaults(string $faults): void
    {
        $this->SetValue(self::IDENT_BLOCKING_FAULTS, $faults);
    }

    private function SetLastFaultSource(string $source): void
    {
        $this->SetValue(self::IDENT_LAST_FAULT_SOURCE, $source);
    }

    private function SetLastFaultTime(string $time): void
    {
        $this->SetValue(self::IDENT_LAST_FAULT_TIME, $time);
    }
}
