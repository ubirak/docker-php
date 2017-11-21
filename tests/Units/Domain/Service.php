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
    public function test has converged(CurrentState $currentState, DesiredState $desiredState, bool $hasConverged)
    {
        $this
            ->when(
                $this->newTestedInstance($currentState, $desiredState)
            )
            ->then
                ->boolean($this->testedInstance->hasConverged())
                ->isIdenticalTo($hasConverged)
        ;
    }

    protected function convergence(): array
    {
        return [
            'Convergence [complete,running]' => [new CurrentState('complete'), new DesiredState('running'), true],
            'Convergence [running,running]' => [new CurrentState('running'), new DesiredState('running'), true],
            'Convergence [failed,running]' => [new CurrentState('failed'), new DesiredState('running'), true],
            'Convergence [complete,shutdown]' => [new CurrentState('complete'), new DesiredState('shutdown'), true],
            'Convergence [running,shutdown]' => [new CurrentState('running'), new DesiredState('shutdown'), true],
            'Convergence [failed,shutdown]' => [new CurrentState('failed'), new DesiredState('shutdown'), true],
            '!= Convergence [accepted,shutdown]' => [new CurrentState('accepted'), new DesiredState('shutdown'), false],
        ];
    }
}
