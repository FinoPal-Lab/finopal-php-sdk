<?php

declare(strict_types=1);

namespace FinoPal\Sdk;

use FinoPal\Sdk\Endpoint\PaymentGateway;

/**
 * نقطه ورود نمونه کد درگاه فینوپال.
 */
final class FinoPal
{
    private Options $options;

    private ?PaymentGateway $paymentGateway = null;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function options(): Options
    {
        return $this->options;
    }

    public function paymentGateway(): PaymentGateway
    {
        if ($this->paymentGateway === null) {
            $this->paymentGateway = new PaymentGateway($this->options);
        }

        return $this->paymentGateway;
    }
}
