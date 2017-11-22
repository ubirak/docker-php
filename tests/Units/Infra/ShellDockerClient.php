<?php

declare(strict_types=1);

namespace App\Tests\Units\Infra;

use atoum;

class ShellDockerClient extends atoum
{
    private $process;

    private $stackName;

    private $shellDockerProcessFactory;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->process = new \mock\Symfony\Component\Process\Process();
        $this->stackName = 'someStack';
        $this->shellDockerProcessFactory = new \mock\App\Infra\ShellDockerProcessFactory();
        $this->calling($this->shellDockerProcessFactory)->stackPs = $this->process;
    }

    public function test stack ps generate services()
    {
        $this
            ->given(
                $this->calling($this->process)->run = 0,
                $this->calling($this->process)->isSuccessful = true,
                $this->calling($this->process)->getOutput = <<<'EOL'
{"Name": "service_foo.1", "CurrentState": "running", "DesiredState": "running", "Error": ""}
{"Name": "service_foo.2", "CurrentState": "running", "DesiredState": "running", "Error": ""}
EOL
            )
            ->and(
                $this->newTestedInstance($this->shellDockerProcessFactory),
                $generator = function () {
                    foreach ($this->testedInstance->stackPs($this->stackName) as $service) {
                        yield $service;
                    }
                }
            )
            ->then
                ->generator($generator())
                    ->hasSize(2)
                ->mock($this->shellDockerProcessFactory)
                    ->call('stackPs')
                        ->withIdenticalArguments($this->stackName)
                        ->once()
                ->mock($this->process)
                    ->call('run')
                        ->once()
                    ->call('isSuccessful')
                        ->once()
                    ->call('getOutput')
                        ->once()
                ->generator($generator())
                    ->yields->variable->isEqualTo(
                        new \App\Domain\Service(
                            'service_foo.1',
                            new \App\Domain\Service\CurrentState('running'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        )
                    )
                    ->yields->variable->isEqualTo(
                        new \App\Domain\Service(
                            'service_foo.2',
                            new \App\Domain\Service\CurrentState('running'),
                            new \App\Domain\Service\DesiredState('running'),
                            ''
                        )
                    )
        ;
    }

    public function test stack ps fail accordingly on process failure()
    {
        $this
            ->given(
                $this->calling($this->process)->run = 1,
                $this->calling($this->process)->isSuccessful = false,
                $this->calling($this->process)->getOutput = '',
                $this->calling($this->process)->getCommandLine = 'some command',
                $this->calling($this->process)->getExitCode = '1',
                $this->calling($this->process)->getExitCodeText = 'Some error code desc',
                $this->calling($this->process)->getWorkingDirectory = '/some/path',
                $this->calling($this->process)->isOutputDisabled = true
            )
            ->and(
                $this->newTestedInstance($this->shellDockerProcessFactory)
            )
            ->exception(function () {
                foreach ($this->testedInstance->stackPs($this->stackName) as $service) {
                    break;
                }
            })
                ->isInstanceOf('\Symfony\Component\Process\Exception\ProcessFailedException')
                ->message
                    ->contains('The command "some command" failed')
        ;
    }

    public function test stack ps fail on non json lines output()
    {
        $this
            ->given(
                $this->calling($this->process)->run = 0,
                $this->calling($this->process)->isSuccessful = true,
                $this->calling($this->process)->getOutput = 'hello I am a non json output!'
            )
            ->and(
                $this->newTestedInstance($this->shellDockerProcessFactory)
            )
            ->exception(function () {
                foreach ($this->testedInstance->stackPs($this->stackName) as $service) {
                    break;
                }
            })
                ->isInstanceOf('\Symfony\Component\Process\Exception\RuntimeException')
                ->message
                    ->contains('json line decode error')
        ;
    }

    /**
     * @dataProvider invalidJsonLine
     */
    public function test stack ps fail on invalid json lines output($invalidJsonLine)
    {
        $this
            ->given(
                $this->calling($this->process)->run = 0,
                $this->calling($this->process)->isSuccessful = true,
                $this->calling($this->process)->getOutput = $invalidJsonLine
            )
            ->and(
                $this->newTestedInstance($this->shellDockerProcessFactory)
            )
            ->exception(function () {
                foreach ($this->testedInstance->stackPs($this->stackName) as $service) {
                }
            })
                ->isInstanceOf('\InvalidArgumentException')
        ;
    }

    protected function invalidJsonLine()
    {
        return [
            'Missing Error on failure' => ['{"Name": "service_foo.1", "CurrentState": "failed", "DesiredState": "running"}'],
            'Missing Name' => ['{"CurrentState": "running", "DesiredState": "running", "Error": ""}'],
            'Missing CurrentState' => ['{"Name": "service_foo.1", "DesiredState": "running", "Error": ""}'],
            'Missing DesiredState' => ['{"Name": "service_foo.2", "CurrentState": "running", "Error": ""}'],
        ];
    }
}
