<?php

declare(strict_types=1);

namespace FinoPal\Sdk;

use FinoPal\Sdk\HttpClient\CurlHttpClient;
use FinoPal\Sdk\HttpClient\HttpClientInterface;

/**
 * پیکربندی کلاینت: merchant_id، سندباکس و در صورت نیاز آدرس پایه.
 */
final class Options
{
    public const PRODUCTION_URL = 'https://payment.finopal.ir';

    public const SANDBOX_URL = 'https://sandbox.finopal.ir';

    private string $merchantId;

    private bool $sandbox;

    private ?string $baseUrl;

    private HttpClientInterface $httpClient;

    private int $timeout;

    /**
     * @param array{
     *     merchant_id?: string,
     *     sandbox?: bool,
     *     base_url?: string|null,
     *     http_client?: HttpClientInterface,
     *     timeout?: int
     * } $config
     */
    public function __construct(array $config)
    {
        $merchantId = trim((string) ($config['merchant_id'] ?? ''));
        if ($merchantId === '') {
            throw new \InvalidArgumentException('merchant_id الزامی است.');
        }

        $this->merchantId = $merchantId;
        $this->sandbox = (bool) ($config['sandbox'] ?? false);
        $baseUrl = $config['base_url'] ?? null;
        $this->baseUrl = is_string($baseUrl) && trim($baseUrl) !== '' ? rtrim(trim($baseUrl), '/') : null;
        $this->timeout = (int) ($config['timeout'] ?? 30);
        $this->httpClient = $config['http_client'] ?? new CurlHttpClient($this->timeout);
    }

    public function merchantId(): string
    {
        return $this->merchantId;
    }

    public function sandbox(): bool
    {
        return $this->sandbox;
    }

    public function baseUrl(): string
    {
        if ($this->baseUrl !== null) {
            return $this->baseUrl;
        }

        return $this->sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }

    public function httpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    public function timeout(): int
    {
        return $this->timeout;
    }
}
