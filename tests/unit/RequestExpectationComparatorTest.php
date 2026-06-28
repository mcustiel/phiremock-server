<?php

use Laminas\Diactoros\ServerRequest;
use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Domain\Condition\Conditions\JsonPathCondition;
use Mcustiel\Phiremock\Domain\Condition\Conditions\JsonPathConditionCollection;
use Mcustiel\Phiremock\Domain\Condition\Matchers\MatcherFactory;
use Mcustiel\Phiremock\Domain\Conditions;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Http\JsonPathName;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Server\Model\Implementation\ScenarioAutoStorage;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class RequestExpectationComparatorTest extends TestCase
{
    public function testJsonPathMatchesNullValues(): void
    {
        $comparator = $this->comparator();
        $request = $this->request('{"user":{"middleName":null}}');
        $expectation = $this->expectationWithJsonPath('user.middleName', null);

        $this->assertTrue($comparator->equals($request, $expectation));
    }

    public function testJsonPathMatchesKeysContainingDots(): void
    {
        $comparator = $this->comparator();
        $request = $this->request('{"user":{"profile.name":"Mariano"}}');
        $expectation = $this->expectationWithJsonPath('user.profile.name', 'Mariano');

        $this->assertTrue($comparator->equals($request, $expectation));
    }

    public function testJsonBodyIsDecodedForEachComparison(): void
    {
        $comparator = $this->comparator();
        $body = new CountingStringStream('{"user":{"name":"Mariano"}}');
        $request = new ServerRequest([], [], '/json', 'POST', $body);

        $this->assertTrue($comparator->equals($request, $this->expectationWithJsonPath('user.name', 'Mariano')));
        $this->assertTrue($comparator->equals($request, $this->expectationWithJsonPath('user.name', 'Mariano')));
        $this->assertSame(2, $body->stringCasts);
    }

    private function comparator(): RequestExpectationComparator
    {
        return new RequestExpectationComparator(new ScenarioAutoStorage(), new NullLogger());
    }

    private function request(string $body): ServerRequest
    {
        return new ServerRequest([], [], '/json', 'POST', new StringStream($body));
    }

    private function expectationWithJsonPath(string $path, $expectedValue): Expectation
    {
        $jsonPath = new JsonPathConditionCollection();
        $jsonPath->setPathCondition(
            new JsonPathName($path),
            new JsonPathCondition(MatcherFactory::equalsTo($expectedValue))
        );

        return new Expectation(
            new Conditions(null, null, null, null, null, null, $jsonPath->iterator()),
            new HttpResponse()
        );
    }
}

class CountingStringStream extends StringStream
{
    public int $stringCasts = 0;

    public function __toString(): string
    {
        ++$this->stringCasts;

        return parent::__toString();
    }
}
