<?php

declare(strict_types=1);

namespace App\Domain;

interface DockerClient
{
    public function stackPs(string $stackName): \Generator;
}
