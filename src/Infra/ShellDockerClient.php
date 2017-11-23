<?php

declare(strict_types=1);

namespace App\Infra;

use App\Domain;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;

class ShellDockerClient implements Domain\DockerClient
{
    private $processFactory;

    public function __construct(ShellDockerProcessFactory $processFactory)
    {
        $this->processFactory = $processFactory;
    }

    public function stackPs(string $stackName, array $filters = []): \Generator
    {
        $process = $this->processFactory->stackPs($stackName, $filters);
        $process->run();

        if (1 === $process->getExitCode() && false !== strpos($process->getErrorOutput(), "nothing found in stack: $stackName")) {
            yield from [];

            return;
        }

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $jsonLines = $this->jsonLines($process->getOutput());
        foreach ($jsonLines as $jsonLine) {
            $json = @json_decode($jsonLine, true);
            if (null === $json) {
                $error = json_last_error_msg();
                throw new RuntimeException("docker stack ps {$stackName} json line decode error: {$error}");
            }

            yield new Domain\Service(
                $json['Name'] ?? '',
                new Domain\Service\CurrentState($json['CurrentState'] ?? ''),
                new Domain\Service\DesiredState($json['DesiredState'] ?? ''),
                $json['Error'] ?? ''
            );
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
