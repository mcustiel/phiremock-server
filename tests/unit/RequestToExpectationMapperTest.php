<?php

use Laminas\Diactoros\ServerRequest;
use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Common\Utils\ArrayToExpectationConverter;
use Mcustiel\Phiremock\Common\Utils\ArrayToExpectationConverterLocator;
use Mcustiel\Phiremock\Domain\Conditions;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Server\Utils\RequestToExpectationMapper;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class RequestToExpectationMapperTest extends TestCase
{
    public function testItDecodesBase64EncodedExpectationPayload(): void
    {
        $expectationArray = [
            'request'  => ['url' => ['isEqualTo' => '/binary']],
            'response' => ['statusCode' => 200],
        ];
        $expectation = new Expectation(new Conditions(), new HttpResponse());
        $converter = $this->createMock(ArrayToExpectationConverter::class);
        $converter->expects($this->once())
            ->method('convert')
            ->with($expectationArray)
            ->willReturn($expectation);
        $locator = $this->getMockBuilder(ArrayToExpectationConverterLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['locate'])
            ->getMock();
        $locator->expects($this->once())
            ->method('locate')
            ->with($expectationArray)
            ->willReturn($converter);
        $mapper = new RequestToExpectationMapper($locator, new NullLogger());
        $encodedExpectation = json_encode($expectationArray);
        $this->assertIsString($encodedExpectation);
        $request = new ServerRequest(
            [],
            [],
            '/__phiremock/expectations',
            'POST',
            new StringStream(base64_encode($encodedExpectation)),
            [RequestToExpectationMapper::CONTENT_ENCODING_HEADER => 'base64']
        );

        $this->assertSame($expectation, $mapper->map($request));
    }
}
