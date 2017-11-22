<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain;

use atoum;

class StackProgress extends atoum
{
    /**
     * @dataProvider convergence
     */
    public function test it converge(int $current, int $desired, bool $hasConverged)
    {
        $this
            ->when(
                $this->newTestedInstance($current, $desired)
            )
            ->then
                ->boolean($this->testedInstance->hasConverged())
                    ->isIdenticalTo($hasConverged)
                ->integer($this->testedInstance->getCurrent())
                    ->isIdenticalTo($current)
                ->integer($this->testedInstance->getDesired())
                    ->isIdenticalTo($desired)
        ;
    }

    /**
     * @dataProvider invalidBounds
     */
    public function test it fails with invalid bounds(int $current, int $desired)
    {
        $this
            ->exception(function () use ($current, $desired) {
                $this->newTestedInstance($current, $desired);
            })
            ->isInstanceOf('\InvalidArgumentException')
        ;
    }

    public function convergence(): array
    {
        return [
            'Convergence #1' => [1, 1, true],
            'Convergence #2' => [PHP_INT_MAX, PHP_INT_MAX, true],
            '!= Convergence #1' => [0, 1, false],
            '!= Convergence #2' => [1, 2, false],
            '!= Convergence #3' => [1, PHP_INT_MAX, false],
        ];
    }

    protected function invalidBounds(): array
    {
        return [
            'current < 0' => [-1, 1],
            'current > desired' => [2, 1],
            'desired = 0' => [2, 0],
            'desired < 0' => [2, -1],
        ];
    }
}
