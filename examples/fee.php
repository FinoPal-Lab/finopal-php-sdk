<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\FeeRequest;
use FinoPal\Sdk\FinoPal;
use FinoPal\Sdk\HttpClient\Exception\ResponseException;
use FinoPal\Sdk\Options;

$options = new Options([
    'merchant_id' => 'fino-xxxx-xxxx-xxxx-xxxx',
]);

$finopal = new FinoPal($options);
$paymentGateway = $finopal->paymentGateway();

$feeRequest = new FeeRequest();
$feeRequest->amount = 1000000;

try {
    $response = $paymentGateway->fee($feeRequest);
    echo 'Amount: ' . $response->amount . "\n";
    echo 'Fee: ' . $response->fee . "\n";
    echo 'Fee type: ' . $response->fee_type . "\n";
} catch (ResponseException $e) {
    echo 'Fee calculation failed: ' . $e->getMessage();
} catch (\Exception $e) {
    echo 'Fee calculation failed: ' . $e->getMessage();
}
