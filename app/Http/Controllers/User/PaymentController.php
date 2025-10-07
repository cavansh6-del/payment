<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\PaygateWallet;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{


    public function store(Request $request, GeneralSettings $settings)
    {

        Order::where('status', 'processing')
            ->where('updated_at', '<', Carbon::now()->subMinutes(15))
            ->update(['status' => 'cancelled']);

        $order = Order::find($request->order_id);

        if ($order->gateway_type === 'paypal') {


            $gateway = $this->getAvailableGateway();

            if ($gateway) {

                $order->status = 'processing';
                $order->comment = $request->comment;
                $order->gateway_id = $gateway->id;
                $order->save();


                $payload = [
                    'order_id'    => $order->id,
                ];

                $token = JWT::encode($payload, config('app.key'), 'HS256');

                $url = $gateway->host . '/pay?token=' . $token;
                return response()->json(['url' => $url]);
            }
        }

        if ($order->gateway_type === 'mercuryo') {

            $walletResponse = Http::get("https://api.paygate.to/control/wallet.php", [
                'address' => $settings->paygate_usdc,
                'callback' => url('/api/paygate/success?order_id=' . $order->id)
            ]);

            $walletResponse->throw();

            $walletData = $walletResponse->json();

            PaygateWallet::updateOrCreate(
                [
                    'order_id' => $order->id
                ],
                [
                    'address_in' => $walletData["address_in"],
                    'polygon_address_in' => $walletData["polygon_address_in"],
                    'callback_url' => $walletData["callback_url"],
                    'ipn_token' => $walletData["ipn_token"],
                ]
            );

            // چون $walletData["address_in"] خودش از قبل urlencode شده به ما داده شده پس بیرون از تابع http_build_query گذاشتمش که خرابش نکنه
            return response()->json(['url' => "https://checkout.paygate.to/process-payment.php?address=" . $walletData["address_in"] . '&' . http_build_query([
                "amount" => $order->total_amount,
                "provider" => "mercuryo",
                "email" => $order->user->email,
                "currency" => "USD"
            ])]);
        }

        if ($order->gateway_type === 'litepay') {

            $litepay = new \App\Services\litepay('merchant'); //using Merchant API

            //always have a valid domain in callback/return
            $token = \Firebase\JWT\JWT::encode(['order_id' => $order->id], config('app.key'), 'HS256');
            $callback_url = url('/api/litepay/callback?order_id=' . $order->id . '&token=' . $token);

            $payload = [
                'order_id'       => $order->id,
                'user_id'        => $order->user_id,
                'amount'         => $order->total_amount,
                'status'      => 'completed',
                'exp'         => now()->addMinutes(180)->timestamp,
            ];

            $jwt_secret = config('app.key');
            $token = \Firebase\JWT\JWT::encode($payload, $jwt_secret, 'HS256');

            $return_url = url('/thank-you?token=' . $token);


            $parameters = array(
                'vendor' => $settings->litepay_vendor,
                'invoice' => $order->id,
                'secret' => $settings->litepay_secret,
                'currency' => 'USD',
                'email' => $order->user->email,
                'price' => $order->total_amount,
                'callbackUrl' => urlencode($callback_url),
                'returnUrl' => urlencode($return_url)
            );


            $data = $litepay->merchant($parameters);
            if ($data->status == 'success') {
                return response()->json(['url' => $data->url]);
            } else {
                return response()->json(['error' => $data->message], 400);
            }
        }

        return null;
    }

    public function check(Request $request)
    {
        $token = $request->token ?? null;
        $jwt_secret = config('app.key');


        $payload = JWT::decode($token, new Key($jwt_secret, 'HS256'));

        $orderId = $payload->order_id;
        $order = Order::where('id', $orderId)->firstOrFail();

        return response()->json($order);
    }

    public function getAvailableGateway()
    {
        return Gateway::where('is_active', true)
            ->whereNull('deactivated_at')
            ->get()
            ->filter(function ($gateway) {
                // اگر last_updated_at مقدار دارد، فقط سفارش‌های بعد از آن را چک کن
                $ordersQuery = Order::query();

                if ($gateway->last_updated_at) {
                    $ordersQuery->where('created_at', '>=', $gateway->last_updated_at);
                }

                $successfulOrders = $ordersQuery
                    ->where('status', 'completed')->where('gateway_id', $gateway->id)
                    ->get();


                $count = $successfulOrders->count();
                $sum   = $successfulOrders->sum('total_amount');

                if ($count >= $gateway->max_transactions || $sum >= $gateway->amount_limit) {
                    $gateway->deactivated_at = Carbon::now();
                }
                $gateway->transactions_amount_since_update     = $sum;
                $gateway->transactions_count_since_update = $count;
                $gateway->save();

                return $count < $gateway->max_transactions && $sum < $gateway->amount_limit;
            })
            ->first();
    }
}
