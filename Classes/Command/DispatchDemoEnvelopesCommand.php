<?php


declare(strict_types=1);

namespace WapplerSystems\MessengerDemo\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration as Typo3ExtensionConfiguration;
use WapplerSystems\MessengerDemo\Configuration\ExtensionConfiguration;
use WapplerSystems\MessengerDemo\Message\DemoJobMessage;

#[AsCommand(
    name: 'messenger-demo:dispatch',
    description: 'Dispatch demo envelopes at configurable intervals to Symfony Messenger.',
)]
final class DispatchDemoEnvelopesCommand extends Command
{

    public function __construct(
        private readonly MessageBusInterface         $messageBus,
        private readonly Typo3ExtensionConfiguration $typo3ExtensionConfiguration
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var array<string,mixed> $raw */
        $raw = (array)$this->typo3ExtensionConfiguration->get(ExtensionConfiguration::EXTKEY);
        $cfg = ExtensionConfiguration::fromArray($raw);

        $io->title('Messenger Demo Dispatcher');
        $io->writeln(sprintf('Transport: %s', $cfg->transportName));
        $io->writeln(sprintf('Batch size: %d', $cfg->batchSize));
        $io->writeln(sprintf('Tick interval: %d s (+ jitter up to %d s)', $cfg->tickIntervalSeconds, $cfg->tickJitterSeconds));
        $io->writeln(sprintf('Processing: %d..%d ms, fail rate: %d%%', $cfg->processingMinMs, $cfg->processingMaxMs, $cfg->failRatePercent));
        if ($cfg->maxMessagesTotal > 0) {
            $io->writeln(sprintf('Max messages total: %d', $cfg->maxMessagesTotal));
        }
        if ($cfg->maxRuntimeSeconds > 0) {
            $io->writeln(sprintf('Max runtime: %d s', $cfg->maxRuntimeSeconds));
        }

        $start = time();
        $dispatched = 0;

        while (true) {
            // Laufzeitlimit?
            if ($cfg->maxRuntimeSeconds > 0 && (time() - $start) >= $cfg->maxRuntimeSeconds) {
                $io->success(sprintf('Stopped (runtime limit). Dispatched=%d', $dispatched));
                return Command::SUCCESS;
            }

            // Mengenlimit?
            if ($cfg->maxMessagesTotal > 0 && $dispatched >= $cfg->maxMessagesTotal) {
                $io->success(sprintf('Stopped (message limit). Dispatched=%d', $dispatched));
                return Command::SUCCESS;
            }

            $toDispatchThisTick = $cfg->batchSize;
            if ($cfg->maxMessagesTotal > 0) {
                $toDispatchThisTick = min($toDispatchThisTick, $cfg->maxMessagesTotal - $dispatched);
            }

            for ($i = 0; $i < $toDispatchThisTick; $i++) {
                $simulateMs = ($cfg->processingMaxMs > $cfg->processingMinMs)
                    ? random_int($cfg->processingMinMs, $cfg->processingMaxMs)
                    : $cfg->processingMinMs;

                $msg = new DemoJobMessage(
                    jobId: sprintf('demo-%d-%04d', time(), random_int(0, 9999)),
                    createdAtUnix: time(),
                    simulateMs: $simulateMs,
                    failRatePercent: $cfg->failRatePercent
                );

                // Transport-Routing: In TYPO3/Symfony wird i.d.R. über routing (messenger.yaml) entschieden.
                // Hier dispatchen wir einfach – routing übernimmt den Transport.
                $this->messageBus->dispatch($msg);
                $dispatched++;
            }

            $io->writeln(sprintf('[%s] Dispatched +%d (total=%d)', date('H:i:s'), $toDispatchThisTick, $dispatched));

            $sleep = $cfg->tickIntervalSeconds;
            if ($cfg->tickJitterSeconds > 0) {
                $sleep += random_int(0, $cfg->tickJitterSeconds);
            }
            sleep($sleep);
        }
    }
}
