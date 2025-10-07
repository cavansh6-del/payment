<?php

namespace App\Services;

if (!extension_loaded('curl')) {
    throw new \Exception('cURL extension seems not to be installed');
}

use GuzzleHttp\Client;

class PayGateCryptoService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
           // 'base_uri' => 'https://api.paygate.to/',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * تبدیل ارز به USD
     */
    public function convertToUSD(string $from, float $value)
    {
        $response = $this->client->get("control/convert.php", [
            'query' => [
                'from' => $from,
                'value' => $value,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * ایجاد کیف پول جدید
     */
    public function createWallet($address,$orderId)
    {
        $response = $this->client->get('https://api.paygate.to/control/wallet.php', [
            'query' => [
                'address'   => $address,
                'callback'  => url('/api/paygate/success?order_id=' . $orderId),
            ]
        ]);


        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * ایجاد و پردازش پرداخت
     */
    public function processPayment($address,float $amount, string $email, string $currency = 'USDC')
    {
        return response()->json(['url' => "https://checkout.paygate.to/process-payment.php?address=" .$address . '&' . http_build_query([
                "amount" => $amount,
                "provider" => "mercuryo",
                "email" => $email,
                "currency" => "USD"
            ])]);
    }

    /**
     * بررسی وضعیت پرداخت (اختیاری)
     */
    public function checkPaymentStatus(string $ipnToken)
    {
        $response = $this->client->get('control/payment-status.php', [
            'query' => [
                'ipn_token' => $ipnToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}

?>
