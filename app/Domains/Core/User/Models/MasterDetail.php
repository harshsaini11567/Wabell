<?php 
namespace App\Domains\Core\User\Models;

use Illuminate\Database\Eloquent\Model;

class MasterDetail extends Model
{

    protected $casts = [
        'education' => 'array', 
        'available_time' => 'array', 
        'available_day' => 'array', 
    ];

    protected $dates = [
        'updated_at',
        'created_at',
    ];

    protected $fillable = [
        'user_id',
        'education',
        'experience',
        'tagline',
        'biography',
        'price_per_hour',
        'available_time',
        'available_day',
        'created_at',
        'updated_at',
    ];
}

?>