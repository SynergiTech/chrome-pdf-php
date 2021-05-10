<?php

namespace SynergiTech\ChromePDF\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use SynergiTech\ChromePDF\Browserless;
use SynergiTech\ChromePDF\Browserless\APIException;
use SynergiTech\ChromePDF\Chrome;
use SynergiTech\ChromePDF\Test\TestCase;

use PHPUnit\Framework\Constraint\ArraySubset;

class BrowserlessTest extends TestCase
{
    private function getMockedClient()
    {
        return $this->getMockBuilder(Chrome::class)
            ->setMethods(['post'])
            ->getMock();
    }

    public function test_rotation()
    {
        $client = $this->getMockedClient();

        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->hasKeyValue(['json', 'rotate'], $this->identicalTo(90))
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);

        $this->assertNull($bl->getRotation());

        $bl->setRotation(90);
        $this->assertSame(90, $bl->getRotation());

        $bl->renderContent('test');

        $bl->setRotation(null);
        $this->assertNull($bl->getRotation());
    }

    public function test_safeMode()
    {
        $client = $this->getMockedClient();

        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->hasKeyValue(['json', 'safeMode'], $this->isTrue())
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);

        $this->assertFalse($bl->getSafeMode());

        $bl->setSafeMode(true);
        $bl->renderContent('test');
    }

    public function test_timeout()
    {
        $client = $this->getMockedClient();

        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                $this->hasKeyValue(['json', 'gotoOptions', 'timeout'], $this->identicalTo(7000))
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);

        $this->assertNull($bl->getTimeout());

        $bl->setTimeout(7000);
        $bl->renderContent('test');
        $this->assertSame(7000, $bl->getTimeout());
    }

    public function test_displayHeaderFooter()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(6))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'displayHeaderFooter']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'displayHeaderFooter'], $this->isTrue())
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'displayHeaderFooter'], $this->isFalse())
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'displayHeaderFooter'], $this->isTrue())
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'displayHeaderFooter'], $this->isTrue())
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'displayHeaderFooter'], $this->isFalse())
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setDisplayHeaderFooter(true);
        $bl->renderContent('test');

        $bl->setDisplayHeaderFooter(false);
        $bl->renderContent('test');

        $bl->setHeader('test');
        $bl->setFooter('test');
        $bl->renderContent('test');

        $bl->setHeader(null);
        $bl->renderContent('test');

        $bl->setFooter(null);
        $bl->renderContent('test');
    }

    public function test_header()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(2))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'headerTemplate']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'headerTemplate'], $this->identicalTo('header-test'))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setHeader('header-test');
        $bl->renderContent('test');
    }

    public function test_footer()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(2))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'footerTemplate']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'footerTemplate'], $this->identicalTo('footer-test'))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setFooter('footer-test');
        $bl->renderContent('test');
    }

    public function test_format()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(2))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'format'], $this->identicalTo('A4'))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'format'], $this->identicalTo('Letter'))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setFormat('Letter');
        $bl->renderContent('test');
    }

    public function test_width()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(3))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'width']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'width'], $this->identicalTo('100px'))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'width'], $this->identicalTo(20))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setWidth('100px');
        $bl->renderContent('test');

        $bl->setWidth(20);
        $bl->renderContent('test');
    }

    public function test_height()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(3))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'height']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'height'], $this->identicalTo('100px'))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'height'], $this->identicalTo(20))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setHeight('100px');
        $bl->renderContent('test');

        $bl->setHeight(20);
        $bl->renderContent('test');
    }

    public function test_landscape()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(3))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'landscape']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'landscape'], $this->isTrue())
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'landscape'], $this->isFalse())
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setLandscape(true);
        $bl->renderContent('test');

        $bl->setLandscape(false);
        $bl->renderContent('test');
    }

    public function test_margin()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(6))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'margin']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(
                        ['json', 'options', 'margin'],
                        $this->equalTo([
                            'top' => '20px',
                            'right' => '20px',
                            'bottom' => '20px',
                            'left' => '20px',
                        ])
                    )
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(
                        ['json', 'options', 'margin'],
                        $this->equalTo([
                            'top' => '1px',
                            'bottom' => '1px',
                            'left' => '2px',
                            'right' => '2px',
                        ])
                    )
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(
                        ['json', 'options', 'margin'],
                        $this->equalTo([
                            'top' => '5px',
                            'right' => '6px',
                            'bottom' => '7px',
                            'left' => '6px',
                        ])
                    )
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(
                        ['json', 'options', 'margin'],
                        $this->equalTo([
                            'top' => '1',
                            'right' => '2',
                            'bottom' => '3',
                            'left' => '4',
                        ])
                    )
                ],
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'margin']))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setMargin('20px');
        $bl->renderContent('test');

        $bl->setMargin('1px', '2px');
        $bl->renderContent('test');

        $bl->setMargin('5px', '6px', '7px');
        $bl->renderContent('test');

        $bl->setMargin('1', '2', '3', '4');
        $bl->renderContent('test');

        $bl->setMargin(null);
        $bl->renderContent('test');
    }

    public function test_pageRanges()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(3))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'pageRanges']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'pageRanges'], $this->identicalTo('2,5-7'))
                ],
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'pageRanges']))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setPageRanges('2,5-7');
        $bl->renderContent('test');

        $bl->setPageRanges(null);
        $bl->renderContent('test');
    }

    public function test_preferCSSPageSize()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(4))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'preferCSSPageSize']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'preferCSSPageSize'], $this->isTrue())
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'preferCSSPageSize'], $this->isFalse())
                ],
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'preferCSSPageSize']))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setPreferCSSPageSize(true);
        $bl->renderContent('test');

        $bl->setPreferCSSPageSize(false);
        $bl->renderContent('test');

        $bl->setPreferCSSPageSize(null);
        $bl->renderContent('test');
    }

    public function test_printBackground()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(3))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'printBackground'], $this->isTrue())
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'printBackground'], $this->isTrue())
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'printBackground'], $this->isFalse())
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setPrintBackground(true);
        $bl->renderContent('test');

        $bl->setPrintBackground(false);
        $bl->renderContent('test');
    }

    public function test_scale()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(3))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'scale']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'options', 'scale'], $this->identicalTo(2.0))
                ],
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'options', 'scale']))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setScale(2);
        $bl->renderContent('test');

        $bl->setScale(null);
        $bl->renderContent('test');
    }

    public function test_waitUntil()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(3))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'gotoOptions']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'gotoOptions', 'waitUntil'], $this->identicalTo('domcontentloaded'))
                ],
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'gotoOptions', 'waitUntil']))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setWaitUntil('domcontentloaded');
        $bl->renderContent('test');

        $bl->setWaitUntil(null);
        $bl->renderContent('test');
    }

    public function test_mediaEmulation()
    {
        $client = $this->getMockedClient();

        $client->expects($this->exactly(3))
            ->method('post')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'emulateMedia']))
                ],
                [
                    $this->anything(),
                    $this->hasKeyValue(['json', 'emulateMedia'], $this->identicalTo('screen'))
                ],
                [
                    $this->anything(),
                    $this->logicalNot($this->hasKeyValue(['json', 'emulateMedia']))
                ]
            )
            ->willReturn(new Response());

        $bl = new Browserless('', $client);
        $bl->renderContent('test');

        $bl->setMediaEmulation('screen');
        $bl->renderContent('test');

        $bl->setMediaEmulation(null);
        $bl->renderContent('test');
    }

    public function test_renderContent()
    {
        $client = $this->getMockedClient();

        $client->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->hasKeyValue(['json', 'html'], $this->identicalTo('test')))
            ->willReturn(new Response(200, [], 'rendered-pdf'));

        $bl = new Browserless('', $client);
        $stream = $bl->renderContent('test');

        $this->assertIsResource($stream);
        $this->assertSame('rendered-pdf', fgets($stream));
    }

    public function test_renderURL()
    {
        $client = $this->getMockedClient();

        $client->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->hasKeyValue(['json', 'url'], $this->identicalTo('https://bbc.co.uk')))
            ->willReturn(new Response(200, [], 'rendered-pdf'));

        $bl = new Browserless('', $client);
        $stream = $bl->renderURL('https://bbc.co.uk');

        $this->assertIsResource($stream);
        $this->assertSame('rendered-pdf', fgets($stream));
    }

    public function test_renderFile()
    {
        $client = $this->getMockedClient();

        $client->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->hasKeyValue(['json', 'html'], $this->identicalTo('test file')))
            ->willReturn(new Response(200, [], 'rendered-pdf'));

        $tmpfile = tmpfile();
        fwrite($tmpfile, 'test file');
        $path = stream_get_meta_data($tmpfile)['uri'];

        $bl = new Browserless('', $client);
        $stream = $bl->renderFile($path);

        $this->assertIsResource($stream);
        $this->assertSame('rendered-pdf', fgets($stream));
    }

    public function test_plainError()
    {
        $this->expectException(APIException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessageMatches('/Failed to render PDF/');
        $this->expectExceptionMessageMatches('/node-pdftk/');

        $mock = new MockHandler([
            new Response(400, [], "The module 'node-pdftk' is not whitelisted in VM."),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $bl = new Browserless('', $client);
        try {
            $stream = $bl->renderContent('');
        } catch (APIException $e) {
            $this->assertInstanceOf(ClientException::class, $e->getPrevious());
            throw $e;
        }
    }

    public function test_jsonError()
    {
        $this->expectException(APIException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessageMatches('/Failed to render PDF/');
        $this->expectExceptionMessageMatches('/"rotate" must be one of/');

        $mock = new MockHandler([
            new Response(400, [], '[{"message":"\"rotate\" must be one of [90, -90, 180]","path":["rotate"],"type":"any.allowOnly","context":{"value":-1000,"valids":[90,-90,180],"key":"rotate","label":"rotate"}}]'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $bl = new Browserless('', $client);
        try {
            $stream = $bl->renderContent('');
        } catch (APIException $e) {
            $this->assertInstanceOf(ClientException::class, $e->getPrevious());
            throw $e;
        }
    }

    public function test_miscError()
    {
        $this->expectException(APIException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessageMatches('/Failed to render PDF/');
        $this->expectExceptionMessageMatches('/Network error/');

        $mock = new MockHandler([
            new RequestException("Network error", new Request('GET', 'test'))

        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $bl = new Browserless('', $client);
        try {
            $stream = $bl->renderContent('');
        } catch (APIException $e) {
            $this->assertInstanceOf(RequestException::class, $e->getPrevious());
            throw $e;
        }
    }
}
