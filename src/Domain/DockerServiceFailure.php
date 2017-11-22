<?php

declare(strict_types=1);

namespace App\Domain;

class DockerServiceFailure extends \RuntimeException
{
    // following consts refers to POSIX errno codes
    public const ENOTRECOVERABLE = 131; /* ENOTRECOVERABLE */

    public static function serviceFailed(string $message)
    {
        return new static($message, self::ENOTRECOVERABLE);
    }
}
