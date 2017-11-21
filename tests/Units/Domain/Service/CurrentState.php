<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain\Service;

use atoum;

class CurrentState extends atoum
{
    /**
     * @dataProvider finalState
     */
    public function test state is final(string $state, bool $isFinal)
    {
        $this
            ->when(
                $this->newTestedInstance($state)
            )
            ->then
                ->boolean($this->testedInstance->isFinal())
                ->isIdenticalTo($isFinal)
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

    protected function finalState(): array
    {
        return [
            'Not final state accepted' => ['accepted', false],
            'Not final state preparing' => ['Preparing 1 second ago', false],
            'Not final state pending' => ['Pending 1 second ago', false],
            'Not final state assigned' => ['assigned 1 second ago', false],
            'Not final state starting' => ['Starting', false],
            'Final state complete' => ['Complete since 2 hours', true],
            'Final state running' => ['Running', true],
            'Final state failed' => ['Failed a few seconds ago', true],
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
