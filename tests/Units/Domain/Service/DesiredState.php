<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain\Service;

use atoum;

class DesiredState extends atoum
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

    public function test state is shutdown()
    {
        $this
            ->when(
                $this->newTestedInstance('shutdown')
            )
            ->then
                ->boolean($this->testedInstance->isShutdown())
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
            'Not stable state running' => ['Running', true],
            'Not stable state shutdown' => ['shutdown', true],
            'Stable state accepted' => [' Accepted', false],
        ];
    }
}
