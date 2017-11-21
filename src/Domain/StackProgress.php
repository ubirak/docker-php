<?php

declare(strict_types=1);

namespace App\Domain;

use Assert\Assertion;

class StackProgress
{
    private $current;

    private $desired;

    public function __construct(int $current, int $desired)
    {
        Assertion::greaterThan($desired, 0, null, 'desired');
        Assertion::greaterThan($current, 0, null, 'current');
        Assertion::lessOrEqualThan($current, $desired, null, 'current');

        $this->current = $current;
        $this->desired = $desired;
    }

    public function getCurrent(): int
    {
        return $this->current;
    }

    public function getDesired(): int
    {
        return $this->desired;
    }

    public function hasConverged(): bool
    {
        return $this->getCurrent() === $this->getDesired();
    }
}
