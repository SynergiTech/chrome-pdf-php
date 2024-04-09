<?php

namespace SynergiTech\ChromePDF\Test\Browserless;

use GuzzleHttp\Psr7\Response;

use SynergiTech\ChromePDF\Browserless;
use SynergiTech\ChromePDF\Browserless\Screenshot;
use SynergiTech\ChromePDF\Chrome;
use SynergiTech\ChromePDF\Test\TestCase;

class ScreenshotTest extends TestCase
{
    private function getMockedClient()
    {
        return $this->getMockBuilder(Chrome::class)
            ->setMethods(['post'])
            ->getMock();
    }

    public function test_render()
    {
        $client = $this->getMockedClient();

        $client->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->hasKeyValue(['json', 'url'], $this->identicalTo('test')))
            ->willReturn(new Response(200, [], 'screenshot'));

        $bl = new Screenshot('', $client);
        $stream = $bl->render('test');

        $this->assertIsResource($stream);
        $this->assertSame('screenshot', fgets($stream));
    }
}
