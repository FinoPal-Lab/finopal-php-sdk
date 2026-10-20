# نمونه کد PHP فینوپال

کیت PHP درگاه پرداخت اینترنتی فینوپال. شکل استفاده همان نمونه کد [زرین‌پال](https://www.zarinpal.com/docs/sdk/php/installation.html) است: `Options`، کلاینت، `paymentGateway()` و یک آبجکت برای هر متد.

مبلغ‌ها به **ریال** هستند. حداقل ۱۰۰۰ ریال.

## نصب

پیش‌نیاز: PHP 8.1 یا بالاتر، افزونه `curl` و Composer.

تا زمان انتشار روی Packagist، بسته را از همین مخزن وصل کنید:

```bash
composer config repositories.finopal path ../finopal-php-sdk
composer require finopal/finopal-php-sdk
```

اگر خود این پوشه را جدا کلون کرده‌اید:

```bash
composer install
```

## پیکربندی

`merchant_id` را از پنل کاربری فینوپال بردارید. برای محیط آزمایشی `sandbox` را `true` بگذارید.

```php
<?php
use FinoPal\Sdk\FinoPal;
use FinoPal\Sdk\Options;

$options = new Options([
    'merchant_id' => 'fino-xxxx-xxxx-xxxx-xxxx',
    'sandbox' => false,
]);
$finopal = new FinoPal($options);
$paymentGateway = $finopal->paymentGateway();
```

| حالت | آدرس |
|---|---|
| واقعی | `https://payment.finopal.ir` |
| سندباکس | `https://sandbox.finopal.ir` |

برای میزبان دلخواه کلید `base_url` را بدهید. این مقدار جای سندباکس و تولید را می‌گیرد.

## درخواست پرداخت

`order_id` در فینوپال الزامی است. `description` حداکثر ۲۵۵ کاراکتر است.

```php
use FinoPal\Sdk\Endpoint\PaymentGateway\RequestTypes\RequestRequest;

$request = new RequestRequest();
$request->amount = 10000;
$request->description = 'Payment for order 12345';
$request->callback_url = 'https://your-site.test/examples/verify.php';
$request->order_id = 'ORD-12345';
$request->mobile = '09120987654';
$request->email = 'test@example.com';

$response = $paymentGateway->request($request);
header('Location: ' . $response->payment_url);
```

اگر `payment_url` خالی باشد:

```php
$paymentGateway->getRedirectUrl($response->authority);
```

نمونه کامل: [examples/request.php](examples/request.php)

## تأیید پرداخت

فقط وقتی `Status=OK` (یا در سندباکس `Status=0`) است `verify` را صدا بزنید. مبلغ باید دقیقاً همان مبلغ درخواست اول باشد.

کد `100` یعنی تازه تأیید شد. کد `101` یعنی قبلاً تأیید شده بود. هر دو یعنی پرداخت موفق است.

نمونه کامل: [examples/verify.php](examples/verify.php)

## استعلام تراکنش

استعلام وضعیت را عوض نمی‌کند. اگر `verify` به‌خاطر قطع شبکه نامعلوم ماند، به‌جای ساخت درخواست تازه استعلام بگیرید.

وضعیت‌ها: `pending`، `successful`، `failed`، `cancelled`، `refunded`، `callback`.

نمونه: [examples/inquiry.php](examples/inquiry.php)

## ریورس تراکنش

فقط روی تراکنش تأییدشده و در بازه کوتاه پس از تأیید. تکرار ریورس یا گذشتن مهلت خطا می‌دهد.

نمونه: [examples/reverse.php](examples/reverse.php)

## محاسبه کارمزد

قبل از ساخت پرداخت، کارمزد را نشان دهید. `fee_type` برابر `Merchant` یعنی کارمزد روی پذیرنده است و `Payer` یعنی روی خریدار.

نمونه: [examples/fee.php](examples/fee.php)

## خطا

| استثنا | معنی |
|---|---|
| `ValidationException` | ورودی نامعتبر؛ درخواستی ارسال نشده |
| `ResponseException` | گیت‌وی درخواست را رد کرده. `getErrorCode()` و `getErrorDetails()` |
| `TransportException` | شبکه، JSON نامعتبر یا خطای ۵xx. نتیجه نامعلوم است؛ استعلام بگیرید |

کدهای پرکاربرد: `-9` اعتبارسنجی، `-11` درگاه یا تراکنش پیدا نشد، `-14` دامنه بازگشت با دامنه ثبت‌شده یکی نیست، `-50` مبلغ تأیید با مبلغ درخواست فرق دارد، `100` و `101` تأیید موفق.

لیست تراکنش‌ها و تراکنش‌های تأییدنشده در API پذیرنده فینوپال نیست و در این نمونه کد هم نیست.
