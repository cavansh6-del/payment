<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaygateWallet;
use App\Models\Product;
use App\Models\Settings;
use App\Models\User;
use Firebase\JWT\Key;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\PayGateCryptoService;

class OrderController extends Controller
{

    protected $paygate;

    public function __construct(PayGateCryptoService $paygate)
    {
        $this->paygate = $paygate;
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
            'gateway_type' => 'required|string|in:paypal,mercuryo,litepay,paygate',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if(!$user){
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->email),
            ]);
        }

        $product = \App\Models\Product::find($request->id);

        $order = Order::create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'total_amount'      => $product->price,
            'status'     => 'pending', // یا هر وضعیت پیش‌فرض
            'gateway_type'=>$request->gateway_type
        ]);


        if ($order->gateway_type === 'paypal') {
            $gateway = $this->getAvailableGateway();

            if ($gateway) {

                $order->status = 'processing';
                $order->gateway_id = $gateway->id;
                $order->save();

                Mail::to($order->user->email)->send(new \App\Mail\OrderMail($order));
            }
        }elseif ($order->gateway_type === 'paygate') {


            $order->status = 'processing';
            $order->save();

            $address = Settings::query()->where('name','paygate_usdc')->first()?->payload;

            $walletData = $this->paygate->createWallet($address,$order->id);

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

            // پردازش پرداخت
            $payment = $this->paygate->processPayment(
                $walletData["address_in"],
                $order->total_amount,
                $order->user->email
            );


            return $payment;
        }

        return response()->json([
            'message' => 'Order placed successfully!',
            'order'   => $order,
        ], 201);
    }


    public function showInvoice($token, Request $request)
    {
        $jwt_secret = env('JWT_SECRET_KEY');
        try {
            // Decode the JWT token
            $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
            // Check the expiry date
            if (Carbon::now()->timestamp > $decoded->expires_at) {
                abort(403, 'The link has expired.');
            }

            // Find the order using the decoded order_id
            $order = Order::findOrFail($decoded->order_id);

            // Return the invoice view if the token is valid
            return view('orders.invoice', compact('order'));
        } catch (\Exception $e) {
            // Handle invalid or expired token
            abort(403, 'Invalid or expired link.');
        }
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

    public function paygateCallback($orderId,Request $request)
    {
        $paygateWallet = PaygateWallet::where('order_id', $order->id)->firstOrFail();

        // اعتبارسنجی آدرس کیف پول
        if ($request->address_in !== $paygateWallet->wallet_address) {
            abort(400, "Invalid wallet address");
        }
        $paygateWallet->order->status = ($request->value_coin > 0) ? 'completed' : 'cancelled';
        $paygateWallet->status = ($request->value_coin > 0) ? 'approved' : 'rejected';
        $paygateWallet->paid_amount = $request->value_coin;
        $paygateWallet->coin = $request->coin;
        $paygateWallet->txid_in = $request->txid_in;
        $paygateWallet->txid_out = $request->txid_out;
        $paygateWallet->order->save();
        $paygateWallet->save();

        return response('OK', 200); // PayGate به body نیاز ندارد
    }
}
