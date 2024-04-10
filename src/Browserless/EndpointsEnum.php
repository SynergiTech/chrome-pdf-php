<?php

namespace SynergiTech\ChromePDF\Browserless;

enum EndpointsEnum: string
{
    case Default = 'https://chrome.browserless.io';
    case London = 'https://production-lon.browserless.io';
    case SanFrancisco = 'https://production-sfo.browserless.io';
}
