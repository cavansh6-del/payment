<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['order_id', 'gateway_id', 'transaction_id', 'status', 'amount'];

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
