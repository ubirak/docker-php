<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain\Service;

use atoum;

class CurrentState extends atoum
{
    /**
     * @dataProvider stableState
     */
    public function test state is stable(string $state, bool $isStable)
    {
        $this
            ->when(
                $this->newTestedInstance($state)
            )
            ->then
                ->boolean($this->testedInstance->isStable())
                ->isIdenticalTo($isStable)
        ;
    }

    /**
     * @dataProvider failureState
     */
    public function test state is failure($state)
    {
        $this
            ->when(
                $this->newTestedInstance($state)
            )
            ->then
                ->boolean($this->testedInstance->hasFailed())
                ->isTrue()
        ;
    }

    public function test invalid state is not accepted()
    {
        $this
            ->given(
                $state = ''
            )
            ->exception(function () use ($state) {
                $this->newTestedInstance($state);
            })
            ->isInstanceOf('\InvalidArgumentException')
        ;
    }

    protected function stableState(): array
    {
        return [
            'Not stable state accepted' => ['accepted', false],
            'Not stable state preparing' => ['Preparing 1 second ago', false],
            'Not stable state pending' => ['Pending 1 second ago', false],
            'Not stable state assigned' => ['assigned 1 second ago', false],
            'Not stable state starting' => ['Starting', false],
            'Stable state complete' => ['Complete since 2 hours', true],
            'Stable state running' => ['Running', true],
            'Stable state failed' => ['Failed a few seconds ago', true],
        ];
    }

    protected function failureState(): array
    {
        return [
            'Not failure state failed' => ['failed'],
            'Not failure state rejected' => ['rejected'],
        ];
    }
}
