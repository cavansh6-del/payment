<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaygateWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaygateController extends Controller
{


    public function success(Request $request)
    {

        $order = Order::findOrFail($request->order_id);
        if ($order->status == 'completed') {


            $payload = [
                'order_id'       => $order->id,
                'user_id'        => $order->user_id,
                'amount'         => $order->total_amount,
                'status'      => 'completed',
                'exp'         => now()->addMinutes(180)->timestamp,
            ];

            $jwt_secret = config('app.key');
            $token = \Firebase\JWT\JWT::encode($payload, $jwt_secret, 'HS256');

            $redirectUrl = '/thank-you?token=' . $token;

            return redirect($redirectUrl);
        }

        $paygateWallet = PaygateWallet::where('order_id', $order->id)->firstOrFail();

        $response = Http::get('https://api.paygate.to/control/payment-status.php', [
            'ipn_token' => $paygateWallet->ipn_token
        ]);

        $response->throw();

        if ($response->json()['status'] == 'paid') {

            return DB::transaction(function () use ($request, $order, $paygateWallet) {

                $paygateWallet->update([
                    'status' => 'paid',
                    'value_coin' => $request->input('value_coin'),
                    'coin' => $request->input('coin'),
                    'txid_in' => $request->input('txid_in'),
                    'txid_out' => $request->input('txid_out'),

                ]);

                // Update the order status to completed
                $order->status = 'completed';
                $order->save();
            });

            $payload = [
                'order_id'       => $order->id,
                'user_id'        => $order->user_id,
                'amount'         => $order->total_amount,
                'status'      => 'completed',
                'exp'         => now()->addMinutes(180)->timestamp,
            ];

            $jwt_secret = config('app.key');
            $token = \Firebase\JWT\JWT::encode($payload, $jwt_secret, 'HS256');

            $redirectUrl = '/thank-you?token=' . $token;

            return redirect($redirectUrl);
        }

        return response()->json(['status' => false, 'message' => 'Payment not completed.']);
    }
}
