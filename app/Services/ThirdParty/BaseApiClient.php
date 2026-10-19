<?php

namespace App\Services\ThirdParty;

use App\Exceptions\ThirdPartyApiException;
use Illuminate\Http\Client\{ConnectionException, PendingRequest, RequestException};
use Illuminate\Support\Facades\{Http, Log};

abstract class BaseApiClient
{
    protected int $timeout = 10;   // seconds
    protected int $retries = 2;    // times
    protected int $retryDelay = 300; // milliseconds

    /**
     * Base URL for the API
     *
     * @return string
     */
    abstract protected function baseUrl(): string;

    /**
     * Authentication token for the API
     *
     * @return string
     */
    protected function token(): ?string
    {
        return null;
    }

    /**
     * Headers for the API
     *
     * @return array<string, string>
     */
    protected function headers(): array
    {
        return [];
    }

    /**
     * Name of the service
     *
     * @return string
     */
    protected function serviceName(): string
    {
        return class_basename(static::class);
    }

    // ------------------------------------------------------------------
    // Shortcuts for common HTTP methods - GET, POST, PUT, PATCH, DELETE
    // ------------------------------------------------------------------

    /**
     * Send a GET request
     *
     * @param string $endpoint API endpoint
     * @param array<string, mixed> $query Query parameters
     *
     * @return array<string, mixed>
     */
    protected function get(string $endpoint, array $query = []): array
    {
        return $this->send('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Send a POST request
     *
     * @param string $endpoint API endpoint
     * @param array<string, mixed> $data Request body
     *
     * @return array<string, mixed>
     */
    protected function post(string $endpoint, array $data = []): array
    {
        return $this->send('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Send a PUT request
     *
     * @param string $endpoint API endpoint
     * @param array<string, mixed> $data Request body
     *
     * @return array<string, mixed>
     */
    protected function put(string $endpoint, array $data = []): array
    {
        return $this->send('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Send a PATCH request
     *
     * @param string $endpoint API endpoint
     * @param array<string, mixed> $data Request body
     *
     * @return array<string, mixed>
     */
    protected function patch(string $endpoint, array $data = []): array
    {
        return $this->send('PATCH', $endpoint, ['json' => $data]);
    }

    /**
     * Send a DELETE request
     *
     * @param string $endpoint API endpoint
     * @param array<string, mixed> $data Request body
     *
     * @return array<string, mixed>
     */
    protected function delete(string $endpoint, array $data = []): array
    {
        return $this->send('DELETE', $endpoint, ['json' => $data]);
    }

    // ------------------------------------------------------------------
    // Internals - request setup and sending
    // ------------------------------------------------------------------

    /**
     * Create a request instance
     *
     * @return PendingRequest
     */
    protected function request(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->timeout($this->timeout)
            ->withHeaders($this->headers())
            ->retry(
                $this->retries,
                $this->retryDelay,
                // Only retry on server errors or connection errors (like DNS errors) - not on client errors (e.g. 4xx) or timeouts (5xx)
                fn ($exception) => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->serverError()),
                throw: false
            );

        if ($token = $this->token()) {
            $request->withToken($token);
        }

        return $request;
    }

    /**
     * Send a request
     *
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     *
     * @throws ThirdPartyApiException
     */
    private function send(string $method, string $endpoint, array $options): array
    {
        try {
            $response = $this->request()->send($method, $endpoint, $options);
        } catch (ConnectionException $e) {
            // Log the error and throw an exception
            Log::channel('api')->error("[{$this->serviceName()}] Connection failed", [
                'method'   => $method,
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);

            throw new ThirdPartyApiException(
                'Unable to reach the external service. Please try again later.',
                0,
                [],
                $e
            );
        }

        if ($response->failed()) {
            // Log the error and throw an exception
            Log::channel('api')->warning("[{$this->serviceName()}] API error", [
                'method'   => $method,
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);

            throw new ThirdPartyApiException(
                $this->errorMessage($response->json()) ?? 'External service returned an error.',
                $response->status(),
                (array) ($response->json() ?? [])
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Get the error message
     *
     * @param array<string, mixed>|null $body
     *
     * @return string|null
     */
    protected function errorMessage(?array $body): ?string
    {
        return $body['message'] ?? $body['error'] ?? null;
    }
}
