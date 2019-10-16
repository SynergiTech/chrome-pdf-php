<?php

use SynergiTech\ChromePDF\Browserless;

$contents = view('enquiries.quote', array('enquiry' => $enquiry))->render();

$renderer = new Browserless(getenv('BROWSERLESS_API_KEY'));
$pdf = $renderer->renderContent($contents);

return response($pdf)->withHeaders(array(
    'Content-Type' => 'application/pdf',
    'Content-Disposition' =>  'inline; filename="enquiry.pdf"',
));
