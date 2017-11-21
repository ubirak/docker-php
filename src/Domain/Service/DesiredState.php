<?php

declare(strict_types=1);

namespace App\Domain\Service;

use Assert\Assertion;

class DesiredState
{
    private const FINAL_STATES = [
        'running',
        'shutdown',
    ];

    private $state;

    public function __construct(string $state)
    {
        $cleanState = strtolower($state);
        Assertion::notBlank($cleanState);

        $this->state = $cleanState;
    }

    public function isFinal(): bool
    {
        return in_array($this->state, self::FINAL_STATES, true);
    }
}
