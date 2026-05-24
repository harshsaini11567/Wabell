<?php

namespace App\Domains\Core\Subscription\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Core\User\Models\User;

class UserSubscription extends Model
{

    protected $table = 'user_subscriptions';

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
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'billing_cycle',
        'auto_renew',
        'price',  // plan price
        'transaction_id',
        'original_transaction_id',
        'plan_data',
        'user_data'
    ]; 
    
    protected $casts = [
        'plan_data' => 'array',
        'user_data' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'billing_cycle' => 'string',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function transactions()
    {
        return $this->hasOne(Transactions::class, 'subscription_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
