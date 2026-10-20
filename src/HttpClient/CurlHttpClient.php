<?php

declare(strict_types=1);

namespace FinoPal\Sdk\HttpClient;

use FinoPal\Sdk\HttpClient\Exception\TransportException;

final class CurlHttpClient implements HttpClientInterface
{
    public function __construct(private readonly int $timeout = 30)
    {
    }

    public function post(string $url, array $payload): array
    {
        if (!function_exists('curl_init')) {
            throw new TransportException('افزونه cURL در PHP فعال نیست.');
        }

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            throw new TransportException('بدنه درخواست قابل تبدیل به JSON نیست.');
        }

        $handle = curl_init($url);
        if ($handle === false) {
            throw new TransportException('شروع درخواست HTTP ناموفق بود.');
        }

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: finopal-php-sdk/1.0',
            ],
        ]);

        $raw = curl_exec($handle);
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        if ($errno !== 0 || !is_string($raw)) {
            throw new TransportException('خطا در اتصال به فینوپال: ' . $error, $status);
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new TransportException('پاسخ فینوپال JSON معتبر نیست.', $status);
        }

        if ($status >= 500) {
            throw new TransportException('سرور فینوپال پاسخ ناموفق داد.', $status);
        }

        return $decoded;
    }
}
