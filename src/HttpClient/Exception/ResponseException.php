<?php

declare(strict_types=1);

namespace FinoPal\Sdk\HttpClient\Exception;

/**
 * پاسخ گیت‌وی با success=false یا کد نتیجه نامعتبر.
 */
final class ResponseException extends \RuntimeException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        string $message,
        private readonly ?int $errorCode,
        private readonly array $details
    ) {
        parent::__construct($message, $errorCode ?? 0);
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrorDetails(): array
    {
        return $this->details;
    }
}
