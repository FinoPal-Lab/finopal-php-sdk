<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes;

final class InquiryResponse
{
    public function __construct(
        public readonly ?string $status,
        public readonly string $message,
        public readonly ?int $code = null
    ) {
    }

    public function isPaid(): bool
    {
        return $this->status === 'successful';
    }
}
