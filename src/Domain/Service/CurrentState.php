<?php

declare(strict_types=1);

namespace App\Domain\Service;

use Assert\Assertion;

class CurrentState
{
    // @see https://docs.docker.com/engine/swarm/how-swarm-mode-works/swarm-task-states/
    private const FINAL_STATES = [
        'complete',
        'running',
        'failed',
        'rejected',
    ];

    private const FAILURE_STATES = [
        'failed',
        'rejected',
    ];

    private $state;

    public function __construct(string $state)
    {
        $cleanState = strtok($state, ' ');
        $cleanState = strtolower((false !== $cleanState) ? $cleanState : '');
        Assertion::notBlank($cleanState);

        $this->state = $cleanState;
    }

    public function isFinal(): bool
    {
        return in_array($this->state, self::FINAL_STATES, true);
    }

    public function hasFailed(): bool
    {
        return in_array($this->state, self::FAILURE_STATES, true);
    }
}
