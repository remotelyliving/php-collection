<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCollection\Tests\Integration;

use RemotelyLiving\PHPCollection\Collection;

class PerformanceTest extends AbstractTestCase
{
    public function testOperatesEfficientlyOverLargeSetsOfData(): void
    {
        $this->sampleMemoryBefore();
        $this->start();
        ;

        $random = Collection::fill(0, 100000, 'hey hey')
            ->each(fn($val) => random_bytes(32) . $val)
            ->filter(fn($val) => !mb_strpos($val, 'g'))
            ->remove(100, 200, 300, 400)
            ->unique()
            ->reverse()
            ->rand();

        $this->sampleMemoryAfter();
        $this->stop();

        $this->assertStringContainsString('hey hey', $random);
    }

    public function testCreatingFromGeneratorDoesNothingYet(): void
    {
        $fill = array_fill(0, 100000, 'hey hey');
        $generator = function () use ($fill) {
            foreach ($fill as $value) {
                yield $value;
            }
        };

        $this->sampleMemoryBefore();
        $this->start();
        ;

        Collection::later($generator());

        $this->sampleMemoryAfter();
        $this->stop();

        $this->assertLessThan(1, $this->getMemoryIncreaseInMB());
        $this->assertLessThan(1, $this->getElapsedSeconds());
    }
}
