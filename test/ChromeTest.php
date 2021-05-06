<?php

namespace SynergiTech\ChromePDF\Test;

use Symfony\Component\Process\Process;
use SynergiTech\ChromePDF\Chrome;
use SynergiTech\ChromePDF\Test\TestCase;

class ChromeTest extends TestCase
{
    private function getMockedProcess()
    {
        $proc = $this->createMock(Process::class);
        $proc->expects($this->once())
            ->method('mustRun')
            ->will($this->returnSelf());
        return $proc;
    }

    private function getMockedPDF()
    {
        return $this->getMockBuilder(Chrome::class)
            ->setMethods(['createProcess'])
            ->getMock();
    }

    public function test_callsDefaultBinary()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->once())
            ->method('createProcess')
            ->with($this->containsEqual('chrome-pdf'))
            ->willReturn($this->getMockedProcess());

        $pdf->renderContent('test');
    }

    public function test_callsSpecifiedBinary()
    {
        $pdf = $this->getMockBuilder(Chrome::class)
            ->setConstructorArgs(['test-pdf-binary'])
            ->setMethods(['createProcess'])
            ->getMock();

        $pdf->expects($this->once())
            ->method('createProcess')
            ->with($this->containsEqual('test-pdf-binary'))
            ->willReturn($this->getMockedProcess());

        $pdf->renderContent('test');
    }

    public function test_createProcessFactory()
    {
        $pdf = new Chrome();
        $this->assertInstanceOf(Process::class, $pdf->createProcess([]));

        $pdf = new Chrome('', TestProcessRunner::class);
        $this->assertInstanceOf(TestProcessRunner::class, $pdf->createProcess([]));
    }

    public function test_sandboxOption()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(3))
            ->method('createProcess')
            ->withConsecutive(
                [
                    $this->logicalAnd(
                        $this->logicalNot($this->containsEqual('--sandbox')),
                        $this->logicalNot($this->containsEqual('--no-sandbox'))
                    )
                ],
                [ $this->containsEqual('--sandbox') ],
                [ $this->containsEqual('--no-sandbox') ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');
        $pdf->setSandbox(true);
        $pdf->renderContent('test');
        $pdf->setSandbox(false);
        $pdf->renderContent('test');
    }

    public function test_mediaEmulation()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(3))
            ->method('createProcess')
            ->withConsecutive(
                [
                    $this->logicalNot($this->containsEqual('--emulateMedia')),
                ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--emulateMedia'),
                        $this->containsEqual('screen')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--emulateMedia'),
                        $this->containsEqual('print')
                    )
                ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');
        $pdf->setMediaEmulation('screen');
        $pdf->renderContent('test');
        $pdf->setMediaEmulation('print');
        $pdf->renderContent('test');
    }

    public function test_landscape()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(3))
            ->method('createProcess')
            ->withConsecutive(
                [
                    $this->logicalAnd(
                        $this->logicalNot($this->containsEqual('--landscape')),
                        $this->logicalNot($this->containsEqual('--no-landscape'))
                    )
                ],
                [ $this->containsEqual('--landscape') ],
                [ $this->containsEqual('--no-landscape') ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');
        $pdf->setLandscape(true);
        $pdf->renderContent('test');
        $pdf->setLandscape(false);
        $pdf->renderContent('test');
    }

    public function test_scale()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(3))
            ->method('createProcess')
            ->withConsecutive(
                [ $this->logicalNot($this->containsEqual('--scale')) ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--scale'),
                        $this->containsEqual('3')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--scale'),
                        $this->containsEqual('0.6')
                    )
                ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');
        $pdf->setScale(3);
        $pdf->renderContent('test');
        $pdf->setScale(0.6);
        $pdf->renderContent('test');
    }

    public function test_displayHeaderFooter()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(6))
            ->method('createProcess')
            ->withConsecutive(
                [
                    $this->logicalAnd(
                        $this->logicalNot($this->containsEqual('--displayHeaderFooter')),
                        $this->logicalNot($this->containsEqual('--no-displayHeaderFooter'))
                    )
                ],
                [ $this->containsEqual('--displayHeaderFooter') ],
                [ $this->containsEqual('--no-displayHeaderFooter') ],
                [ $this->containsEqual('--displayHeaderFooter') ],
                [ $this->containsEqual('--displayHeaderFooter') ],
                [ $this->containsEqual('--no-displayHeaderFooter') ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        // should not set any display header footer flags
        $pdf->renderContent('test');
        $pdf->setDisplayHeaderFooter(true);
        // should now enable it
        $pdf->renderContent('test');
        $pdf->setDisplayHeaderFooter(false);
        // should now disable it
        $pdf->renderContent('test');
        $pdf->setHeader('test');
        $pdf->setFooter('test');
        // should now enable it
        $pdf->renderContent('test');
        $pdf->setHeader(null);
        // should still be enabled
        $pdf->renderContent('test');
        $pdf->setFooter(null);
        // should now be disabled
        $pdf->renderContent('test');
    }

    public function test_header()
    {
        $pdfBuilder = $this->getMockBuilder(Chrome::class)
            ->setMethods(['createProcess']);

        $pdf = $pdfBuilder->getMock();
        $pdf->expects($this->exactly(2))
            ->method('createProcess')
            ->with(
                $this->logicalAnd(
                    $this->logicalNot($this->containsEqual('--displayHeaderFooter')),
                    $this->logicalNot($this->containsEqual('--headerTemplate'))
                )
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');
        $pdf->setHeader('header-test-1');
        $pdf->setHeader(null);
        $pdf->renderContent('test');

        $pdf = $pdfBuilder->getMock();
        $pdf->expects($this->once())
            ->method('createProcess')
            // I don't think we can use withConsecutive here because of
            // https://github.com/sebastianbergmann/phpunit/issues/3590
            ->with(
                $this->logicalAnd(
                    $this->containsEqual('--displayHeaderFooter'),
                    $this->containsEqual('--headerTemplate'),
                    $this->callback(function ($args) {
                        $key = array_search('--headerTemplate', $args) + 1;
                        $tempFile = $args[$key];
                        $this->stringEndsWith('.html')->evaluate($tempFile);

                        return file_get_contents($tempFile) === 'header-test-2';
                    })
                )
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->setHeader('header-test-2');
        $pdf->renderContent('test');
    }

    public function test_footer()
    {
        $pdfBuilder = $this->getMockBuilder(Chrome::class)
            ->setMethods(['createProcess']);

        $pdf = $pdfBuilder->getMock();
        $pdf->expects($this->exactly(2))
            ->method('createProcess')
            ->with(
                $this->logicalAnd(
                    $this->logicalNot($this->containsEqual('--displayHeaderFooter')),
                    $this->logicalNot($this->containsEqual('--headerTemplate'))
                )
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');
        $pdf->setFooter('footer-test-1');
        $pdf->setFooter(null);
        $pdf->renderContent('test');

        $pdf = $pdfBuilder->getMock();
        $pdf->expects($this->once())
            ->method('createProcess')
            // I don't think we can use withConsecutive here because of
            // https://github.com/sebastianbergmann/phpunit/issues/3590
            ->with(
                $this->logicalAnd(
                    $this->containsEqual('--displayHeaderFooter'),
                    $this->containsEqual('--footerTemplate'),
                    $this->callback(function ($args) {
                        $key = array_search('--footerTemplate', $args) + 1;
                        $tempFile = $args[$key];
                        $this->stringEndsWith('.html')->evaluate($tempFile);

                        return file_get_contents($tempFile) === 'footer-test-2';
                    })
                )
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->setFooter('footer-test-2');
        $pdf->renderContent('test');
    }

    public function test_printBackground()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(3))
            ->method('createProcess')
            ->withConsecutive(
                [ $this->containsEqual('--printBackground') ],
                [ $this->containsEqual('--no-printBackground') ],
                [ $this->containsEqual('--printBackground') ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');
        $pdf->setPrintBackground(false);
        $pdf->renderContent('test');
        $pdf->setPrintBackground(true);
        $pdf->renderContent('test');
    }

    public function test_pageRanges()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(3))
            ->method('createProcess')
            ->withConsecutive(
                [ $this->logicalNot($this->containsEqual('--pageRanges')) ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--pageRanges'),
                        $this->containsEqual('1,2,5-7,9')
                    )
                ],
                [ $this->logicalNot($this->containsEqual('--pageRanges')) ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');
        $pdf->setPageRanges('1,2,5-7,9');
        $pdf->renderContent('test');
        $pdf->setPageRanges(null);
        $pdf->renderContent('test');
    }

    public function test_width()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(4))
            ->method('createProcess')
            ->withConsecutive(
                [ $this->logicalNot($this->containsEqual('--width')) ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--width'),
                        $this->containsEqual('100')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--width'),
                        $this->containsEqual('56cm')
                    )
                ],
                [ $this->logicalNot($this->containsEqual('--width')) ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');

        $pdf->setWidth(100);
        $pdf->renderContent('test');

        $pdf->setWidth('56cm');
        $pdf->renderContent('test');

        $pdf->setWidth(null);
        $pdf->renderContent('test');
    }

    public function test_height()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(4))
            ->method('createProcess')
            ->withConsecutive(
                [ $this->logicalNot($this->containsEqual('--height')) ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--height'),
                        $this->containsEqual('100')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--height'),
                        $this->containsEqual('56cm')
                    )
                ],
                [ $this->logicalNot($this->containsEqual('--height')) ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');

        $pdf->setHeight(100);
        $pdf->renderContent('test');

        $pdf->setHeight('56cm');
        $pdf->renderContent('test');

        $pdf->setHeight(null);
        $pdf->renderContent('test');
    }

    public function test_preferCSSPageSize()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(3))
            ->method('createProcess')
            ->withConsecutive(
                [ $this->logicalNot($this->containsEqual('--preferCSSPageSize')) ],
                [ $this->containsEqual('--preferCSSPageSize') ],
                [ $this->logicalNot($this->containsEqual('--preferCSSPageSize')) ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');

        $pdf->setpreferCSSPageSize(true);
        $pdf->renderContent('test');

        $pdf->setpreferCSSPageSize(false);
        $pdf->renderContent('test');
    }

    public function test_waitUntil()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(3))
            ->method('createProcess')
            ->withConsecutive(
                [ $this->logicalNot($this->containsEqual('--waitUntil')) ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--waitUntil'),
                        $this->containsEqual('domcontentloaded')
                    )
                ],
                [ $this->logicalNot($this->containsEqual('--waitUntil')) ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');

        $pdf->setWaitUntil('domcontentloaded');
        $pdf->renderContent('test');

        $pdf->setWaitUntil(null);
        $pdf->renderContent('test');
    }

    public function test_margins()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(6))
            ->method('createProcess')
            ->withConsecutive(
                [ $this->logicalNot($this->containsEqual('--margin')) ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--margin'),
                        $this->containsEqual('0,0,0,0')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--margin'),
                        $this->containsEqual('20px,20px,20px,20px')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--margin'),
                        $this->containsEqual('1px,2px,1px,2px')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--margin'),
                        $this->containsEqual('5px,6px,7px,6px')
                    )
                ],
                [ $this->logicalNot($this->containsEqual('--margin')) ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');

        $pdf->setMargin('0', '0', '0', '0');
        $pdf->renderContent('test');

        $pdf->setMargin('20px');
        $pdf->renderContent('test');

        $pdf->setMargin('1px', '2px');
        $pdf->renderContent('test');

        $pdf->setMargin('5px', '6px', '7px');
        $pdf->renderContent('test');

        $pdf->setMargin(null);
        $pdf->renderContent('test');
    }

    public function test_format()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(2))
            ->method('createProcess')
            ->withConsecutive(
                [
                    $this->logicalAnd(
                        $this->containsEqual('--format'),
                        $this->containsEqual('A4')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->containsEqual('--format'),
                        $this->containsEqual('Letter')
                    )
                ]
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $pdf->renderContent('test');

        $pdf->setFormat('Letter');
        $pdf->renderContent('test');
    }

    public function test_renderContent()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->once())
            ->method('createProcess')
            ->with(
                $this->logicalAnd(
                    $this->containsEqual('--file'),
                    $this->callback(function ($args) {
                        $key = array_search('--file', $args) + 1;
                        $tempFile = $args[$key];

                        return file_get_contents($tempFile) === 'renderContent';
                    })
                )
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $stream = $pdf->renderContent('renderContent');
        $this->assertIsResource($stream);

        $meta = stream_get_meta_data($stream);
        $this->assertEquals('r', $meta['mode']);
    }

    public function test_renderURL()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->once())
            ->method('createProcess')
            ->with(
                $this->logicalAnd(
                    $this->containsEqual('--page'),
                    $this->containsEqual('https://bbc.co.uk')
                )
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $stream = $pdf->renderURL('https://bbc.co.uk');
        $this->assertIsResource($stream);

        $meta = stream_get_meta_data($stream);
        $this->assertEquals('r', $meta['mode']);
    }

    public function test_renderFile()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->once())
            ->method('createProcess')
            ->with(
                $this->logicalAnd(
                    $this->containsEqual('--file'),
                    $this->containsEqual('/any/file')
                )
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $stream = $pdf->renderFile('/any/file');
        $this->assertIsResource($stream);

        $meta = stream_get_meta_data($stream);
        $this->assertEquals('r', $meta['mode']);
    }
}
