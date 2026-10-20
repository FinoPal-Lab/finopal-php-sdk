<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes;

final class ReverseResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?int $code = null
    ) {
    }
}
