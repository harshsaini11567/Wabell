<?php

namespace App\Domains\Core\Subscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Core\Upload\Models\Uploads;

class Plan extends Model
{
    use SoftDeletes;

    protected $table = 'plans';

    // Laravel will automatically handle timestamps if true (default is true)
    public $timestamps = true;

    // Tell Eloquent to treat these columns as Carbon instances
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Fillable columns - match your DB columns exactly
    protected $fillable = [
        'id',
        'name_en',
        'name_ar',
        'monthly_price',
        'yearly_price',
        'features_en',
        'features_ar',
        'is_active',
        'plan_slug',
        'ios_product_id'
    ];   

    public function uploads()
    {
        return $this->morphMany(Uploads::class, 'uploadsable');
    }

    public function planImage()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type', 'plan_image');
    }

    public function getPlanImageUrlAttribute()
    {
        if ($this->planImage) {
            return $this->planImage->file_url;
        }
        return "";
    }

    public function verifiedIcon()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type', 'verified_icon');
    }

    public function getVerifiedIconUrlAttribute()
    {
        if ($this->verifiedIcon) {
            return $this->verifiedIcon->file_url;
        }
        return "";
    }

}
