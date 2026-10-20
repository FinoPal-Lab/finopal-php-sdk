<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes;

final class FeeResponse
{
    public function __construct(
        public readonly ?int $amount,
        public readonly ?int $fee,
        public readonly ?string $fee_type,
        public readonly string $message,
        public readonly ?int $code = null
    ) {
    }

    public function isPaidByCustomer(): bool
    {
        return $this->fee_type === 'Payer';
    }
}
