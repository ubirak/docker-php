<?php

declare(strict_types=1);

namespace App\Infra;

use Symfony\Component\Process\Process;

class ShellDockerProcessFactory
{
    public function stackPs(string $stackName): Process
    {
        return new Process("docker stack ps $stackName --format='{{json .}}'");
    }
}
