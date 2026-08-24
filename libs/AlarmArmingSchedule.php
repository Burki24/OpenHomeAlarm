<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use JsonException;
use UnexpectedValueException;

/** Normalizes weekly automatic-arming schedules and selects entries due now. */
final class AlarmArmingSchedule
{
    private const DAY_FIELDS = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
        5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
    ];

    private const MODES = ['home', 'away', 'night'];

    /** @return list<array<string, bool|string>> */
    public static function schedules(string $encodedSchedules): array
    {
        if (trim($encodedSchedules) === '') {
            return [];
        }

        try {
            $schedules = json_decode($encodedSchedules, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException('Invalid automatic arming schedule JSON.', 0, $exception);
        }
        if (!is_array($schedules) || !array_is_list($schedules)) {
            throw new UnexpectedValueException('Automatic arming schedules must be a list.');
        }

        $normalized = [];
        foreach ($schedules as $index => $schedule) {
            if (!is_array($schedule)) {
                throw new UnexpectedValueException('Every automatic arming schedule must be an object.');
            }

            $enabled = $schedule['Enabled'] ?? true;
            $name = $schedule['Name'] ?? '';
            $time = $schedule['Time'] ?? '';
            $mode = $schedule['Mode'] ?? 'away';
            if (!is_bool($enabled) || !is_string($name) || !is_string($time) || !is_string($mode)) {
                throw new UnexpectedValueException('Invalid automatic arming schedule field type.');
            }

            $days = [];
            foreach (self::DAY_FIELDS as $field) {
                $value = $schedule[$field] ?? false;
                if (!is_bool($value)) {
                    throw new UnexpectedValueException('Automatic arming weekdays must be boolean values.');
                }
                $days[$field] = $value;
            }

            $name = trim($name);
            $time = trim($time);
            $mode = strtolower(trim($mode));
            if (preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $time) !== 1) {
                throw new UnexpectedValueException('Automatic arming time must use the HH:MM format.');
            }
            if (!in_array($mode, self::MODES, true)) {
                throw new UnexpectedValueException('Invalid automatic arming mode.');
            }
            if ($enabled && !in_array(true, $days, true)) {
                throw new UnexpectedValueException('Enabled automatic arming schedules require at least one weekday.');
            }

            $normalized[] = array_merge([
                'Enabled' => $enabled,
                'Name'    => $name !== '' ? $name : sprintf('Schedule %d', $index + 1)
            ], $days, ['Time' => $time, 'Mode' => $mode]);
        }

        return $normalized;
    }

    /**
     * @param list<array<string, bool|string>> $schedules
     *
     * @return list<array<string, bool|string>>
     */
    public static function due(array $schedules, int $isoWeekday, string $time): array
    {
        $dayField = self::DAY_FIELDS[$isoWeekday] ?? null;
        if ($dayField === null) {
            return [];
        }

        return array_values(array_filter(
            $schedules,
            static fn (array $schedule): bool => $schedule['Enabled'] === true
                && $schedule[$dayField] === true
                && $schedule['Time'] === $time
        ));
    }

    /** @param array<string, bool|string> $schedule */
    public static function executionKey(array $schedule, string $localMinute): string
    {
        return hash('sha256', json_encode($schedule, JSON_THROW_ON_ERROR)) . ':' . $localMinute;
    }
}
