<?php


declare(strict_types=1);

namespace WapplerSystems\MessengerDemo\MessageHandler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use WapplerSystems\MessengerDemo\Message\DemoJobMessage;

#[AsMessageHandler]
final class DemoJobMessageHandler
{
    public function __construct(
        private readonly LoggerInterface $logger
    )
    {
    }

    public function __invoke(DemoJobMessage $message): void
    {
        // Simulierte Arbeit
        if ($message->simulateMs > 0) {
            usleep($message->simulateMs * 1000);
        }

        // Zufallsfehler
        $roll = random_int(1, 100);
        if ($roll <= $message->failRatePercent) {
            $this->logger->warning(
                'DemoJob failed intentionally',
                ['jobId' => $message->jobId, 'roll' => $roll, 'failRate' => $message->failRatePercent]
            );

            throw new \RuntimeException('Intentional demo failure for jobId=' . $message->jobId);
        }

        $this->logger->info(
            'DemoJob processed',
            ['jobId' => $message->jobId, 'simulateMs' => $message->simulateMs]
        );
    }
}
