<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\LitepayController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\PaygateController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\PayPalController;
use App\Models\Order;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Route::middleware('api')->group(function () {
    Route::get('check-user', [AuthController::class, 'checkEmailExists']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);


    Route::get('products', [ProductController::class, 'index']);

    Route::post('order', [OrderController::class, 'store']);
    Route::post('payment', [PaymentController::class, 'store']);
    Route::post('payment/check', [PaymentController::class, 'check']);



    Route::get('paygate/success', [PaygateController::class, 'success']);
    Route::get('litepay/callback', [LitepayController::class, 'callback']);

    Route::post('paypal/success', [PayPalController::class, 'success']);


    Route::get('/order', function () {

        try {
            $decoded = JWT::decode(request()->bearerToken(), new Key(config('app.key'), 'HS256'));
            $order = Order::findOrFail($decoded->order_id);
        } catch (\Exception $e) {
            abort(404);
        }

        return [
            'id'=>$order->id,
            'name'=>$order->product->product_invoice,
            'email'=>$order->user->email,
            'amount'=>$order->total_amount,
            'status'=>$order->status,
        ];
    });
});
