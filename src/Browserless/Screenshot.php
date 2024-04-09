<?php

namespace SynergiTech\ChromePDF\Browserless;

class Screenshot
{
    use Client;

    /**
     * Defaults to the following options:
     * - quality: 75
     * - type: jpeg
     * - fullPage: true
     *
     * @param array<string, mixed> $options see https://www.browserless.io/docs/screenshot#custom-options
     *
     * @return resource
     */
    public function render(string $url, array $options = [])
    {
        $options = array_merge([
            'quality' => 75,
            'type' => 'jpeg',
            'fullPage' => false,
        ], $options);

        return $this->request(
            endpoint: '/screenshot',
            json: [
                'url' => $url,
                'options' => $options,
            ]
        );
    }
}
