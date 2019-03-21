# PHP wrapper for chrome-pdf

This is a PHP wrapper for [SynergiTech/chrome-pdf](https://github.com/SynergiTech/chrome-pdf) inspired by [KnpLabs/snappy](https://github.com/KnpLabs/snappy).

## Installation

The `chrome-pdf` binary should be present on your system and optionally available on your path.

```
composer require synergitech/chrome-pdf-php
```

## Usage

Instantiate the class, passing the path to the `chrome-pdf` binary if necessary, and apply options in the same way you would with Snappy.

**Please Note** You must remember to set `displayHeaderFooter` if you want to set either a header or footer and apply margin to make it visible in the PDF.

The available options can be seen in the code. There is also functionality to handle a CSS-style declaring of margins to make input easier.

### Docker Support

In order to run chrome-pdf inside a Docker container, you will have to disable the sandbox feature by setting the `sandbox` option to `"false"` (string not boolean).

## Examples

### Laravel Example

```php
use SynergiTech\ChromePDF\PDF;

// a controller function

$contents = view('enquiries.quote', array('enquiry' => $enquiry))->render();

$pdf = new PDF();

$contents = $pdf->getOutputFromHtml($contents);

return response($contents)->withHeaders(array(
    'Content-Type' => 'application/pdf',
    'Content-Disposition' =>  'inline; filename="enquiry.pdf"',
));
```

### FuelPHP Example (converting from Snappy)

```php

// a controller function

$pdf = new \SynergiTech\ChromePDF\SnappyPDF();

$pdf->setOption('displayHeaderFooter', 'true');
$pdf->setOption('header-html', \View::forge('manage/invoices/pdfheader'));
$pdf->setOption('footer-html', \View::forge('manage/invoices/pdffooter')->set(array(
    'invoice' => $invoice
)));
$pdf->setOption('margin', '100px 0');

$contents = $pdf->getOutputFromHtml(
    \View::forge('manage/invoices/pdfbody')
        ->set(array(
            'invoice' => $invoice,
        ))
);
return \Response::forge(
        $contents,
        200,
        array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice.pdf"'
        )
    );
```
