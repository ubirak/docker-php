<?php

declare(strict_types=1);

namespace App\App;

use App\Domain\DockerClient;
use App\Domain\StackProgress;

class DockerService
{
    private $dockerClient;

    public function __construct(DockerClient $dockerClient)
    {
        $this->dockerClient = $dockerClient;
    }

    public function stackProgress(string $stackName): StackProgress
    {
        $currentCount = 0;
        $desiredCount = 0;
        $encounteredServices = [];

        foreach ($this->dockerClient->stackPs($stackName) as $service) {
            if (array_key_exists($service->getName(), $encounteredServices)) {
                continue;
            }
            $encounteredServices[$service->getName()] = true;

            if ($service->hasFailed()) {
                throw new DockerServiceFailure($service->getError());
            }

            ++$desiredCount;
            if ($service->hasConverged()) {
                $currentCount += 1;
            }
        }

        return new StackProgress($currentCount, $desiredCount);
    }
}
