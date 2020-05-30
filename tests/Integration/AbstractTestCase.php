<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCollection\Tests\Integration;

use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    private float $startTime = 0.0;
    private float $endTime = 0.0;

    private int $peakMemoryBefore = 0;
    private int $peakMemoryAfter = 0;

    protected function start(): void
    {
        $this->startTime = microtime(true);
    }

    protected function stop(): void
    {
        $this->endTime = microtime(true);
    }

    protected function getElapsedSeconds(): float
    {
        return $this->endTime - $this->startTime;
    }

    protected function sampleMemoryBefore(): void
    {
        $this->peakMemoryBefore = memory_get_peak_usage(true);
    }

    protected function sampleMemoryAfter(): void
    {
        $this->peakMemoryAfter = memory_get_peak_usage(true);
    }

    protected function getMemoryIncreaseInMB(): float
    {
        return ($this->peakMemoryAfter - $this->peakMemoryBefore) / 1024 / 1024;
    }
}
