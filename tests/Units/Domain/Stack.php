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
                            'Name' => 'some_service_foo.1',
                            'CurrentState' => 'running',
                            'DesiredState' => 'running',
                        ],
                        [
                            'Name' => 'some_service_bar.1',
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

    public function test it dedupe listed services the first occurrence win()
    {
        $this
            ->given(
                $dockerClientMock = new \mock\App\Domain\DockerClient(),
                $this->calling($dockerClientMock)->stackPs = function () {
                    yield from
                    [
                        [
                            'Name' => 'some_service_foo.1',
                            'CurrentState' => 'running',
                            'DesiredState' => 'running',
                        ],
                        [
                            'Name' => 'some_service_foo.1',
                            'CurrentState' => 'failed',
                            'DesiredState' => 'shutdown',
                        ],
                        [
                            'Name' => 'some_service_foo.1',
                            'CurrentState' => 'failed',
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
                            'Name' => 'some_service_foo.1',
                            'CurrentState' => 'running',
                            'DesiredState' => 'running',
                        ],
                        [
                            'Name' => 'some_service_bar.1',
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
