<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\InquiryRequest;
use FinoPal\Sdk\FinoPal;
use FinoPal\Sdk\HttpClient\Exception\ResponseException;
use FinoPal\Sdk\Options;

$options = new Options([
    'merchant_id' => 'fino-xxxx-xxxx-xxxx-xxxx',
]);

$finopal = new FinoPal($options);
$paymentGateway = $finopal->paymentGateway();

$inquiryRequest = new InquiryRequest();
$inquiryRequest->authority = 'FP_636E4A5CBBCE0F222B46829BD74AB4A9';

try {
    $response = $paymentGateway->inquiry($inquiryRequest);
    echo "Transaction Inquiry:\n";
    echo 'Code: ' . $response->code . "\n";
    echo 'Message: ' . $response->message . "\n";
    echo 'Status: ' . $response->status . "\n";
} catch (ResponseException $e) {
    echo 'Transaction Inquiry Validation Error: ' . $e->getMessage();
} catch (\Exception $e) {
    echo 'Transaction Inquiry Validation Error: ' . $e->getMessage();
}
