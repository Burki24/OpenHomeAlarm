<?php

declare(strict_types=1);

namespace Burki24\OpenHomeAlarm;

use UnexpectedValueException;

/** Signals a reference to a missing or disabled alarm partition. */
final class AlarmPartitionAssignmentException extends UnexpectedValueException
{
}
