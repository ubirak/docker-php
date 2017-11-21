<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Service\CurrentState;
use App\Domain\Service\DesiredState;

class Service
{
    private $currentState;

    private $desiredState;

    public function __construct(CurrentState $currentState, DesiredState $desiredState)
    {
        $this->currentState = $currentState;
        $this->desiredState = $desiredState;
    }

    public function hasConverged(): bool
    {
        return $this->currentState->isFinal() && $this->desiredState->isFinal();
    }

    public function hasFailed(): bool
    {
        return $this->currentState->hasFailed();
    }
}
