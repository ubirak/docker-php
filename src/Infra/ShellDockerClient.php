<?php

declare(strict_types=1);

namespace App\Infra;

use App\Domain;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ShellDockerClient implements Domain\DockerClient
{
    public function stackPs(string $stackName): \Generator
    {
        $process = new Process("docker stack ps $stackName --format='{{json .}}'");
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $jsonLines = $this->jsonLines($process->getOutput());
        foreach ($jsonLines as $jsonLine) {
            yield json_decode($jsonLine, true);
        }
    }

    private function jsonLines(string $lines): array
    {
        return array_filter(
            explode("\n", $lines),
            function ($line) { return strlen(trim($line)) > 0; }
        );
    }
}
