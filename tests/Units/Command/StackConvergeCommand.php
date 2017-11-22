<?php

declare(strict_types=1);

namespace App\Tests\Units\Command;

use atoum;

class StackConvergeCommand extends atoum
{
    private $dockerService;

    private $stackName;

    private $input;

    private $output;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->dockerService = new \mock\App\App\DockerService();
        $stackName = 'someStack';
        $this->stackName = $stackName;

        $this->input = new \mock\Symfony\Component\Console\Input\InputInterface();
        $this->calling($this->input)->getArgument = function ($name) use ($stackName) {
            return 'stack' === $name ? $stackName : null;
        };
        $this->calling($this->input)->getOption = function ($name) {
            return 'limit' === $name ? 2 : null;
        };
        $this->output = new \mock\Symfony\Component\Console\Output\OutputInterface();
        $this->calling($this->output)->getFormatter = new \mock\Symfony\Component\Console\Formatter\OutputFormatterInterface();
    }

    public function test successfuly wait for stack convergence()
    {
        $this
            ->given(
                $this->newTestedInstance($this->dockerService),
                $this->calling($this->dockerService)->stackProgress = new \App\Domain\StackProgress(1, 1)
            )
            ->when(
                $result = $this->testedInstance->execute($this->input, $this->output)
            )
            ->then
                ->variable($result)
                    ->isEqualTo(0)
                ->mock($this->dockerService)
                    ->call('stackProgress')
                        ->withIdenticalArguments($this->stackName)
                        ->twice()
        ;
    }

    public function test fail on service failure()
    {
        $this
            ->given(
                $this->newTestedInstance($this->dockerService),
                $this->calling($this->dockerService)->stackProgress = function () {
                    throw new \App\App\DockerServiceFailure();
                }
            )
            ->when(
                $result = $this->testedInstance->execute($this->input, $this->output)
            )
            ->then
                ->variable($result)
                    ->isEqualTo(131)
        ;
    }

    public function test fail on timeout()
    {
        $this
            ->given(
                $this->newTestedInstance($this->dockerService),
                $this->calling($this->dockerService)->stackProgress = function () {
                    sleep(1);

                    return new \App\Domain\StackProgress(1, 2);
                }
            )
            ->when(
                $result = $this->testedInstance->execute($this->input, $this->output)
            )
            ->then
                ->variable($result)
                    ->isEqualTo(62)
        ;
    }
}
