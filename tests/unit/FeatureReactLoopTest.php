<?php

use PHPUnit\Framework\TestCase;
use React\EventLoop\StreamSelectLoop;
use React\Promise\Deferred;

class FeatureReactLoopTest extends TestCase
{
    public function testTimersResolveWithoutBlockingTheLoop(): void
    {
        $loop = new StreamSelectLoop();
        $resolved = [];

        foreach ([0.03, 0.01, 0.02] as $delay) {
            $deferred = new Deferred();
            $loop->addTimer($delay, function () use ($deferred, $delay) {
                $deferred->resolve($delay);
            });
            $deferred->promise()->then(function ($delay) use (&$resolved) {
                $resolved[] = $delay;
            });
        }

        $loop->addTimer(0.05, function () use ($loop) {
            $loop->stop();
        });
        $loop->run();

        $this->assertSame([0.01, 0.02, 0.03], $resolved);
    }
}
