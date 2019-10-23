<?php

namespace SynergiTech\ChromePDF\Test;

use SynergiTech\ChromePDF\AbstractPDF;
use SynergiTech\ChromePDF\Test\TestCase;

class AbstractPDFTest extends TestCase
{
    private function getMock()
    {
        return $this->getMockForAbstractClass(AbstractPDF::class);
    }

    public function test_format()
    {
        $mock = $this->getMock();

        $this->assertSame('A4', $mock->getFormat());

        $mock->setFormat('Letter');
        $this->assertSame('Letter', $mock->getFormat());
    }

    public function test_margin()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getMarginTop());
        $this->assertNull($mock->getMarginRight());
        $this->assertNull($mock->getMarginBottom());
        $this->assertNull($mock->getMarginLeft());

        $mock->setMargin('1', '2', '3', '4');
        $this->assertSame('1', $mock->getMarginTop());
        $this->assertSame('2', $mock->getMarginRight());
        $this->assertSame('3', $mock->getMarginBottom());
        $this->assertSame('4', $mock->getMarginLeft());

        $mock->setMargin('10px', '20px');
        $this->assertSame('10px', $mock->getMarginTop());
        $this->assertSame('20px', $mock->getMarginRight());
        $this->assertSame('10px', $mock->getMarginBottom());
        $this->assertSame('20px', $mock->getMarginLeft());

        $mock->setMargin('30px');
        $this->assertSame('30px', $mock->getMarginTop());
        $this->assertSame('30px', $mock->getMarginRight());
        $this->assertSame('30px', $mock->getMarginBottom());
        $this->assertSame('30px', $mock->getMarginLeft());

        $mock->setMargin('1px', '2px', '3px');
        $this->assertSame('1px', $mock->getMarginTop());
        $this->assertSame('2px', $mock->getMarginRight());
        $this->assertSame('3px', $mock->getMarginBottom());
        $this->assertSame('2px', $mock->getMarginLeft());
    }

    public function test_waitUntil()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getWaitUntil());

        $mock->setWaitUntil('domcontentloaded');
        $this->assertSame('domcontentloaded', $mock->getWaitUntil());
    }

    public function test_pageRanges()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getPageRanges());

        $mock->setPageRanges('1,2,3');
        $this->assertSame('1,2,3', $mock->getPageRanges());
    }

    public function test_printBackground()
    {
        $mock = $this->getMock();

        $this->assertTrue($mock->getPrintBackground());

        $mock->setPrintBackground(false);
        $this->assertFalse($mock->getPrintBackground());
    }

    public function test_mediaEmulation()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getMediaEmulation());

        $mock->setMediaEmulation('screen');
        $this->assertSame('screen', $mock->getMediaEmulation());
    }

    public function test_scale()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getScale());

        $mock->setScale(1.2);
        $this->assertSame(1.2, $mock->getScale());
    }

    public function test_displayHeaderFooter()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getDisplayHeaderFooter());

        $mock->setDisplayHeaderFooter(true);
        $this->assertTrue($mock->getDisplayHeaderFooter());

        $mock->setDisplayHeaderFooter(false);
        $this->assertFalse($mock->getDisplayHeaderFooter());

        $mock->setHeader('test');
        $this->assertTrue($mock->getDisplayHeaderFooter());

        $mock->setHeader(null);
        $this->assertFalse($mock->getDisplayHeaderFooter());

        $mock->setFooter('test');
        $this->assertTrue($mock->getDisplayHeaderFooter());

        $mock->setFooter(null);
        $this->assertFalse($mock->getDisplayHeaderFooter());
    }

    public function test_header()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getHeader());

        $mock->setHeader('header');
        $this->assertSame('header', $mock->getHeader());

        $mock->setHeader(null);
        $this->assertNull($mock->getHeader());
    }

    public function test_footer()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getFooter());

        $mock->setFooter('footer');
        $this->assertSame('footer', $mock->getFooter());

        $mock->setFooter(null);
        $this->assertNull($mock->getFooter());
    }

    public function test_width()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getWidth());

        $mock->setWidth('10px');
        $this->assertSame('10px', $mock->getWidth());

        $mock->setWidth(123);
        $this->assertSame(123, $mock->getWidth());
    }

    public function test_height()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getHeight());

        $mock->setHeight('10px');
        $this->assertSame('10px', $mock->getHeight());

        $mock->setHeight(123);
        $this->assertSame(123, $mock->getHeight());
    }

    public function test_preferCSSPageSize()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getPreferCSSPageSize());

        $mock->setPreferCSSPageSize(false);
        $this->assertFalse($mock->getPreferCSSPageSize());

        $mock->setPreferCSSPageSize(true);
        $this->assertTrue($mock->getPreferCSSPageSize());
    }

    public function test_landscape()
    {
        $mock = $this->getMock();

        $this->assertNull($mock->getLandscape());

        $mock->setLandscape(false);
        $this->assertFalse($mock->getLandscape());

        $mock->setLandscape(true);
        $this->assertTrue($mock->getLandscape());
    }
}
