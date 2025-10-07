<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'name',
        'group',
        'payload',
    ];

    protected $casts = [
    ];

    public function toArray()
    {
        return [
            $this->name => $this->payload,
        ];
    }
}
