<?php

declare(strict_types=1);

namespace App\Tests\Units\Infra;

use atoum;

class ShellDockerProcessFactory extends atoum
{
    public function test it create stack ps process()
    {
        $this
            ->given(
                $stackName = 'someStack',
                $expectedCommand = "docker stack ps $stackName --format='{{json .}}'",
                $this->newTestedInstance()
            )
            ->when(
                $process = $this->testedInstance->stackPs($stackName)
            )
            ->then
                ->object($process)
                    ->isInstanceOf('\Symfony\Component\Process\Process')
                ->string($process->getCommandLine())
                    ->isIdenticalTo($expectedCommand)
        ;
    }
}
