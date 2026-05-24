<?php

namespace App\Domains\Core\SplashScreen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Domains\Core\Upload\Models\Uploads;

class SplashScreen extends Model
{
    use SoftDeletes;

    protected $table = 'splash_screens';

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
        'uuid',
        'title_en',
        'title_ar',
        'description_en',
        'description_ar',
        'status',
        'position',
    ];

    // Auto-generate uuid on creating a new role
    protected static function boot()
    {
        parent::boot();

        static::creating(function (SplashScreen $model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function uploads()
    {
        return $this->morphMany(Uploads::class, 'uploadsable');
    }

    public function splashImage()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type', 'splash_image');
    }

    public function getSplashImageUrlAttribute()
    {
        if ($this->splashImage) {
            return $this->splashImage->file_url;
        }
        return "";
    }

    

}
