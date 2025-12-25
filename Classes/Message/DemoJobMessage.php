<?php


declare(strict_types=1);

namespace WapplerSystems\MessengerDemo\Message;

final class DemoJobMessage
{
    public function __construct(
        public readonly string $jobId,
        public readonly int    $createdAtUnix,
        public readonly int    $simulateMs,
        public readonly int    $failRatePercent
    )
    {
    }
}
