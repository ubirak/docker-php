<?php

declare(strict_types=1);

namespace App\App;

use App\Domain;

class DockerService
{
    private $dockerClient;

    public function __construct(Domain\DockerClient $dockerClient)
    {
        $this->dockerClient = $dockerClient;
    }

    public function stackProgress(string $stackName): Domain\StackProgress
    {
        $currentCount = 0;
        $desiredCount = 0;
        $encounteredServices = [];

        foreach ($this->dockerClient->stackPs($stackName) as $process) {
            if (array_key_exists($process['Name'], $encounteredServices)) {
                continue;
            }

            $encounteredServices[$process['Name']] = true;
            $service = new Domain\Service(
                new Domain\Service\CurrentState($process['CurrentState']),
                new Domain\Service\DesiredState($process['DesiredState'])
            );
            if ($service->hasFailed()) {
                throw new Domain\ServiceFailure();
            }

            ++$desiredCount;
            if ($service->hasConverged()) {
                $currentCount += 1;
            }
        }

        return new Domain\StackProgress($currentCount, $desiredCount);
    }
}
