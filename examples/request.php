<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\RequestRequest;
use FinoPal\Sdk\FinoPal;
use FinoPal\Sdk\HttpClient\Exception\ResponseException;
use FinoPal\Sdk\Options;

$options = new Options([
    'merchant_id' => 'fino-xxxx-xxxx-xxxx-xxxx',
    'sandbox' => false,
]);

$finopal = new FinoPal($options);
$paymentGateway = $finopal->paymentGateway();

$request = new RequestRequest();
$request->amount = 10000;
$request->description = 'Payment for order 12345';
$request->callback_url = 'https://your-site.test/examples/verify.php';
$request->order_id = 'ORD-12345';
$request->mobile = '09120987654';
$request->email = 'test@example.com';

try {
    $response = $paymentGateway->request($request);
    $url = $response->payment_url !== ''
        ? $response->payment_url
        : $paymentGateway->getRedirectUrl($response->authority);
    header('Location: ' . $url);
} catch (ResponseException $e) {
    var_dump($e->getErrorDetails());
} catch (\Exception $e) {
    echo 'Payment Error: ' . $e->getMessage();
}
