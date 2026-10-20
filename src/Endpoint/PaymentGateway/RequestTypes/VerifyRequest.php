<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes;

final class VerifyRequest
{
    public string $authority = '';

    public int $amount = 0;
}
