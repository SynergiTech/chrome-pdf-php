# PHP ChromePDF Renderer
![Unit tests](https://github.com/SynergiTech/chrome-pdf-php/workflows/Unit%20tests/badge.svg)
[![Tests](https://github.com/SynergiTech/chrome-pdf-php/actions/workflows/main.yml/badge.svg)](https://github.com/SynergiTech/chrome-pdf-php/actions/workflows/main.yml)

_For pre-V1 documentation [click here](https://github.com/SynergiTech/chrome-pdf-php/blob/v0/README.md)_

This is a library for creating PDFs from HTML rendered with the SkPDF backend via Chrome. In order to do this, you can opt to use one of the supported drivers:
* [SynergiTech/chrome-pdf](https://github.com/SynergiTech/chrome-pdf)
* [browserless](https://www.browserless.io/)

## Installation
```
composer require synergitech/chrome-pdf-php
```
### chrome-pdf
If you are planning to use the [`chrome-pdf`](https://github.com/SynergiTech/chrome-pdf) driver to render PDFs locally, you should also make sure to install this from npm.

### browserless
If you are planning to use the [browserless](https://www.browserless.io/) driver to render PDFs remotely, you should register for an API key. Remember that local assets cannot be rendered by browserless.

## Usage
A common interface is provided via AbstractPDF. The options presented via this class will be available from all drivers.

You should instantiate one of the available drivers, potentially passing options required by the driver:
```php
use SynergiTech\ChromePDF\Chrome;
use SynergiTech\ChromePDF\Browserless;

$pdf = new Chrome('path-to-chrome-pdf');
$pdf->renderContent('<h1>test</h1>');

$pdf = new Browserless('your-api-key');
$pdf->renderContent('<h1>test</h1>');
```

## Examples
Some examples can be found in the `examples` folder.
