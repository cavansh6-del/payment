<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $fillable = [
        'name',
        'host',
        'public_key',
        'private_key',
        'max_transactions',
        'amount_limit',
        'inactive_after_max_transactions',
        'is_active',
        'last_updated_at',
        'deactivated_at',
        'transactions_amount_since_update',
        'total_transactions_amount',
        'total_transactions_count',
        'transactions_count_since_update',
        'logo_path',
        'description','email_template_type','link','email'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }


    public function isMaxTransactionsReached()
    {
        return $this->transactions()->count() >= $this->max_transactions;
    }

    public function isInactive()
    {
        if ($this->isMaxTransactionsReached()) {

            return now()->diffInMinutes($this->updated_at) >= $this->inactive_after_max_transactions;
        }
        return false;
    }
}
