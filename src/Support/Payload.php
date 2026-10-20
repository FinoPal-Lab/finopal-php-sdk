<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Support;

final class Payload
{
    /**
     * @param array<string, mixed> $response
     */
    public static function code(array $response): ?int
    {
        $candidates = [
            $response['code'] ?? null,
            is_array($response['data'] ?? null) ? ($response['data']['code'] ?? null) : null,
            is_array($response['errors'] ?? null) ? ($response['errors']['code'] ?? null) : null,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === null || is_bool($candidate)) {
                continue;
            }
            if (is_int($candidate) || (is_string($candidate) && is_numeric($candidate))) {
                return (int) $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $response
     */
    public static function message(array $response, string $fallback): string
    {
        $candidates = [
            $response['message'] ?? null,
            is_array($response['errors'] ?? null) ? ($response['errors']['message'] ?? null) : null,
            is_array($response['data'] ?? null) ? ($response['data']['message'] ?? null) : null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return $fallback;
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    public static function data(array $response): array
    {
        $data = $response['data'] ?? null;

        return is_array($data) ? $data : $response;
    }
}
