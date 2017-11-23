<?php

declare(strict_types=1);

namespace App\Infra;

use Symfony\Component\Process\Process;

class ShellDockerProcessFactory
{
    public function stackPs(string $stackName, array $filters = []): Process
    {
        $filter = join(' ', array_map(
            function ($expression) {
                return sprintf("--filter '%s'", trim($expression, "'\t\n\r\0\x0B"));
            },
            $filters
        ));
        $commandLine = trim(sprintf("docker stack ps $stackName --format='{{json .}}' $filter"));

        return new Process($commandLine);
    }
}
