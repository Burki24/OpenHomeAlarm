<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

/**
 * Formats the stable, presentation-independent diagnostics API payload.
 */
final class AlarmDiagnostics
{
    public const API_VERSION = 1;

    /**
     * @param list<array{
     *     Kind:string,
     *     Name:string,
     *     VariableID:int,
     *     PartitionID:string,
     *     Enabled:bool,
     *     Monitored:bool,
     *     Status:string,
     *     Active:bool|null,
     *     LastChanged:int,
     *     LastUpdated:int
     * }> $items
     *
     * @return array<string,mixed>
     */
    public static function build(array $items, int $generatedAt): array
    {
        $summary = [
            'Total'      => count($items),
            'Ready'      => 0,
            'Triggered'  => 0,
            'Missing'    => 0,
            'Unreadable' => 0,
            'Disabled'   => 0,
            'Problems'   => 0,
            'Healthy'    => true
        ];

        foreach ($items as $item) {
            $key = match ($item['Status']) {
                'ready'      => 'Ready',
                'triggered'  => 'Triggered',
                'missing'    => 'Missing',
                'unreadable' => 'Unreadable',
                'disabled'   => 'Disabled',
                default      => null
            };
            if ($key !== null) {
                ++$summary[$key];
            }
            if (
                in_array($item['Status'], ['missing', 'unreadable'], true)
                || ($item['Kind'] === 'fault' && $item['Status'] === 'triggered')
            ) {
                ++$summary['Problems'];
            }
        }
        $summary['Healthy'] = $summary['Problems'] === 0;

        return [
            'ApiVersion'  => self::API_VERSION,
            'GeneratedAt' => max(0, $generatedAt),
            'Summary'     => $summary,
            'Items'       => array_values($items)
        ];
    }

    /** @param array<string,mixed> $payload */
    public static function encode(array $payload): string
    {
        return json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
