<?php

namespace Survos\SaisBundle\Command;

use Survos\SaisBundle\Model\ProcessPayload;
use Survos\SaisBundle\Service\SaisClientService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand(
    name: 'sais:queue',
    description: 'dispatch images to be resized',
)]
class SaisQueueCommand extends InvokableServiceCommand
{
    use RunsCommands, RunsProcesses;

    public function __construct(
        private SaisClientService $saisService,
    )
    {
        parent::__construct();
    }


    public function __invoke(
        IO     $io,
        #[Argument(description: 'client code')]
        string $code,
        #[Option(name: 'url', description: 'image urls')]
        array $images,
        #[Option(name: 'size', description: 'resize filters')]
        array $sizes,
        #[Option(description: 'wait for process to complete')]
        bool $wait = false,
    ): int
    {
        $response = $this->saisService->dispatchProcess(new ProcessPayload(
            $code,
            $images,
            $sizes,
            wait: $wait
        ));
        dump($response);
        return Command::SUCCESS;
    }
}
