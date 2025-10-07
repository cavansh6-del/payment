<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PayPalController extends Controller
{



    public function success(Request $request)
    {
        $payload = JWT::decode($request->bearerToken(), new Key(env('PAYPAL_CLIENT_JWT_KEY'), 'HS256'));
        $master_payload = JWT::decode($payload['master_token'], new Key(config('app.key'), 'HS256'));

        if(Order::where('paypal_order_id', $master_payload['txn_id'])->where('id', '!=', $master_payload['order_id'])->exists()) {
            return;
        }
        $order = Order::findOrFail($master_payload['order_id']);


        $order->status = 'completed';
        $order->paypal_order_id = $master_payload['txn_id'];
        
        $order->save();
    }
}
