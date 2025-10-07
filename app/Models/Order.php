<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Order extends Model
{
    protected $fillable = ['id', 'user_id', 'product_id', 'paypal_order_id', 'gateway_id', 'status', 'total_amount', 'gateway_type'];

    public function toArray()
    {
        return [
            'id'    => $this->id,
            'product' => $this->product,
            'paypal_order_id' => $this->paypal_order_id,
            'total_amount'      => $this->total_amount,
            'status'     =>  $this->status, // یا هر وضعیت پیش‌فرض
        ];
    }

    protected static function booted(): void

    {

        static::saved(function (Order $order) {
            if ($order->wasChanged('status') && $order->status === 'completed') {


                Mail::raw('Your order has been shipped. Thank you for shopping with us!', function ($message) use ($order) {
                    $message->to($order->user->email)
                        ->subject('Order Shipped');
                });

            }
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class,'gateway_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)   // خروجی مثل /storage/mediaFiles/...
            : null;
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    // آخرین پرداخت
    public function latestPayment()
    {
        return $this->hasOne(OrderPayment::class)->latestOfMany();
    }

// مجموع پرداخت‌های ثبت‌شده
    public function totalPaid(): float
    {
        return $this->payments()->sum('amount');
    }

// بررسی تسویه‌شده یا نه
    public function isFullyPaid(): bool
    {
        return $this->totalPaid() >= $this->amount; // فرض: ستون amount مبلغ کل سفارش
    }
}
