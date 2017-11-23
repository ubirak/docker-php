<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain;

use atoum;

class DockerService extends atoum
{
    private $dockerClient;

    private $clock;

    private $stackName;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->dockerClient = new \mock\App\Domain\DockerClient();
        $this->clock = new \mock\App\Domain\Clock();
        $this->stackName = 'someStack';
    }

    public function test it track stack convergence()
    {
        $this
            ->given(
                $this->calling($this->clock)->time = time()
            )
            ->and(
                $this->calling($this->dockerClient)->stackPs = function () {
                    yield from [
                        new \App\Domain\Service(
                            'some_service_foo.1',
                            new \App\Domain\Service\CurrentState('pending'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        ),
                        new \App\Domain\Service(
                            'some_service_bar.1',
                            new \App\Domain\Service\CurrentState('running'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        ),
                    ];
                },
                $this->calling($this->dockerClient)->stackPs[2] = function () {
                    yield from [
                        new \App\Domain\Service(
                            'some_service_foo.1',
                            new \App\Domain\Service\CurrentState('running'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        ),
                        new \App\Domain\Service(
                            'some_service_bar.1',
                            new \App\Domain\Service\CurrentState('running'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        ),
                    ];
                }
            )
            ->and(
                $this->newTestedInstance($this->dockerClient, $this->clock)
            )
            ->and(
                $generator = function () {
                    foreach ($this->testedInstance->stackConverge($this->stackName) as $progress) {
                        yield $progress;
                    }
                }
            )
            ->generator($generator())
                ->yields
                    ->variable
                        ->isEqualTo(
                            new \App\Domain\StackProgress(1, 2)
                        )
                ->yields
                    ->variable
                        ->isEqualTo(
                            new \App\Domain\StackProgress(2, 2, 1)
                        )
        ;
    }

    public function test it track stack progress()
    {
        $this
            ->given(
                $this->calling($this->dockerClient)->stackPs = function () {
                    yield from [
                        new \App\Domain\Service(
                            'some_service_foo.1',
                            new \App\Domain\Service\CurrentState('running'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        ),
                        new \App\Domain\Service(
                            'some_service_bar.1',
                            new \App\Domain\Service\CurrentState('running'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        ),
                    ];
                }
            )
            ->and(
                $previousProgress = new \App\Domain\StackProgress(0, 2)
            )
            ->and(
                $this->newTestedInstance($this->dockerClient, $this->clock)
            )
            ->when(
                $progress = $this->testedInstance->stackProgress($this->stackName, $previousProgress)
            )
            ->then
                ->mock($this->dockerClient)
                    ->call('stackPs')
                        ->withIdenticalArguments($this->stackName)
                        ->once()
                ->boolean($progress->hasConverged())
                    ->isTrue()
                ->integer($progress->getStep())
                    ->isIdenticalTo(2)
        ;
    }

    public function test it dedupe stack services the first occurrence win()
    {
        $this
            ->given(
                $this->calling($this->dockerClient)->stackPs = function () {
                    yield from [
                        new \App\Domain\Service(
                            'some_service_foo.1',
                            new \App\Domain\Service\CurrentState('running'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        ),
                        new \App\Domain\Service(
                            'some_service_foo.1',
                            new \App\Domain\Service\CurrentState('failed'),
                            new \App\Domain\Service\DesiredState('shutdown'),
                            'Some error'
                        ),
                        new \App\Domain\Service(
                            'some_service_foo.1',
                            new \App\Domain\Service\CurrentState('failed'),
                            new \App\Domain\Service\DesiredState('shutdown'),
                            'Some other error'
                        ),
                    ];
                }
            )
            ->and(
                $this->newTestedInstance($this->dockerClient, $this->clock)
            )
            ->when(
                $progress = $this->testedInstance->stackProgress($this->stackName)
            )
            ->then
                ->mock($this->dockerClient)
                    ->call('stackPs')
                        ->withIdenticalArguments($this->stackName)
                        ->once()
                ->boolean($progress->hasConverged())
                    ->isTrue()
        ;
    }

    public function test it fail to track stack progress on any service failure()
    {
        $this
            ->given(
                $errorMessage = 'Some Error from some service with exit code 127',
                $this->calling($this->dockerClient)->stackPs = function () use ($errorMessage) {
                    yield from [
                        new \App\Domain\Service(
                            'some_service_foo.1',
                            new \App\Domain\Service\CurrentState('running'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        ),
                        new \App\Domain\Service(
                            'some_service_bar.1',
                            new \App\Domain\Service\CurrentState('failed'),
                            new \App\Domain\Service\DesiredState('running'),
                            $errorMessage
                        ),
                    ];
                }
            )
            ->and(
                $this->newTestedInstance($this->dockerClient, $this->clock)
            )
            ->exception(function () {
                $this->testedInstance->stackProgress($this->stackName);
            })
            ->isInstanceOf('\App\Domain\DockerServiceFailure')
                ->message
                    ->contains($errorMessage)
        ;
    }
}
