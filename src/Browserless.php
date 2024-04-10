<?php

namespace SynergiTech\ChromePDF;

use SynergiTech\ChromePDF\Browserless\Client;

/**
 * Driver to render PDFs remotely using browserless.io
 */
class Browserless extends AbstractPDF
{
    use Client;

    /**
    * @var string
    */
    private $pdfEndpoint = '/pdf';

    /**
     * @var bool
     */
    private $safeMode = false;
    /**
     * @var int|null
     */
    private $rotate;
    /**
     * @var int|null
     */
    private $timeout;

    /**
     * Sets the PDF documents rotation
     *
     * @param  int $rotation The number of degrees to rotate the document by
     * @return self
     */
    public function setRotation(int $rotation = null): self
    {
        $this->rotate = $rotation;
        return $this;
    }

    /**
     * Sets whether or not to ask Browserless to attempt to render the document in safe mode
     *
     * @link https://docs.browserless.io/docs/pdf.html#safemode
     * @param  bool $safeMode
     * @return self
     */
    public function setSafeMode(bool $safeMode): self
    {
        $this->safeMode = $safeMode;
        return $this;
    }

    /**
     * Sets the maximum time the PDF renderer should be prepared to spend rendering
     *
     * @param  int $milliseconds
     * @return self
     */
    public function setTimeout(int $milliseconds = null): self
    {
        $this->timeout = $milliseconds;
        return $this;
    }

    /**
     * Retrieves the rendering timeout
     *
     * @return int|null
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * Whether the document will be rendered in safe mode or not
     *
     * @return bool
     */
    public function getSafeMode(): bool
    {
        return $this->safeMode;
    }

    /**
     * Gets the documents rotation angle in degrees
     *
     * @return int|null
     */
    public function getRotation(): ?int
    {
        return $this->rotate;
    }

    /**
     * Gets the payload of JSON options to be sent to browserless, minus the `url` or `html` property
     *
     * @return array<string, mixed>
     */
    public function getFormattedOptions(): array
    {
        $pdfOptions = [];
        if ($this->getDisplayHeaderFooter() !== null) {
            $pdfOptions['displayHeaderFooter'] = $this->getDisplayHeaderFooter();
        }
        if ($this->getFooter() !== null) {
            $pdfOptions['footerTemplate'] = $this->getFooter();
        }
        if ($this->getFormat() !== null) {
            $pdfOptions['format'] = $this->getFormat();
        }
        if ($this->getHeader() !== null) {
            $pdfOptions['headerTemplate'] = $this->getHeader();
        }
        if ($this->getLandscape() !== null) {
            $pdfOptions['landscape'] = $this->getLandscape();
        }
        $margin = [
            'top' => $this->getMarginTop(),
            'right' => $this->getMarginRight(),
            'bottom' => $this->getMarginBottom(),
            'left' => $this->getMarginLeft(),
        ];
        $margin = array_filter($margin);
        if (!empty($margin)) {
            $pdfOptions['margin'] = $margin;
        }

        if ($this->getPageRanges() !== null) {
            $pdfOptions['pageRanges'] = $this->getPageRanges();
        }
        if ($this->getPreferCSSPageSize() !== null) {
            $pdfOptions['preferCSSPageSize'] = $this->getPreferCSSPageSize();
        }
        if ($this->getPrintBackground() !== null) {
            $pdfOptions['printBackground'] = $this->getPrintBackground();
        }
        if ($this->getScale() !== null) {
            $pdfOptions['scale'] = $this->getScale();
        }
        if ($this->getWidth() !== null) {
            $pdfOptions['width'] = $this->getWidth();
        }
        if ($this->getHeight() !== null) {
            $pdfOptions['height'] = $this->getHeight();
        }

        $options = [
            'options' => $pdfOptions,
            'safeMode' => $this->getSafeMode(),
        ];

        $goto = [];
        if ($this->getWaitUntil() !== null) {
            $goto['waitUntil'] = $this->getWaitUntil();
        }
        if ($this->getTimeout() !== null) {
            $goto['timeout'] = $this->getTimeout();
        }
        if (!empty($goto)) {
            $options['gotoOptions'] = $goto;
        }

        if ($this->getRotation() !== null) {
            $options['rotate'] = $this->getRotation();
        }

        if ($this->getMediaEmulation() !== null) {
            $options['emulateMedia'] = $this->getMediaEmulation();
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return resource
     */
    private function render(array $options)
    {
        return $this->request($this->pdfEndpoint, $options);
    }

    /**
     * @inheritdoc
     */
    public function renderContent(string $content)
    {
        $options = $this->getFormattedOptions();
        $options['html'] = $content;
        return $this->render($options);
    }

    /**
     * @inheritdoc
     */
    public function renderURL(string $url)
    {
        $options = $this->getFormattedOptions();
        $options['url'] = $url;
        return $this->render($options);
    }

    /**
     * @inheritdoc
     */
    public function renderFile(string $path)
    {
        $options = $this->getFormattedOptions();
        $options['html'] = file_get_contents($path);
        return $this->render($options);
    }
}
