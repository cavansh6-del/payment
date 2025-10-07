<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaygateWallet extends Model
{
    protected $fillable = [
        'order_id',
        'address_in',
        'polygon_address_in',
        'callback_url',
        'ipn_token',
        'status',
        'value_coin',
        'coin',
        'txid_in',
        'txid_out',
    ];
}
