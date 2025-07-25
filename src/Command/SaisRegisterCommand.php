<?php

namespace Survos\SaisBundle\Command;

use Survos\SaisBundle\Model\AccountSetup;
use Survos\SaisBundle\Service\SaisClientService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('sais:register', 'Register a sais client')]
final class SaisRegisterCommand
{
    public function __construct(
        private SaisClientService $saisClientService,
        //?string $name = null
        )
    {
    }


    public function __invoke(
        SymfonyStyle     $io,
        #[Argument(description: '(string)')]
        string $code,
        #[Argument(description: 'approx count of images')]
        int    $count
    ): int
    {
        try {
            $response = $this->saisClientService->accountSetup(new AccountSetup($code, $count));
        } catch (\Throwable $exception) {
            // @todo: throw an except when the account already exists but the number is different
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        if ($response['approxImageCount'] <> $count) {
            $io->warning("Image count cannot be changed if there are any images in the system for it.");
        }

        // @todo: return current count too.
        $io->success($response['userIdentifier'] . ' is registered for ' . $response['approxImageCount']);
        return Command::SUCCESS;
    }

}
