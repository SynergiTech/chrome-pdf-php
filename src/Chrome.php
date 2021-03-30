<?php

namespace SynergiTech\ChromePDF;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Chrome extends AbstractPDF
{
    /**
     * @var bool|null
     */
    private $sandbox;

    /**
     * an array of temporary file handles
     * they are unlinked after rendering
     *
     * @var array
     */
    private $handles = [];

    /**
     * @var string
     */
    private $processClass;

    /**
     * @var string
     */
    private $binary;

    /**
     * @param string $binary Custom path to the chrome-pdf binary
     * @param string $processClass A custom override for symfony/process if required
     */
    public function __construct($binary = 'chrome-pdf', $processClass = null)
    {
        $this->binary = $binary;
        $this->processClass = $processClass ?: Process::class;
    }

    /**
     * A factory for creating process classes, to execute the binary
     *
     * @param  string $cmdline The command to run
     * @return object
     */
    public function createProcess($cmdline)
    {
        return new $this->{'processClass'}($cmdline);
    }

    /**
     * Whether or not to enable the Chromium sandbox
     *
     * @param boolean $sandboxEnabled
     * @return self
     */
    public function setSandbox(bool $sandboxEnabled = true): self
    {
        $this->sandbox = $sandboxEnabled;
        return $this;
    }

    /**
     * Whether the sandbox is turned on
     *
     * @return bool|null
     */
    public function getSandbox(): ?bool
    {
        return $this->sandbox;
    }

    /**
     * Get a formatted string representing the margin values,
     * to be passed to the command line.
     *
     * @return string|null
     */
    public function getMarginString(): ?string
    {
        if (
            $this->getMarginTop() === null and $this->getMarginBottom() === null
            and $this->getMarginRight() === null and $this->getMarginLeft() === null
        ) {
            return null;
        }

        $margins = [
            $this->getMarginTop() ?: 0,
            $this->getMarginRight() ?: 0,
            $this->getMarginBottom() ?: 0,
            $this->getMarginLeft() ?: 0,
        ];
        return implode(",", $margins);
    }

    /**
     * Gets an array of options to pass to the command line
     *
     * @return array
     */
    private function getCommandFlags(): array
    {
        $opts = [];
        $opts[] = "--format";
        $opts[] = $this->getFormat();

        if ($this->getMarginString()) {
            $opts[] = "--margin";
            $opts[] = $this->getMarginString();
        }
        if ($this->getMediaEmulation()) {
            $opts[] = "--emulateMedia";
            $opts[] = $this->getMediaEmulation();
        }
        if ($this->getSandbox() === true) {
            $opts[] = "--sandbox";
        }
        if ($this->getSandbox() === false) {
            $opts[] = "--no-sandbox";
        }
        if ($this->getLandscape() === true) {
            $opts[] = "--landscape";
        }
        if ($this->getLandscape() === false) {
            $opts[] = "--no-landscape";
        }
        if ($this->getScale()) {
            $opts[] = "--scale";
            $opts[] = $this->getScale();
        }
        if ($this->getDisplayHeaderFooter() === true) {
            $opts[] = "--displayHeaderFooter";
        }
        if ($this->getDisplayHeaderFooter() === false) {
            $opts[] = "--no-displayHeaderFooter";
        }
        if ($this->getHeader()) {
            $headerFile = $this->getFileForString($this->getHeader());
            $opts[] = "--headerTemplate";
            $opts[] = $headerFile;
        }
        if ($this->getFooter()) {
            $footerFile = $this->getFileForString($this->getFooter());
            $opts[] = "--footerTemplate";
            $opts[] = $footerFile;
        }
        if ($this->getPrintBackground() === true) {
            $opts[] = "--printBackground";
        }
        if ($this->getPrintBackground() === false) {
            $opts[] = "--no-printBackground";
        }
        if ($this->getPageRanges()) {
            $opts[] = "--pageRanges";
            $opts[] = $this->getPageRanges();
        }
        if ($this->getWidth()) {
            $opts[] = "--width";
            $opts[] = $this->getWidth();
        }
        if ($this->getHeight()) {
            $opts[] = "--height";
            $opts[] = $this->getHeight();
        }
        if ($this->getPreferCSSPageSize() === true) {
            $opts[] = "--preferCSSPageSize";
        }
        if ($this->getPreferCSSPageSize() === false) {
            $opts[] = "--no-preferCSSPageSize";
        }
        if ($this->getWaitUntil()) {
            $opts[] = "--waitUntil";
            $opts[] = $this->getWaitUntil();
        }

        return $opts;
    }

    /**
     * Store the given string into a temporary file, and return its path
     *
     * @param  string $content The string to save into the temporary file
     * @return string          The path to the temporary file
     */
    private function getFileForString(string $content): string
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'chromepdf');
        // Chrome must see the correct extension to load it as html
        rename($tmpfile, $tmpfile .= '.html');
        $this->handles[] = $tmpfile;
        file_put_contents($tmpfile, $content);

        return $tmpfile;
    }

    /**
     * Execute the local renderer with the given options
     *
     * @param  array $options
     * @return string A path to a temporary file containing the rendered PDF
     */
    private function executeBinary(array $options): string
    {
        $output = tempnam(sys_get_temp_dir(), 'chromepdf');
        $options[] = "--path";
        $options[] = $output;
        array_unshift($options, $this->binary, 'pdf');
        $proc = $this->createProcess($options);
        $proc->mustRun();
        array_map('unlink', $this->handles);
        $this->handles = [];

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function renderContent(string $content)
    {
        $opts = $this->getCommandFlags();

        $file = $this->getFileForstring($content);
        $opts[] = "--file";
        $opts[] = $file;

        $outputFile = $this->executeBinary($opts);
        return fopen($outputFile, 'r');
    }

    /**
     * @inheritdoc
     */
    public function renderURL(string $url)
    {
        $opts = $this->getCommandFlags();
        $opts[] = "--page";
        $opts[] = $url;

        $outputFile = $this->executeBinary($opts);
        return fopen($outputFile, 'r');
    }

    /**
     * @inheritdoc
     */
    public function renderFile(string $path)
    {
        $opts = $this->getCommandFlags();
        $opts[] = "--file";
        $opts[] = $path;

        $outputFile = $this->executeBinary($opts);
        return fopen($outputFile, 'r');
    }
}
