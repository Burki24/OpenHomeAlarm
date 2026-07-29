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
        'EnableIPSView'            => true,
        'IPSViewPageColor'         => 'D8C59B',
        'IPSViewSurfaceColor'      => '#9B795A',
        'IPSViewTextColor'         => 'FFFFFF',
        'IPSViewDangerColor'       => 'FF8174',
        'UnrelatedConfiguration'   => 'keep-me'
    ],
    'attributes' => [
        'UnrelatedAttribute' => 42
    ]
], JSON_THROW_ON_ERROR);

$migratedJSON = $instance->Migrate($input);
assertIPSViewMigration($migratedJSON !== '', 'Legacy IPSView colors must trigger a persistence migration.');

$migrated = json_decode($migratedJSON, true, 512, JSON_THROW_ON_ERROR);
$configuration = $migrated['configuration'] ?? [];

assertIPSViewMigration(
    ($configuration['IPSViewPageColorValue'] ?? null) === 0xD8C59B
        && ($configuration['IPSViewSurfaceColorValue'] ?? null) === 0x9B795A
        && ($configuration['IPSViewTextColorValue'] ?? null) === 0xFFFFFF
        && ($configuration['IPSViewDangerColorValue'] ?? null) === 0xFF8174,
    'Legacy hexadecimal strings must be converted to integer SelectColor values.'
);
assertIPSViewMigration(
    !array_key_exists('IPSViewPageColor', $configuration)
        && !array_key_exists('IPSViewSurfaceColor', $configuration)
        && !array_key_exists('IPSViewTextColor', $configuration)
        && !array_key_exists('IPSViewDangerColor', $configuration),
    'Migrated legacy color properties must be removed from the persistence.'
);
assertIPSViewMigration(
    ($configuration['UnrelatedConfiguration'] ?? null) === 'keep-me'
        && (($migrated['attributes']['UnrelatedAttribute'] ?? null) === 42),
    'The migration must preserve unrelated configuration and attributes.'
);
assertIPSViewMigration(
    $instance->Migrate($migratedJSON) === '',
    'Already migrated persistence must not be rewritten.'
);

fwrite(STDOUT, "OpenHomeAlarm IPSView color migration checks passed.\n");
