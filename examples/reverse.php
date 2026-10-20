<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\ReverseRequest;
use FinoPal\Sdk\FinoPal;
use FinoPal\Sdk\HttpClient\Exception\ResponseException;
use FinoPal\Sdk\Options;

$options = new Options([
    'merchant_id' => 'fino-xxxx-xxxx-xxxx-xxxx',
]);

$finopal = new FinoPal($options);
$paymentGateway = $finopal->paymentGateway();

$reverseRequest = new ReverseRequest();
$reverseRequest->authority = 'FP_636E4A5CBBCE0F222B46829BD74AB4A9';

try {
    $response = $paymentGateway->reverse($reverseRequest);
    echo 'Transaction Reversed: ' . ($response->code ?? '') . "\n";
    echo 'Transaction Reversed: ' . $response->message . "\n";
} catch (ResponseException $e) {
    echo 'Transaction reversal failed due to API error: ' . $e->getMessage() . "\n";
    if ($e->getErrorDetails()) {
        echo 'Error Details: ' . json_encode($e->getErrorDetails(), JSON_UNESCAPED_UNICODE) . "\n";
    }
} catch (\Exception $e) {
    echo 'Transaction reversal failed: ' . $e->getMessage() . "\n";
}
