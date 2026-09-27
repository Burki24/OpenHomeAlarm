<?php

declare(strict_types=1);

class IPSModuleStrict
{
    public function Migrate(string $JSONData): string
    {
        return $JSONData;
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

function assertIPSViewMigration(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$instance = new OpenHomeAlarm();
$input = json_encode([
    'configuration' => [
        'EnableIPSView'                  => true,
        'IPSViewTheme'                   => 2,
        'IPSViewTransparent'             => true,
        'IPSViewFontScale'               => 115,
        'IPSViewPageColor'               => 'D8C59B',
        'IPSViewSurfaceColorValue'       => 0x9B795A,
        'IPSViewSurfaceStrongColorValue' => 0xAD8A69,
        'IPSViewTextColorValue'          => 0xFFFFFF,
        'IPSViewMutedTextColorValue'     => 0xF1E6D5,
        'IPSViewAccentColorValue'        => 0xE0BE63,
        'IPSViewSuccessColorValue'       => 0x78D79C,
        'IPSViewWarningColorValue'       => 0xFFD166,
        'IPSViewDangerColor'             => 'FF8174',
        'UnrelatedConfiguration'         => 'keep-me'
    ],
    'attributes' => [
        'UnrelatedAttribute' => 42
    ]
], JSON_THROW_ON_ERROR);

$migratedJSON = $instance->Migrate($input);
assertIPSViewMigration($migratedJSON !== '', 'Legacy IPSView settings must trigger a persistence migration.');

$migrated = json_decode($migratedJSON, true, 512, JSON_THROW_ON_ERROR);
$configuration = $migrated['configuration'] ?? [];

assertIPSViewMigration(
    ($configuration['IPSViewStyleSource'] ?? null) === OpenHomeAlarm::IPSVIEW_STYLE_SOURCE_DARK
        && ($configuration['IPSViewStyleTransparentBackground'] ?? null) === true
        && ($configuration['IPSViewStyleFontScale'] ?? null) === 115,
    'Legacy theme, transparency and font scale must map to the universal style properties.'
);
assertIPSViewMigration(
    ($configuration['IPSViewStyleViewBackgroundColor'] ?? null) === 0xD8C59B
        && ($configuration['IPSViewStylePageBackgroundColor'] ?? null) === 0xD8C59B
        && ($configuration['IPSViewStyleControlBackgroundColor'] ?? null) === 0x9B795A
        && ($configuration['IPSViewStyleControlActiveBackgroundColor'] ?? null) === 0xAD8A69
        && ($configuration['IPSViewStylePopupBackgroundColor'] ?? null) === 0xAD8A69,
    'Legacy page and surface colors must map to the universal background roles.'
);
assertIPSViewMigration(
    ($configuration['IPSViewStyleTextColor'] ?? null) === 0xFFFFFF
        && ($configuration['IPSViewStyleTextActiveColor'] ?? null) === 0xFFFFFF
        && ($configuration['IPSViewStyleTextInactiveColor'] ?? null) === 0xF1E6D5
        && ($configuration['IPSViewStyleAccentColor'] ?? null) === 0xE0BE63
        && ($configuration['IPSViewStyleInformationColor'] ?? null) === 0xE0BE63,
    'Legacy text and accent colors must map to the universal text and semantic roles.'
);
assertIPSViewMigration(
    ($configuration['IPSViewStylePositiveColor'] ?? null) === 0x78D79C
        && ($configuration['IPSViewStyleWarningColor'] ?? null) === 0xFFD166
        && ($configuration['IPSViewStyleCriticalColor'] ?? null) === 0xFF8174,
    'Legacy status colors must map to positive, warning and critical roles.'
);
foreach ([
    'IPSViewTheme',
    'IPSViewTransparent',
    'IPSViewFontScale',
    'IPSViewPageColor',
    'IPSViewSurfaceColorValue',
    'IPSViewSurfaceStrongColorValue',
    'IPSViewTextColorValue',
    'IPSViewMutedTextColorValue',
    'IPSViewAccentColorValue',
    'IPSViewSuccessColorValue',
    'IPSViewWarningColorValue',
    'IPSViewDangerColor'
] as $legacyProperty) {
    assertIPSViewMigration(
        !array_key_exists($legacyProperty, $configuration),
        sprintf('Migrated legacy property %s must be removed.', $legacyProperty)
    );
}
assertIPSViewMigration(
    ($configuration['UnrelatedConfiguration'] ?? null) === 'keep-me'
        && (($migrated['attributes']['UnrelatedAttribute'] ?? null) === 42),
    'The migration must preserve unrelated configuration and attributes.'
);
assertIPSViewMigration(
    $instance->Migrate($migratedJSON) === '',
    'Already migrated persistence must not be rewritten.'
);

$lastGoodPartitions = '[{"Enabled":true,"ID":"main","Name":"Main area"},{"Enabled":true,"ID":"schuppen","Name":"Schuppen"}]';
$lastGoodSensors = '[{"Enabled":true,"PartitionID":"schuppen","VariableID":12345,"SensorType":0}]';
$lastGoodEscalation = json_encode([[
    'Enabled'      => true,
    'Name'         => 'Sirene',
    'DelaySeconds' => 0,
    'Action'       => ['actionID' => '{NOTIFY}', 'parameters' => ['TEXT' => 'Alarm']]
]], JSON_THROW_ON_ERROR);
$lastGoodSecurityConfiguration = [
    'Partitions'           => $lastGoodPartitions,
    'Sensors'              => $lastGoodSensors,
    'FaultInputs'          => '[]',
    'AlarmEscalationSteps' => $lastGoodEscalation
];
$partiallyResetPersistence = json_encode([
    'configuration' => [
        'Partitions'               => '[{"Enabled":true,"ID":"main","Name":"Main area"}]',
        'Sensors'                  => $lastGoodSensors,
        'FaultInputs'              => '[]',
        'AlarmEscalationSteps'     => '[]',
        'PushoverNotificationMode' => 2
    ],
    'attributes' => [
        'AppliedSecurityConfiguration' => 'v2:' . json_encode(
            $lastGoodSecurityConfiguration,
            JSON_THROW_ON_ERROR
        )
    ]
], JSON_THROW_ON_ERROR);
$recoveredJSON = $instance->Migrate($partiallyResetPersistence);
assertIPSViewMigration(
    $recoveredJSON !== '',
    'A partial configuration reset with an invalid partition assignment must trigger recovery.'
);
$recoveredPersistence = json_decode($recoveredJSON, true, 512, JSON_THROW_ON_ERROR);
$recoveredConfiguration = $recoveredPersistence['configuration'] ?? [];
assertIPSViewMigration(
    ($recoveredConfiguration['Partitions'] ?? null) === $lastGoodPartitions
        && ($recoveredConfiguration['AlarmEscalationSteps'] ?? null) === $lastGoodEscalation
        && ($recoveredConfiguration['PushoverNotificationMode'] ?? null) === 2,
    'Recovery must restore the last valid areas and escalation steps while preserving newer settings.'
);

$validCurrentPersistence = json_decode($partiallyResetPersistence, true, 512, JSON_THROW_ON_ERROR);
$validCurrentPersistence['configuration']['Partitions'] = $lastGoodPartitions;
$validCurrentPersistence['configuration']['AlarmEscalationSteps'] = '[]';
assertIPSViewMigration(
    $instance->Migrate(json_encode($validCurrentPersistence, JSON_THROW_ON_ERROR)) === '',
    'A valid intentional configuration must not be replaced by an older security snapshot.'
);

$missingSnapshotPersistence = json_decode($partiallyResetPersistence, true, 512, JSON_THROW_ON_ERROR);
$missingSnapshotPersistence['attributes'] = [];
assertIPSViewMigration(
    $instance->Migrate(json_encode($missingSnapshotPersistence, JSON_THROW_ON_ERROR)) === '',
    'An invalid configuration without a verified last-applied snapshot must not be rewritten automatically.'
);

fwrite(STDOUT, "OpenHomeAlarm IPSView style migration checks passed.\n");
