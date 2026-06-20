<?php

use Laminas\Diactoros\ServerRequest;
use Mcustiel\Phiremock\Server\Actions\ActionLocator;
use Mcustiel\Phiremock\Server\Actions\GuiAction;
use Mcustiel\Phiremock\Server\Http\Implementation\FastRouterHandler;
use Mcustiel\Phiremock\Server\Utils\Config\Config;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class GuiActionTest extends TestCase
{
    public function testGuiEndpointRendersHtml(): void
    {
        $locator = $this->getMockBuilder(ActionLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['locate'])
            ->getMock();
        $locator->expects($this->once())
            ->method('locate')
            ->with(ActionLocator::GUI)
            ->willReturn(new GuiAction());

        $router = new FastRouterHandler($locator, $this->createConfig(), new NullLogger());
        $response = $router->dispatch(new ServerRequest([], [], '/__phiremock/gui', 'GET'));
        $body = (string) $response->getBody();
        $resourceHtml = file_get_contents(dirname(__DIR__, 2) . '/resources/gui.html');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertNotFalse($resourceHtml);
        $this->assertSame($resourceHtml, $body);
        $this->assertStringContainsString('Create an expectation', $body);
        $this->assertStringContainsString('Delete expectations', $body);
        $this->assertStringContainsString('List expectations', $body);
        $this->assertStringContainsString('Search executed requests', $body);
        $this->assertStringContainsString('/__phiremock/expectations', $body);
        $this->assertStringContainsString('/__phiremock/executions', $body);
        $this->assertStringContainsString('<div class="output-list" id="list-output">', $body);
        $this->assertStringNotContainsString('<pre class="output" id="list-output">', $body);
        $this->assertStringContainsString('<div class="output-list" id="requests-output">', $body);
        $this->assertStringNotContainsString('<pre class="output" id="requests-output">', $body);
    }

    private function createConfig(): Config
    {
        return new Config([
            Config::IP               => '127.0.0.1',
            Config::PORT             => 8086,
            Config::DEBUG            => true,
            Config::EXPECTATIONS_DIR => sys_get_temp_dir(),
            Config::FACTORY_CLASS    => '',
        ]);
    }
}
