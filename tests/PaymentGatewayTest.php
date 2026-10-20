<?php

declare(strict_types=1);

namespace FinoPal\Sdk\Tests;

use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\FeeRequest;
use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\InquiryRequest;
use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\RequestRequest;
use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\ReverseRequest;
use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\VerifyRequest;
use FinoPal\Sdk\FinoPal;
use FinoPal\Sdk\HttpClient\Exception\ResponseException;
use FinoPal\Sdk\HttpClient\Exception\ValidationException;
use FinoPal\Sdk\Options;
use PHPUnit\Framework\TestCase;

final class PaymentGatewayTest extends TestCase
{
    public function testRequestSendsFinoPalFieldsAndPrefersPaymentUrl(): void
    {
        $http = new FakeHttpClient([
            'success' => true,
            'code' => 100,
            'message' => 'ok',
            'data' => [
                'authority' => 'FP_ABC',
                'payment_url' => 'https://payment.finopal.ir/api/v1/payment/redirect/FP_ABC',
            ],
        ]);
        $gateway = $this->gateway($http);

        $request = new RequestRequest();
        $request->amount = 10000;
        $request->description = 'سفارش ۱۲۳۴۵';
        $request->callback_url = 'https://shop.example/verify.php';
        $request->order_id = 'ORD-12345';
        $request->mobile = '09120000000';

        $response = $gateway->request($request);

        self::assertSame('FP_ABC', $response->authority);
        self::assertSame('https://payment.finopal.ir/api/v1/payment/redirect/FP_ABC', $response->payment_url);
        self::assertSame('https://sandbox.finopal.ir/api/v1/payment/request', $http->calls[0]['url']);
        self::assertSame('fino-test', $http->calls[0]['payload']['merchant_id']);
        self::assertSame('ORD-12345', $http->calls[0]['payload']['order_id']);
        self::assertSame(10000, $http->calls[0]['payload']['amount']);
    }

    public function testRedirectUrlFallsBackWhenPaymentUrlMissing(): void
    {
        $http = new FakeHttpClient([
            'success' => true,
            'code' => 100,
            'data' => ['authority' => 'FP_ABC'],
        ]);
        $gateway = $this->gateway($http);
        $request = new RequestRequest();
        $request->amount = 1000;
        $request->description = 'x';
        $request->callback_url = 'https://shop.example/verify.php';
        $request->order_id = '1';

        $response = $gateway->request($request);

        self::assertSame(
            'https://sandbox.finopal.ir/api/v1/payment/redirect/FP_ABC',
            $response->payment_url
        );
        self::assertSame(
            'https://sandbox.finopal.ir/api/v1/payment/redirect/FP_ABC',
            $gateway->getRedirectUrl('FP_ABC')
        );
    }

    public function testVerifyAcceptsAlreadyVerifiedCode101(): void
    {
        $http = new FakeHttpClient([
            'success' => true,
            'code' => 101,
            'message' => 'already',
            'data' => ['ref_id' => '99', 'code' => 101],
        ]);
        $gateway = $this->gateway($http);
        $request = new VerifyRequest();
        $request->authority = 'FP_ABC';
        $request->amount = 10000;

        $response = $gateway->verify($request);

        self::assertSame(101, $response->code);
        self::assertSame('99', $response->ref_id);
        self::assertTrue($response->isSuccessful());
        self::assertSame('https://sandbox.finopal.ir/api/v1/payment/verify', $http->calls[0]['url']);
    }

    public function testGatewayErrorBecomesResponseException(): void
    {
        $http = new FakeHttpClient([
            'success' => false,
            'code' => -11,
            'message' => 'درگاه یافت نشد.',
            'errors' => ['code' => -11, 'message' => 'درگاه یافت نشد.'],
        ]);
        $gateway = $this->gateway($http);
        $request = new RequestRequest();
        $request->amount = 10000;
        $request->description = 'x';
        $request->callback_url = 'https://shop.example/verify.php';
        $request->order_id = '1';

        try {
            $gateway->request($request);
            self::fail('expected ResponseException');
        } catch (ResponseException $e) {
            self::assertSame(-11, $e->getErrorCode());
            self::assertSame('درگاه یافت نشد.', $e->getMessage());
        }
    }

    public function testValidationDoesNotCallHttp(): void
    {
        $http = new FakeHttpClient(['success' => true]);
        $gateway = $this->gateway($http);
        $request = new RequestRequest();
        $request->amount = 100;

        try {
            $gateway->request($request);
            self::fail('expected ValidationException');
        } catch (ValidationException) {
            self::assertSame([], $http->calls);
        }
    }

    public function testInquiryReverseAndFee(): void
    {
        $http = new FakeHttpClient([
            'success' => true,
            'code' => 100,
            'message' => 'ok',
            'status' => 'successful',
            'amount' => 5000,
            'fee' => 500,
            'fee_type' => 'Payer',
            'data' => ['status' => 'successful', 'code' => 100],
        ]);
        $gateway = $this->gateway($http);

        $inquiry = new InquiryRequest();
        $inquiry->authority = 'FP_ABC';
        self::assertTrue($gateway->inquiry($inquiry)->isPaid());

        $reverse = new ReverseRequest();
        $reverse->authority = 'FP_ABC';
        self::assertTrue($gateway->reverse($reverse)->success);

        $fee = new FeeRequest();
        $fee->amount = 5000;
        $feeResponse = $gateway->fee($fee);
        self::assertSame(500, $feeResponse->fee);
        self::assertTrue($feeResponse->isPaidByCustomer());

        self::assertSame('https://sandbox.finopal.ir/api/v1/payment/inquiry', $http->calls[0]['url']);
        self::assertSame('https://sandbox.finopal.ir/api/v1/payment/reverse', $http->calls[1]['url']);
        self::assertSame('https://sandbox.finopal.ir/api/v1/payment/feeCalculation', $http->calls[2]['url']);
    }

    private function gateway(FakeHttpClient $http): \FinoPal\Sdk\Endpoint\PaymentGateway
    {
        return (new FinoPal(new Options([
            'merchant_id' => 'fino-test',
            'sandbox' => true,
            'http_client' => $http,
        ])))->paymentGateway();
    }
}
