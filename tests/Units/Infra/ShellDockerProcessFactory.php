<?php

declare(strict_types=1);

namespace App\Tests\Units\Infra;

use atoum;

class ShellDockerProcessFactory extends atoum
{
    /**
     * @dataProvider stackPsSamples
     */
    public function test it create stack ps process($stackName, $filters, $expectedCommand)
    {
        $this
            ->given(
                $this->newTestedInstance()
            )
            ->when(
                $process = $this->testedInstance->stackPs($stackName, $filters)
            )
            ->then
                ->object($process)
                    ->isInstanceOf('\Symfony\Component\Process\Process')
                ->string($process->getCommandLine())
                    ->isIdenticalTo($expectedCommand)
        ;
    }

    protected function stackPsSamples(): array
    {
        return [
            'Without filters' => ['someStack', [], "docker stack ps someStack --format='{{json .}}'"],
            'With a filter' => ['someStack', ['label=foo'], "docker stack ps someStack --format='{{json .}}' --filter 'label=foo'"],
            'With many filters' => ['someStack', ['label=foo', 'label=bar'], "docker stack ps someStack --format='{{json .}}' --filter 'label=foo' --filter 'label=bar'"],
        ];
    }
}
