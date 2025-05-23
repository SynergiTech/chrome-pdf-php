<?php

namespace SynergiTech\ChromePDF\Browserless;

use GuzzleHttp\Psr7\StreamWrapper;

trait Client
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * @param \GuzzleHttp\Client $client custom Guzzle client
     */
    public function __construct(
        ?string $apiKey = null,
        EndpointsEnum|string $endpoint = EndpointsEnum::Default,
        $client = null
    ) {
        if ($client === null) {
            // @codeCoverageIgnoreStart
            $client = new \GuzzleHttp\Client([
                'base_uri' => ($endpoint instanceof EndpointsEnum) ? $endpoint->value : $endpoint,
            ]);
            // @codeCoverageIgnoreEnd
        }
        $this->client = $client;
        if ($apiKey !== null) {
            $this->setApiKey($apiKey);
        }
    }

    /**
     * Retrieves the browserless.io API key
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @param array<mixed> $json
     *
     * @return resource
     */
    protected function request(string $endpoint, array $json)
    {
        try {
            $response = $this->client->post($endpoint, [
                'query' => [
                    'token' => $this->getApiKey(),
                ],
                'json' => $json,
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $message = 'No response';

            $response = $e->getResponse();

            /**
             * You could use $e->hasResponse() but that is not accurate enough,
             * as phpstan will be analysing against method signatures from guzzle 6 & 7
             */
            if ($response !== null) {
                $message = $response->getBody();

                $json = json_decode($message);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $messages = [];
                    foreach ($json as $error) {
                        $messages[] = $error->message;
                    }
                    $message = implode(', ', $messages);
                }
            }

            throw new APIException("Failed to render from Browserless: {$message}", $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new APIException("Failed to render from Browserless: {$e->getMessage()}", $e->getCode(), $e);
        }

        return StreamWrapper::getResource($response->getBody());
    }

    /**
     * Sets the browserless API key
     *
     * @param  string $apiKey
     * @return self
     */
    private function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }
}
