<?php

declare(strict_types=1);

namespace App\Domain;

use Assert\Assertion;

class WriteOnceHashmap implements \IteratorAggregate, \Countable
{
    private $map;

    public function __construct(array $values = [])
    {
        $this->map = [];

        foreach ($values as $key => $value) {
            $this->add($key, $value);
        }
    }

    public function add(string $key, $value): void
    {
        Assertion::notBlank($key);

        if (array_key_exists($key, $this->map)) {
            throw new \LogicException("Cannot write more than once in the map for key {$key}.");
        }

        $this->map[$key] = $value;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->map);
    }

    public function count(): int
    {
        return count($this->map);
    }
}
