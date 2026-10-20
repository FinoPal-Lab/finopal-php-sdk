<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes;

final class RequestResponse
{
    public function __construct(
        public readonly string $authority,
        public readonly string $payment_url,
        public readonly int $code,
        public readonly string $message,
        public readonly ?string $expires_at = null
    ) {
    }
}
