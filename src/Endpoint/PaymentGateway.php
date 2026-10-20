<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Endpoint;

use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\FeeRequest;
use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\InquiryRequest;
use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\RequestRequest;
use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\ReverseRequest;
use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\VerifyRequest;
use FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes\FeeResponse;
use FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes\InquiryResponse;
use FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes\RequestResponse;
use FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes\ReverseResponse;
use FinoPal\Sdk\Endpoint\PaymentGateway\ResponseTypes\VerifyResponse;
use FinoPal\Sdk\HttpClient\Exception\ResponseException;
use FinoPal\Sdk\HttpClient\Exception\ValidationException;
use FinoPal\Sdk\Options;
use FinoPal\Sdk\Support\Payload;

/**
 * متدهای درگاه، هم‌شکل ZarinPal\Sdk paymentGateway().
 */
final class PaymentGateway
{
    private const SUCCESS_VERIFY = [100, 101];

    public function __construct(private readonly Options $options)
    {
    }

    public function request(RequestRequest $request): RequestResponse
    {
        $amount = $this->amount($request->amount);
        $description = trim($request->description);
        $callback = trim($request->callback_url);
        $orderId = trim($request->order_id);

        if ($description === '' || mb_strlen($description) > 255) {
            throw new ValidationException('description الزامی است و حداکثر ۲۵۵ کاراکتر دارد.');
        }
        if (filter_var($callback, FILTER_VALIDATE_URL) === false) {
            throw new ValidationException('callback_url باید یک آدرس http(s) معتبر باشد.');
        }
        if ($orderId === '' || strlen($orderId) > 100) {
            throw new ValidationException('order_id الزامی است و حداکثر ۱۰۰ کاراکتر دارد.');
        }

        $payload = [
            'amount' => $amount,
            'description' => $description,
            'callback_url' => $callback,
            'order_id' => $orderId,
        ];
        if ($request->mobile !== null && $request->mobile !== '') {
            $payload['mobile'] = $request->mobile;
        }
        if ($request->email !== null && $request->email !== '') {
            $payload['email'] = $request->email;
        }
        if ($request->metadata !== null) {
            $payload['metadata'] = $request->metadata;
        }

        $response = $this->post('/api/v1/payment/request', $payload);
        $code = Payload::code($response);
        $data = Payload::data($response);
        $authority = isset($data['authority']) ? (string) $data['authority'] : '';

        if ($code !== 100 || $authority === '') {
            throw new ResponseException(
                Payload::message($response, 'ایجاد تراکنش ناموفق بود.'),
                $code,
                $response
            );
        }

        $paymentUrl = isset($data['payment_url']) ? (string) $data['payment_url'] : '';

        return new RequestResponse(
            authority: $authority,
            payment_url: $paymentUrl !== '' ? $paymentUrl : $this->getRedirectUrl($authority),
            code: 100,
            message: Payload::message($response, 'تراکنش ایجاد شد.'),
            expires_at: isset($data['expires_at']) ? (string) $data['expires_at'] : null
        );
    }

    public function getRedirectUrl(string $authority): string
    {
        $authority = trim($authority);
        if ($authority === '') {
            throw new ValidationException('authority خالی است.');
        }

        return $this->options->baseUrl() . '/api/v1/payment/redirect/' . rawurlencode($authority);
    }

    public function verify(VerifyRequest $request): VerifyResponse
    {
        $authority = $this->authority($request->authority);
        $amount = $this->amount($request->amount);

        $response = $this->post('/api/v1/payment/verify', [
            'authority' => $authority,
            'amount' => $amount,
        ]);
        $code = Payload::code($response);
        $data = Payload::data($response);

        if (!in_array($code, self::SUCCESS_VERIFY, true)) {
            throw new ResponseException(
                Payload::message($response, 'تأیید تراکنش ناموفق بود.'),
                $code,
                $response
            );
        }

        return new VerifyResponse(
            code: (int) $code,
            ref_id: isset($data['ref_id']) ? (string) $data['ref_id'] : null,
            amount: isset($data['amount']) ? (string) $data['amount'] : null,
            message: Payload::message($response, 'پرداخت تأیید شد.'),
            card_pan: isset($data['card_pan']) ? (string) $data['card_pan'] : null,
            fee: isset($data['fee']) && is_numeric($data['fee']) ? (int) $data['fee'] : null
        );
    }

    public function inquiry(InquiryRequest $request): InquiryResponse
    {
        $response = $this->post('/api/v1/payment/inquiry', [
            'authority' => $this->authority($request->authority),
        ]);

        if (($response['success'] ?? false) !== true) {
            throw new ResponseException(
                Payload::message($response, 'استعلام تراکنش ناموفق بود.'),
                Payload::code($response),
                $response
            );
        }

        $data = Payload::data($response);
        $status = $data['status'] ?? $response['status'] ?? null;

        return new InquiryResponse(
            status: $status !== null ? (string) $status : null,
            message: Payload::message($response, 'استعلام انجام شد.'),
            code: Payload::code($response)
        );
    }

    public function reverse(ReverseRequest $request): ReverseResponse
    {
        $response = $this->post('/api/v1/payment/reverse', [
            'authority' => $this->authority($request->authority),
        ]);

        if (($response['success'] ?? false) !== true) {
            throw new ResponseException(
                Payload::message($response, 'ریورس تراکنش ناموفق بود.'),
                Payload::code($response),
                $response
            );
        }

        return new ReverseResponse(
            success: true,
            message: Payload::message($response, 'تراکنش برگشت داده شد.'),
            code: Payload::code($response)
        );
    }

    public function fee(FeeRequest $request): FeeResponse
    {
        $response = $this->post('/api/v1/payment/feeCalculation', [
            'amount' => $this->amount($request->amount),
        ]);
        $code = Payload::code($response);

        if (($response['success'] ?? false) !== true && $code !== 100) {
            throw new ResponseException(
                Payload::message($response, 'محاسبه کارمزد ناموفق بود.'),
                $code,
                $response
            );
        }

        return new FeeResponse(
            amount: isset($response['amount']) && is_numeric($response['amount']) ? (int) $response['amount'] : null,
            fee: isset($response['fee']) && is_numeric($response['fee']) ? (int) $response['fee'] : null,
            fee_type: isset($response['fee_type']) ? (string) $response['fee_type'] : null,
            message: Payload::message($response, 'کارمزد محاسبه شد.'),
            code: $code
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload): array
    {
        $payload['merchant_id'] = $this->options->merchantId();
        $url = $this->options->baseUrl() . $path;

        return $this->options->httpClient()->post($url, $payload);
    }

    private function amount(int $amount): int
    {
        if ($amount < 1000) {
            throw new ValidationException('مبلغ باید عدد صحیح و حداقل ۱۰۰۰ ریال باشد.');
        }

        return $amount;
    }

    private function authority(string $authority): string
    {
        $authority = trim($authority);
        if ($authority === '') {
            throw new ValidationException('authority الزامی است.');
        }

        return $authority;
    }
}
