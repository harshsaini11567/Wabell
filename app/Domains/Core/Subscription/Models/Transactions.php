<?php

namespace App\Domains\Core\Subscription\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Core\User\Models\User;

class Transactions extends Model
{

    protected $table = 'transactions';

    // Laravel will automatically handle timestamps if true (default is true)
    public $timestamps = true;

    // Tell Eloquent to treat these columns as Carbon instances
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    // Fillable columns - match your DB columns exactly
    protected $fillable = [
        'id',
        'user_id',
        'subscription_id',
        'payment_id',
        'registration_id',
        'amount',
        'currency',
        'payment_status',
        'payment_method',
        'payment_data',
        'user_data'
    ]; 

    protected $casts = [
        'payment_data' => 'array',
        'user_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }
}
