<?php

namespace SynergiTech\ChromePDF\Browserless;

class Screenshot
{
    use Client;

    /**
     * @param array<string,mixed> $options see https://www.browserless.io/docs/screenshot#custom-options
     *
     * @return resource
     */
    public function render(string $url, array $options = [])
    {
        $options = array_merge([
            'type' => 'jpeg',
            'fullPage' => false,
        ], $options);

        if ($options['type'] === 'jpeg' && ! isset($options['quality'])) {
            $options['quality'] = 75;
        }

        return $this->request(
            endpoint: '/screenshot',
            json: [
                'url' => $url,
                'options' => $options,
            ],
        );
    }
}
