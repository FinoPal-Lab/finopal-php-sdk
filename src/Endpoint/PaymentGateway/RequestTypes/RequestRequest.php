<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes;

final class RequestRequest
{
    public int $amount = 0;

    public string $description = '';

    public string $callback_url = '';

    public string $order_id = '';

    public ?string $mobile = null;

    public ?string $email = null;

    /** @var array<string, mixed>|null */
    public ?array $metadata = null;
}
