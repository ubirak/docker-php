<?php

declare(strict_types=1);

namespace App\Domain;

use Assert\Assertion;

class DockerService
{
    public const SHORTLIVED_LABEL = 'label=docker-php.service.lifecycle=shortlived';

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
        yield from $this->genericStackConverge($stackName, $timeout);
    }

    public function stackShortLivedConverge(string $stackName, int $timeout = 300): \Generator
    {
        $serviceCheckMethod = 'hasSuccessfullyEnded';
        $filters = [self::SHORTLIVED_LABEL];

        yield from $this->genericStackConverge($stackName, $timeout, $serviceCheckMethod, $filters);
    }

    public function stackConvergeProgress(string $stackName, string $serviceCheckMethod = 'hasConverged', $filters = [], ?StackProgress $previous = null): StackProgress
    {
        Assertion::notBlank($stackName);
        Assertion::notBlank($serviceCheckMethod);
        $map = new WriteOnceHashmap();

        foreach ($this->dockerClient->stackPs($stackName, $filters) as $service) {
            try {
                $map->add($service->getName(), \call_user_func([$service, $serviceCheckMethod]) ? 1 : 0);

                if ($service->hasFailed()) {
                    throw DockerServiceFailure::serviceFailed($service->getName(), $service->getError());
                }
            } catch (\LogicException $e) {
            }
        }

        return StackProgress::trackFromPrevious(
            \array_sum(\iterator_to_array($map)),
            \count($map),
            $previous
        );
    }

    private function genericStackConverge(string $stackName, int $timeout = 300, string $serviceCheckMethod = 'hasConverged', array $filters = []): \Generator
    {
        Assertion::notBlank($stackName);
        Assertion::greaterThan($timeout, 0);

        $startTime = $this->clock->time();
        for (
            $progress = $previous = $this->stackConvergeProgress($stackName, $serviceCheckMethod, $filters);
            true !== $progress->hasConverged();
            $progress = $this->stackConvergeProgress($stackName, $serviceCheckMethod, $filters, $previous), $previous = $progress
            ) {
            if ($this->clock->time() - $startTime >= $timeout) {
                throw DockerServiceFailure::timeout($timeout);
            }

            yield $progress;

            $this->clock->usleep(self::SLEEP_IN_U_SECONDS);
        }

        yield $progress;
    }
}
