<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\VerifyRequest;
use FinoPal\Sdk\FinoPal;
use FinoPal\Sdk\HttpClient\Exception\ResponseException;
use FinoPal\Sdk\Options;

$options = new Options([
    'merchant_id' => 'fino-xxxx-xxxx-xxxx-xxxx',
    'sandbox' => false,
]);

$finopal = new FinoPal($options);
$paymentGateway = $finopal->paymentGateway();

$authority = isset($_GET['Authority']) ? (string) $_GET['Authority'] : '';
$status = isset($_GET['Status']) ? (string) $_GET['Status'] : '';

if ($status === 'OK' || $status === '0') {
    $amount = getAmountFromDatabase($authority);
    if ($amount) {
        $verifyRequest = new VerifyRequest();
        $verifyRequest->authority = $authority;
        $verifyRequest->amount = $amount;
        try {
            $response = $paymentGateway->verify($verifyRequest);
            if ($response->code === 100) {
                echo "Payment Verified:\n";
                echo 'Reference ID: ' . $response->ref_id . "\n";
                echo 'Card PAN: ' . $response->card_pan . "\n";
                echo 'Fee: ' . $response->fee . "\n";
            } elseif ($response->code === 101) {
                echo "Payment already verified.\n";
                echo 'Reference ID: ' . $response->ref_id . "\n";
            } else {
                echo 'Transaction failed with code: ' . $response->code;
            }
        } catch (ResponseException $e) {
            echo 'Payment Verification Failed: ' . $e->getMessage();
        } catch (\Exception $e) {
            echo 'Payment Error: ' . $e->getMessage();
        }
    } else {
        echo 'No Matching Transaction Found For This Authority Code.';
    }
} else {
    echo 'Transaction was cancelled or failed.';
}

function getAmountFromDatabase(string $authority): ?int
{
    unset($authority);

    return null;
}
