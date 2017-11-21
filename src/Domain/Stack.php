<?php

declare(strict_types=1);

namespace App\Domain;

class Stack
{
    private $dockerClient;

    public function __construct(DockerClient $dockerClient)
    {
        $this->dockerClient = $dockerClient;
    }

    public function getProgress(string $stackName): StackProgress
    {
        $currentCount = 0;
        $desiredCount = 0;

        foreach ($this->dockerClient->stackPs($stackName) as $process) {
            $service = new Service(
                new Service\CurrentState($process['CurrentState']),
                new Service\DesiredState($process['DesiredState'])
            );
            if ($service->hasFailed()) {
                throw new ServiceFailure();
            }

            ++$desiredCount;
            if ($service->hasConverged()) {
                $currentCount += 1;
            }
        }

        return new StackProgress($currentCount, $desiredCount);
    }
}
