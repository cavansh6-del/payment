<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'payer_name',
        'payer_bank_info',
        'receipt_path',
        'status',
    ];

    /**
     * ارتباط با سفارش
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
