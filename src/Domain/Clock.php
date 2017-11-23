<?php

declare(strict_types=1);

namespace App\Domain;

interface Clock
{
    public function time(): int;

    public function usleep(int $microSeconds): void;
}
