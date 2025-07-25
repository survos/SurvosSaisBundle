<?php

namespace Survos\SaisBundle\Command;

use Survos\SaisBundle\Model\ProcessPayload;
use Survos\SaisBundle\Service\SaisClientService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sais:queue',
    description: 'dispatch images to be resized',
)]
class SaisQueueCommand
{

    public function __construct(
        private SaisClientService $saisService,
    )
    {
    }


    public function __invoke(
        SymfonyStyle     $io,
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
