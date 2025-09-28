<?php
declare(strict_types=1);

namespace Survos\SaisBundle\Command;

use Survos\SaisBundle\Contract\SelectionProviderInterface;
use Survos\SaisBundle\Event\IterateBatchEvent;
use Survos\SaisBundle\Event\SelectionBatchEvent;
use Survos\SaisBundle\Service\SaisClientService;
use Survos\SaisBundle\Service\SaisHttpClientService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand('sais:iterate', 'Iterate a selection, resolve via SAIS, then dispatch result batches')]
final class SaisIterateCommand
{
    public function __construct(
        public readonly EventDispatcherInterface $dispatcher,
        public readonly SaisHttpClientService $client,
//        public readonly SelectionProviderInterface $selection,   // REQUIRED (no B/C)
        public readonly ValidatorInterface $validator,
    ) {}

    public function __invoke(
        SymfonyStyle $io,

        #[Option('Batch size for resolution/events (default: 500)')]
        int $batch = 500,

        #[Option('Dataset/root code (e.g. "grp")')]
        ?string $root = null,

        #[Option('Start offset (default: 0)')]
        int $offset = 0,

        #[Option('Stop after N records (omit for all)')]
        ?int $limit = null,

        #[Option('Simulate without dispatching result events')]
        bool $dryRun = false,
    ): int {
        // --- Validate options with Symfony Validator (no manual error strings)
        $input = [
            'batch'  => $batch,
            'offset' => $offset,
            'limit'  => $limit,
        ];

        $constraints = new Assert\Collection([
            'batch'  => [new Assert\NotNull(), new Assert\Type('integer'), new Assert\GreaterThanOrEqual(1)],
            'offset' => [new Assert\NotNull(), new Assert\Type('integer'), new Assert\GreaterThanOrEqual(0)],
            'limit'  => [new Assert\Type(['type' => 'integer']), new Assert\GreaterThanOrEqual(1)],
        ]);

        $violations = $this->validator->validate($input, $constraints);

        if (\count($violations) > 0) {
            foreach ($violations as $v) {
                $io->error($v->getPropertyPath() . ': ' . $v->getMessage());
            }
            return Command::FAILURE;
        }

        $total = $this->selection->countSelections($root);
        $target = $limit ? min(max(0, $total - $offset), $limit) : max(0, $total - $offset);

        $io->title('SAIS Iterate');
        $io->writeln(sprintf(
            'root=%s batch=%d offset=%d limit=%s total=%d target=%d dryRun=%s',
            $root ?? '(none)', $batch, $offset, $limit ?? '∞', $total, $target, $dryRun ? 'yes' : 'no'
        ));

        if ($target <= 0) {
            $io->warning('Nothing to process.');
            return Command::SUCCESS;
        }

        $progress = $io->createProgressBar($target);
        $progress->setFormat('verbose');
        $progress->start();

        $meta = [
            'runId'     => bin2hex(random_bytes(6)),
            'startedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $seen = 0;
        $ids  = [];

        foreach ($this->selection->getSelectionIterator($root, $offset, $limit) as $sel) {
            $id = (string) ($sel['saisCode'] ?? $sel['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $ids[] = $id;
            $seen++;

            if ($io->isVerbose() && ($seen % 5000) === 0) {
                $io->writeln(sprintf('… gathered %d selections', $seen));
            }

            if (\count($ids) >= $batch) {
                $this->resolveAndDispatch($root, $ids, $meta, $dryRun, $io);
                $progress->advance(\count($ids));
                $ids = [];
            }
        }

        if ($ids !== []) {
            $this->resolveAndDispatch($root, $ids, $meta, $dryRun, $io);
            $progress->advance(\count($ids));
        }

        $progress->finish();
        $io->newLine(2);
        $io->success(sprintf('Done. Processed %d record%s. runId=%s', $seen, $seen === 1 ? '' : 's', $meta['runId']));

        return Command::SUCCESS;
    }

    /** @param string[] $ids */
    private function resolveAndDispatch(?string $root, array $ids, array $meta, bool $dryRun, SymfonyStyle $io): void
    {
        if ($io->isVeryVerbose()) {
            $io->writeln(sprintf('Resolving %d id(s) via SAIS', \count($ids)));
        }

        // Tell listeners what we are about to resolve (useful for metrics/logging)
        $this->dispatcher->dispatch(new SelectionBatchEvent($root ?? '', $ids, $meta));

        // Resolve selection → full media rows via SAIS
        $rows = $this->client->fetchMediaByIds($ids);

        // Dispatch resolved rows
        $event = new IterateBatchEvent(root: $root ?? '', batch: $rows, offset: 0, count: \count($rows), meta: $meta);
        if (!$dryRun) {
            $this->dispatcher->dispatch($event);
        }

        if ($io->isVeryVerbose()) {
            $io->writeln(sprintf('Dispatched batch with %d resolved row(s).', \count($rows)));
        }
    }
}
