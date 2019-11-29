<?php

namespace SynergiTech\ChromePDF\Test;

use Symfony\Component\Process\Process;
use SynergiTech\ChromePDF\Chrome;
use SynergiTech\ChromePDF\Test\TestCase;

class ChromeTest extends TestCase
{
    private function getMockedProcess()
    {
        $proc = $this->createMock(Process::class, ['mustRun']);
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
            ->with($this->contains('chrome-pdf'))
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
            ->with($this->contains('test-pdf-binary'))
            ->willReturn($this->getMockedProcess());

        $pdf->renderContent('test');
    }

    public function test_createProcessFactory()
    {
        $pdf = new Chrome();
        $this->assertInstanceOf(Process::class, $pdf->createProcess(''));

        $pdf = new Chrome('', TestProcessRunner::class);
        $this->assertInstanceOf(TestProcessRunner::class, $pdf->createProcess(''));
    }

    public function test_sandboxOption()
    {
        $pdf = $this->getMockedPDF();

        $pdf->expects($this->exactly(3))
            ->method('createProcess')
            ->withConsecutive(
                [
                    $this->logicalAnd(
                        $this->logicalNot($this->contains('--sandbox')),
                        $this->logicalNot($this->contains('--no-sandbox'))
                    )
                ],
                [ $this->contains('--sandbox') ],
                [ $this->contains('--no-sandbox') ]
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
                    $this->logicalNot($this->contains('--emulateMedia')),
                ],
                [
                    $this->logicalAnd(
                        $this->contains('--emulateMedia'),
                        $this->contains('screen')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->contains('--emulateMedia'),
                        $this->contains('print')
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
                        $this->logicalNot($this->contains('--landscape')),
                        $this->logicalNot($this->contains('--no-landscape'))
                    )
                ],
                [ $this->contains('--landscape') ],
                [ $this->contains('--no-landscape') ]
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
                [ $this->logicalNot($this->contains('--scale')) ],
                [
                    $this->logicalAnd(
                        $this->contains('--scale'),
                        $this->contains('3')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->contains('--scale'),
                        $this->contains('0.6')
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
                        $this->logicalNot($this->contains('--displayHeaderFooter')),
                        $this->logicalNot($this->contains('--no-displayHeaderFooter'))
                    )
                ],
                [ $this->contains('--displayHeaderFooter') ],
                [ $this->contains('--no-displayHeaderFooter') ],
                [ $this->contains('--displayHeaderFooter') ],
                [ $this->contains('--displayHeaderFooter') ],
                [ $this->contains('--no-displayHeaderFooter') ]
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
                    $this->logicalNot($this->contains('--displayHeaderFooter')),
                    $this->logicalNot($this->contains('--headerTemplate'))
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
                    $this->contains('--displayHeaderFooter'),
                    $this->contains('--headerTemplate'),
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
                    $this->logicalNot($this->contains('--displayHeaderFooter')),
                    $this->logicalNot($this->contains('--headerTemplate'))
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
                    $this->contains('--displayHeaderFooter'),
                    $this->contains('--footerTemplate'),
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
                [ $this->contains('--printBackground') ],
                [ $this->contains('--no-printBackground') ],
                [ $this->contains('--printBackground') ]
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
                [ $this->logicalNot($this->contains('--pageRanges')) ],
                [
                    $this->logicalAnd(
                        $this->contains('--pageRanges'),
                        $this->contains('1,2,5-7,9')
                    )
                ],
                [ $this->logicalNot($this->contains('--pageRanges')) ]
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
                [ $this->logicalNot($this->contains('--width')) ],
                [
                    $this->logicalAnd(
                        $this->contains('--width'),
                        $this->contains('100')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->contains('--width'),
                        $this->contains('56cm')
                    )
                ],
                [ $this->logicalNot($this->contains('--width')) ]
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
                [ $this->logicalNot($this->contains('--height')) ],
                [
                    $this->logicalAnd(
                        $this->contains('--height'),
                        $this->contains('100')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->contains('--height'),
                        $this->contains('56cm')
                    )
                ],
                [ $this->logicalNot($this->contains('--height')) ]
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
                [ $this->logicalNot($this->contains('--preferCSSPageSize')) ],
                [ $this->contains('--preferCSSPageSize') ],
                [ $this->logicalNot($this->contains('--preferCSSPageSize')) ]
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
                [ $this->logicalNot($this->contains('--waitUntil')) ],
                [
                    $this->logicalAnd(
                        $this->contains('--waitUntil'),
                        $this->contains('domcontentloaded')
                    )
                ],
                [ $this->logicalNot($this->contains('--waitUntil')) ]
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
                [ $this->logicalNot($this->contains('--margin')) ],
                [
                    $this->logicalAnd(
                        $this->contains('--margin'),
                        $this->contains('0,0,0,0')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->contains('--margin'),
                        $this->contains('20px,20px,20px,20px')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->contains('--margin'),
                        $this->contains('1px,2px,1px,2px')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->contains('--margin'),
                        $this->contains('5px,6px,7px,6px')
                    )
                ],
                [ $this->logicalNot($this->contains('--margin')) ]
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
                        $this->contains('--format'),
                        $this->contains('A4')
                    )
                ],
                [
                    $this->logicalAnd(
                        $this->contains('--format'),
                        $this->contains('Letter')
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
                    $this->contains('--file'),
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
                    $this->contains('--page'),
                    $this->contains('https://bbc.co.uk')
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
                    $this->contains('--file'),
                    $this->contains('/any/file')
                )
            )
            ->will($this->returnCallback(function () { return $this->getMockedProcess(); }));

        $stream = $pdf->renderFile('/any/file');
        $this->assertIsResource($stream);

        $meta = stream_get_meta_data($stream);
        $this->assertEquals('r', $meta['mode']);
    }
}
