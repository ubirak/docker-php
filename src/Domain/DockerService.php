<?php

declare(strict_types=1);

namespace App\Domain;

use Assert\Assertion;

class DockerService
{
    private const SLEEP_IN_U_SECONDS = 3 * (10 ** 5);

    private $dockerClient;

    private $clock;

    public function __construct(DockerClient $dockerClient, Clock $clock)
    {
        $this->dockerClient = $dockerClient;
        $this->clock = $clock;
    }

    public function stackConverge(string $stackName, int $timeout = 300): \Generator
    {
        Assertion::notBlank($stackName);
        Assertion::greaterThan($timeout, 0);

        $startTime = $this->clock->time();
        for (
            $progress = $previous = $this->stackProgress($stackName);
            true !== $progress->hasConverged();
            $progress = $this->stackProgress($stackName, $previous), $previous = $progress
            ) {
            if ($this->clock->time() - $startTime >= $timeout) {
                throw DockerServiceFailure::timeout($timeout);
            }

            yield $progress;

            $this->clock->usleep(self::SLEEP_IN_U_SECONDS);
        }

        yield $progress;
    }

    public function stackProgress(string $stackName, ?StackProgress $previous = null): StackProgress
    {
        Assertion::notBlank($stackName);
        $map = new WriteOnceHashmap();

        foreach ($this->dockerClient->stackPs($stackName) as $service) {
            try {
                $map->add($service->getName(), $service->hasConverged() ? 1 : 0);

                if ($service->hasFailed()) {
                    throw DockerServiceFailure::serviceFailed($service->getError());
                }
            } catch (\LogicException $e) {
            }
        }

        return StackProgress::trackFromPrevious(
            array_sum(iterator_to_array($map)),
            count($map),
            $previous
        );
    }
}
