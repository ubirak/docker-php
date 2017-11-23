<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain;

use atoum;

class StackProgress extends atoum
{
    public function test accessors return expected values()
    {
        $this
            ->given(
                $current = 1,
                $desired = 2,
                $step = 1
            )
            ->when(
                $this->newTestedInstance($current, $desired, $step)
            )
            ->then
                ->integer($this->testedInstance->getCurrent())
                    ->isIdenticalTo(1)
                ->integer($this->testedInstance->getDesired())
                    ->isIdenticalTo(2)
                ->integer($this->testedInstance->getStep())
                    ->isIdenticalTo(1)
        ;
    }

    /**
     * @dataProvider trackFromPrevious
     */
    public function test track from previous compute the right step($previousCurrent, $previousDesired, $current, $desired, $expectedStep)
    {
        $this
            ->given(
                $previous = new \App\Domain\StackProgress($previousCurrent, $previousDesired)
            )
            ->when(
                $sut = $this->testedClass->getClass()::trackFromPrevious($current, $desired, $previous)
            )
            ->then
                ->integer($sut->getStep())
                    ->isIdenticalTo($expectedStep)
        ;
    }

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
        ;
    }

    /**
     * @dataProvider invalidBounds
     */
    public function test it fails with invalid bounds(int $current, int $desired, int $step = 0)
    {
        $this
            ->exception(function () use ($current, $desired, $step) {
                $this->newTestedInstance($current, $desired, $step);
            })
            ->isInstanceOf('\InvalidArgumentException')
        ;
    }

    protected function trackFromPrevious(): array
    {
        return [
            '0,1 => 0,1 | step 0' => [0, 1, 0, 1, 0],
            '1,2 => 2,2 | step 1' => [1, 2, 2, 2, 1],
            '1,3 => 3,3 | step 2' => [1, 3, 3, 3, 2],
        ];
    }

    protected function convergence(): array
    {
        return [
            'Convergence #0' => [0, 0, true],
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
            'desired < 0' => [2, -1],
            'step < 0' => [2, 2, -1],
        ];
    }
}
