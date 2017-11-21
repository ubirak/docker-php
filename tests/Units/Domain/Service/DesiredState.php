<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain\Service;

use atoum;

class DesiredState extends atoum
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
            'Not final state running' => ['Running', true],
            'Not final state shutdown' => ['shutdown', true],
            'Final state accepted' => [' Accepted', false],
        ];
    }
}
