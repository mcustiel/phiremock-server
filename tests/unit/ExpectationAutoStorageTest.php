<?php

use Mcustiel\Phiremock\Domain\Conditions;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Http\Body;
use Mcustiel\Phiremock\Domain\Http\StatusCode;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Domain\Options\Priority;
use Mcustiel\Phiremock\Server\Model\Implementation\ExpectationAutoStorage;
use PHPUnit\Framework\TestCase;

class ExpectationAutoStorageTest extends TestCase
{
    public function testItProvidesStablePriorityOrderedExpectations(): void
    {
        $storage = new ExpectationAutoStorage();
        $firstDefault = $this->expectation('first default');
        $low = $this->expectation('low', 1);
        $firstHigh = $this->expectation('first high', 10);
        $secondHigh = $this->expectation('second high', 10);

        $storage->addExpectation($firstDefault);
        $storage->addExpectation($low);
        $storage->addExpectation($firstHigh);
        $storage->addExpectation($secondHigh);

        $this->assertSame(
            [$firstHigh, $secondHigh, $low, $firstDefault],
            $storage->listExpectations()
        );
    }

    private function expectation(string $body, ?int $priority = null): Expectation
    {
        return new Expectation(
            new Conditions(),
            new HttpResponse(new StatusCode(200), new Body($body)),
            null,
            $priority === null ? null : new Priority($priority)
        );
    }
}
