<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain;

use App\Domain\Service\CurrentState;
use App\Domain\Service\DesiredState;
use atoum;

class Service extends atoum
{
    /**
     * @dataProvider convergence
     */
    public function test has converged(CurrentState $currentState, DesiredState $desiredState, string $error, bool $hasConverged)
    {
        $this
            ->given(
                $name = 'some_service.1'
            )
            ->when(
                $this->newTestedInstance($name, $currentState, $desiredState, $error)
            )
            ->then
                ->boolean($this->testedInstance->hasConverged())
                    ->isIdenticalTo($hasConverged)
                ->string($this->testedInstance->getName())
                    ->isIdenticalTo($name)
                ->string($this->testedInstance->getError())
                    ->isIdenticalTo($error)
        ;
    }

    public function test has failed()
    {
        $this
            ->given(
                $name = 'some_service.1',
                $currentState = new CurrentState('failed'),
                $desiredState = new DesiredState('shutdown'),
                $error = 'ho no!'
            )
            ->when(
                $this->newTestedInstance($name, $currentState, $desiredState, $error)
            )
            ->then
                ->boolean($this->testedInstance->hasFailed())
                    ->isTrue()
        ;
    }

    public function test has successfully ended()
    {
        $this
            ->given(
                $name = 'some_service.1',
                $currentState = new CurrentState('success'),
                $desiredState = new DesiredState('shutdown'),
                $error = ''
            )
            ->when(
                $this->newTestedInstance($name, $currentState, $desiredState, $error)
            )
            ->then
                ->boolean($this->testedInstance->hasSuccessfullyEnded())
                    ->isTrue()
        ;
    }

    public function test name cannot be blank()
    {
        $this
            ->given(
                $name = '',
                $error = ''
            )
            ->exception(function () use ($name, $error) {
                $this->newTestedInstance($name, new CurrentState('running'), new DesiredState('running'), $error);
            })
                ->isInstanceOf('\InvalidArgumentException')
                    ->message
                        ->contains('was expected to contain a value')
        ;
    }

    protected function convergence(): array
    {
        return [
            'Convergence [complete,running,]' => [new CurrentState('complete'), new DesiredState('running'), '', true],
            'Convergence [running,running,]' => [new CurrentState('running'), new DesiredState('running'), '', true],
            'Convergence [failed,running,]' => [new CurrentState('failed'), new DesiredState('running'), 'error #1', true],
            'Convergence [complete,shutdown,]' => [new CurrentState('complete'), new DesiredState('shutdown'), '', true],
            'Convergence [running,shutdown,]' => [new CurrentState('running'), new DesiredState('shutdown'), '', true],
            'Convergence [failed,shutdown,error]' => [new CurrentState('failed'), new DesiredState('shutdown'), 'error #2', true],
            '!= Convergence [accepted,shutdown,]' => [new CurrentState('accepted'), new DesiredState('shutdown'), '', false],
        ];
    }
}
