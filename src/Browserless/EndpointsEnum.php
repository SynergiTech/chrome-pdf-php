<?php

namespace SynergiTech\ChromePDF\Browserless;

enum EndpointsEnum: string
{    
    case Default = 'https://chrome.browserless.io';

    // V1 and V2
    case ChromeEuAms = 'https://chrome-eu-ams.browserless.io';
    case ChromeEuUk = 'https://chrome-eu-uk.browserless.io';
    case ChromeUsEast = 'https://chrome-us-east.browserless.io';
    case ChromeUsWest = 'https://chrome-us-west.browserless.io';

    // V2 only
    case Amsterdam = 'https://production-ams.browserless.io';
    case London = 'https://production-lon.browserless.io';
    case SanFrancisco = 'https://production-sfo.browserless.io';
}
