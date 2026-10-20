<?php

declare(strict_types=1);

namespace FinoPal\Sdk\HttpClient;

interface HttpClientInterface
{
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function post(string $url, array $payload): array;
}
