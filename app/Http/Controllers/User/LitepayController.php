<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class LitepayController extends Controller
{
    public function callback(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'secret' => 'required|string',
        ]);

        $token = $request->input('token');
        $payload = \Firebase\JWT\JWT::decode($token, new Key(config('app.key'), 'HS256'));

        if ($payload->order_id == $request->input('order_id')) {
            Order::where('id', $request->input('order_id'))
                ->update(['status' => 'completed']);
        }
    }
}
