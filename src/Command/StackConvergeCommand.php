<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Domain\DockerService;
use App\Domain\DockerServiceFailure;

class StackConvergeCommand extends Command
{
    private $dockerService;

    public function __construct(DockerService $dockerService)
    {
        $this->dockerService = $dockerService;
        parent::__construct('stack:converge');
    }

    protected function configure()
    {
        $this
            ->setDescription('Wait for services of a freshly deployed stack to converge.')
            ->addArgument('stack', InputArgument::REQUIRED, 'name of the stack')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'a duration limit in seconds to wait about the stack to converge', 300)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $stackName = $input->getArgument('stack');
        $limit = intval($input->getOption('limit'));

        try {
            $progress = $this->dockerService->stackConverge($stackName, $limit)->current();
            $io->progressStart($progress->getDesired());
            foreach ($this->dockerService->stackConverge($stackName, $limit) as $progress) {
                $io->progressAdvance($progress->getStep());
            }
            $io->progressFinish();
            $io->success('Stack has successfuly converged');
        } catch (DockerServiceFailure $e) {
            $io->error($e->getMessage());

            return $e->getCode();
        }
    }
}
