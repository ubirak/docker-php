<?php

declare(strict_types=1);

namespace App\Domain;

use Assert\Assertion;

class StackProgress
{
    private $current;

    private $desired;

    private $step;

    public function __construct(int $current, int $desired, int $step = 0)
    {
        Assertion::greaterThan($desired, 0);
        Assertion::greaterOrEqualThan($current, 0);
        Assertion::lessOrEqualThan($current, $desired);
        Assertion::greaterOrEqualThan($step, 0);

        $this->current = $current;
        $this->desired = $desired;
        $this->step = $step;
    }

    public static function trackFromPrevious(int $current, int $desired, ?self $previous)
    {
        $step = 0;
        if (null !== $previous) {
            $step = $current - $previous->getCurrent();
        }

        return new static($current, $desired, $step);
    }

    public function getCurrent(): int
    {
        return $this->current;
    }

    public function getDesired(): int
    {
        return $this->desired;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function hasConverged(): bool
    {
        return $this->getCurrent() === $this->getDesired();
    }

    public function __toString(): string
    {
        return "{$this->current}/{$this->desired} [+{$this->step}]";
    }
}
