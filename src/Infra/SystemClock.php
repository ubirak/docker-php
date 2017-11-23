<?php

declare(strict_types=1);

namespace App\Infra;

use App\Domain\Clock;
use Assert\Assertion;

class SystemClock implements Clock
{
    public function time(): int
    {
        return time();
    }

    public function usleep(int $microSeconds): void
    {
        Assertion::greaterOrEqualThan($microSeconds, 0);
        usleep($microSeconds);
    }
}
