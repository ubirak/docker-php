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
        Assertion::greaterThan($desired, 0);
        Assertion::greaterThan($current, 0);
        Assertion::lessOrEqualThan($current, $desired);

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
