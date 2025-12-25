<?php

declare(strict_types=1);

namespace WapplerSystems\MessengerDemo\Configuration;

final class ExtensionConfiguration
{
    public const EXTKEY = 'messenger_demo';

    // Wie viele Messages pro "Tick"
    public int $batchSize = 5;

    // Intervall (Sekunden) zwischen Ticks
    public int $tickIntervalSeconds = 10;

    // Optional: Jitter (zufällige Streuung) pro Tick in Sekunden (0..jitter)
    public int $tickJitterSeconds = 2;

    // Verarbeitungssimulation (Sleep) pro Message (Min/Max in Millisekunden)
    public int $processingMinMs = 150;
    public int $processingMaxMs = 800;

    // Fehlerquote in Prozent (0..100)
    public int $failRatePercent = 15;

    // Transportname (Messenger transport) – z.B. "async"
    public string $transportName = 'async';

    // Optional: wenn gesetzt, begrenzt der Command die Gesamtzahl erzeugter Messages
    public int $maxMessagesTotal = 0;

    // Optional: Laufzeitlimit des Commands (Sekunden) – 0 = unbegrenzt
    public int $maxRuntimeSeconds = 0;

    public static function fromArray(array $data): self
    {
        $self = new self();

        foreach ($data as $k => $v) {
            if (property_exists($self, (string)$k)) {
                $self->{$k} = is_string($v) ? trim($v) : $v;
            }
        }

        // einfache Normalisierung / Schutz
        $self->batchSize = max(1, (int)$self->batchSize);
        $self->tickIntervalSeconds = max(1, (int)$self->tickIntervalSeconds);
        $self->tickJitterSeconds = max(0, (int)$self->tickJitterSeconds);

        $self->processingMinMs = max(0, (int)$self->processingMinMs);
        $self->processingMaxMs = max($self->processingMinMs, (int)$self->processingMaxMs);

        $self->failRatePercent = min(100, max(0, (int)$self->failRatePercent));

        $self->maxMessagesTotal = max(0, (int)$self->maxMessagesTotal);
        $self->maxRuntimeSeconds = max(0, (int)$self->maxRuntimeSeconds);

        $self->transportName = $self->transportName !== '' ? $self->transportName : 'async';

        return $self;
    }
}
