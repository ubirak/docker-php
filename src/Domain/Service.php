<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Service\CurrentState;
use App\Domain\Service\DesiredState;
use Assert\Assertion;

class Service
{
    private $name;

    private $currentState;

    private $desiredState;

    private $error;

    public function __construct(string $name, CurrentState $currentState, DesiredState $desiredState, string $error)
    {
        Assertion::notBlank($name);

        $this->name = $name;
        $this->currentState = $currentState;
        $this->desiredState = $desiredState;
        if ($currentState->hasFailed()) {
            Assertion::notBlank($error);
        }
        $this->error = $error;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function hasConverged(): bool
    {
        return $this->currentState->isStable() && $this->desiredState->isStable();
    }

    public function hasFailed(): bool
    {
        return $this->currentState->hasFailed();
    }
}
