<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes;

final class VerifyResponse
{
    public function __construct(
        public readonly int $code,
        public readonly ?string $ref_id,
        public readonly ?string $amount,
        public readonly string $message,
        public readonly ?string $card_pan = null,
        public readonly ?int $fee = null
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->code === 100 || $this->code === 101;
    }
}
