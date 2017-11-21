<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain;

// use App\Domain\StackProgress;
use atoum;

class Stack extends atoum
{
    public function test it track progress of a stack()
    {
        $this
            ->given(
                $dockerClientMock = new \mock\App\Domain\DockerClient(),
                $this->calling($dockerClientMock)->stackPs = function () {
                    yield from
                    [
                        [
                            'CurrentState' => 'running',
                            'DesiredState' => 'running',
                        ],
                        [
                            'CurrentState' => 'running',
                            'DesiredState' => 'shutdown',
                        ],
                    ];
                }
            )
            ->and(
                $stackName = 'someStack'
            )
            ->and(
                $this->newTestedInstance($dockerClientMock)
            )
            ->when(
                $progress = $this->testedInstance->getProgress($stackName)
            )
            ->then
                ->mock($dockerClientMock)
                    ->call('stackPs')
                        ->withIdenticalArguments($stackName)
                        ->once()
                ->boolean($progress->hasConverged())
                    ->isTrue()
        ;
    }

    public function test it fail to track progress on any service failure()
    {
        $this
            ->given(
                $dockerClientMock = new \mock\App\Domain\DockerClient(),
                $this->calling($dockerClientMock)->stackPs = function () {
                    yield from
                    [
                        [
                            'CurrentState' => 'running',
                            'DesiredState' => 'running',
                        ],
                        [
                            'CurrentState' => 'failed',
                            'DesiredState' => 'running',
                        ],
                    ];
                }
            )
            ->and(
                $stackName = 'someStack'
            )
            ->and(
                $this->newTestedInstance($dockerClientMock)
            )
            ->exception(function () use ($dockerClientMock, $stackName) {
                $this->testedInstance->getProgress($stackName);
            })
            ->isInstanceOf('\App\Domain\ServiceFailure')
        ;
    }
}
