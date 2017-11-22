<?php

declare(strict_types=1);

namespace App\Tests\Units\Domain;

use atoum;

class DockerService extends atoum
{
    private $dockerClient;

    private $stackName;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->dockerClient = new \mock\App\Domain\DockerClient();
        $this->stackName = 'someStack';
    }

    public function test it track progress of a stack()
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
                $this->newTestedInstance($this->dockerClient)
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

    public function test it dedupe listed services the first occurrence win()
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
                $this->newTestedInstance($this->dockerClient)
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

    public function test it fail to track progress on any service failure()
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
                $this->newTestedInstance($this->dockerClient)
            )
            ->exception(function () {
                $this->testedInstance->stackProgress($this->stackName);
            })
            ->isInstanceOf('\App\Domain\DockerServiceFailure')
                ->message
                    ->contains($errorMessage)
        ;
    }

    protected function createService(array $payload)
    {
        yield new \App\Domain\Service(
            $payload['Name'],
            new \App\Domain\Service\CurrentState($payload['CurrentState']),
            new \App\Domain\Service\DesiredState($payload['DesiredState']),
            $payload['Error']
        );
    }
}
