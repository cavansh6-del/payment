<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'mounth', 'price', 'image_url', 'order','product_invoice', 'published'];


    public function toArray()
    {
        return [
            'id'   => $this->id,
            'name'   => $this->name,
            'mounth' => $this->mounth,
            'price'  => $this->price,
            'description'  => $this->description,
        ];
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity');
    }
}
