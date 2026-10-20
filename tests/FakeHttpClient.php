<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Tests;

use FinoPal\Sdk\HttpClient\HttpClientInterface;

final class FakeHttpClient implements HttpClientInterface
{
    /** @var list<array{url: string, payload: array<string, mixed>}> */
    public array $calls = [];

    /** @param array<string, mixed> $response */
    public function __construct(private readonly array $response)
    {
    }

    public function post(string $url, array $payload): array
    {
        $this->calls[] = ['url' => $url, 'payload' => $payload];

        return $this->response;
    }
}
