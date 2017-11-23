<?php

declare(strict_types=1);

namespace App\Tests\Units\Command;

use atoum;

class StackConvergeCommand extends atoum
{
    private $dockerService;

    private $stackName;

    private $timeLimit;

    private $input;

    private $output;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->dockerService = new \mock\App\Domain\DockerService();
        $stackName = 'someStack';
        $this->stackName = $stackName;
        $timeLimit = 2;
        $this->timeLimit = $timeLimit;

        $this->input = new \mock\Symfony\Component\Console\Input\InputInterface();
        $this->calling($this->input)->getArgument = function ($name) use ($stackName) {
            return 'stack' === $name ? $stackName : null;
        };
        $this->calling($this->input)->getOption = function ($name) use ($timeLimit) {
            return 'limit' === $name ? $timeLimit : null;
        };
        $this->output = new \mock\Symfony\Component\Console\Output\OutputInterface();
        $this->calling($this->output)->getFormatter = new \mock\Symfony\Component\Console\Formatter\OutputFormatterInterface();
    }

    public function test successfully wait for stack convergence()
    {
        $this
            ->given(
                $this->newTestedInstance($this->dockerService),
                $this->calling($this->dockerService)->stackConverge = function () {
                    yield new \App\Domain\StackProgress(0, 3);
                    yield new \App\Domain\StackProgress(1, 3, 1);
                    yield new \App\Domain\StackProgress(3, 3, 2);
                },
                $this->calling($this->dockerService)->stackShortLivedConverge = function () {
                    yield new \App\Domain\StackProgress(1, 1);
                }
            )
            ->when(
                $result = $this->testedInstance->execute($this->input, $this->output)
            )
            ->then
                ->variable($result)
                    ->isEqualTo(0)
                ->mock($this->dockerService)
                    ->call('stackConverge')
                        ->withIdenticalArguments($this->stackName, $this->timeLimit)
                        ->twice()
        ;
    }

    public function test fail on service failure()
    {
        $this
            ->given(
                $this->newTestedInstance($this->dockerService),
                $this->calling($this->dockerService)->stackConverge = function () {
                    throw \App\Domain\DockerServiceFailure::serviceFailed('Ho no!');
                }
            )
            ->when(
                $result = $this->testedInstance->execute($this->input, $this->output)
            )
            ->then
                ->variable($result)
                    ->isEqualTo(\App\Domain\DockerServiceFailure::ENOTRECOVERABLE)
        ;
    }

    public function test fail on timeout()
    {
        $this
            ->given(
                $timeLimit = $this->timeLimit,
                $this->newTestedInstance($this->dockerService),
                $this->calling($this->dockerService)->stackConverge = function () use ($timeLimit) {
                    throw \App\Domain\DockerServiceFailure::timeout($timeLimit);
                }
            )
            ->when(
                $result = $this->testedInstance->execute($this->input, $this->output)
            )
            ->then
                ->variable($result)
                    ->isEqualTo(\App\Domain\DockerServiceFailure::ETIME)
        ;
    }
}
