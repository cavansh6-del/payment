<?php

namespace App\Mail;

use App\Models\Settings;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;

    /**
     * Create a new message instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     */
    public function build()
    {

        $name = 'template_with_'.$this->order->gateway->email_template_type;
        $this->content = Settings::query()->where('name',$name)->first()?->payload;

        $secretKey = env('JWT_SECRET_KEY'); // You can store this key in `.env`
        $payload = [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user->id,
            'expires_at' => Carbon::now()->addDay(7)->timestamp, // Expiry date (7 day)
            'iat' => Carbon::now()->timestamp, // Token issue time
        ];
        $jwt = JWT::encode($payload, $secretKey, 'HS256');  // 'HS256' is the default algorithm, but you should explicitly specify it.
        $link = request()->root() . "/admin/login?token={$jwt}";



        $this->content = str_replace('#email', $this->order->gateway->email, $this->content);
        $this->content = str_replace('#link', $this->order->gateway->link, $this->content);
        $this->content = str_replace('#orderId', $this->order->id, $this->content);
        $this->content = str_replace('#amount', $this->order->total_amount, $this->content);
        $this->content = str_replace('#product',  $this->order->product->name, $this->content);
        $this->content = str_replace('#payment-receipt', "<a href='$link' target='_blank'>payment receipt</a>", $this->content);



        return $this->subject('payment receipt')
            ->view('emails.order')
            ->with([
                'link' => $link,
                'order_id' => $this->order->id,
                'product' => $this->order->product->name,
                'amount' => $this->order->total_amount,
                'days' => 7,
            ]);
    }
}
