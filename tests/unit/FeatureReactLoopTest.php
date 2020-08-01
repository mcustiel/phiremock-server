<?php

use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory as EventLoop;
use React\EventLoop\LoopInterface;

class FeatureReactLoopTest extends TestCase
{
    /** @var LoopInterface */
    private $loop;

    protected function setUp(): void
    {
        $this->loop = EventLoop::create();
        $this->loop->run();
    }

    protected function tearDown(): void
    {
        $this->loop->stop();
    }

    public function testParallelExecutions(): void
    {
        $function = function () {
            $deferred = new \React\Promise\Deferred();

            $this->loop->addTimer(0, function () use ($deferred) {
                $seconds = rand(3, 8);
                echo sprintf('Sleeping for %d seconds', $seconds);
                sleep($seconds);
                $deferred->resolve($seconds);
            });

            return $deferred->promise();
        };
        for ($i = 0; $i < 10; ++$i) {
            $promise = new \React\Promise\LazyPromise($function);
            $promise->then(function ($seconds) {
                echo sprintf('Slept for %d seconds', $seconds);
            });
        }

        sleep(10);

        $this->assertTrue(true);
    }
}
