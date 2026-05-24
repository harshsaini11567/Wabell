<?php

namespace App\Domains\Core\Announcement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use SoftDeletes;

    protected $table = 'announcements';

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
        'description_ar'
    ];

    // Auto-generate uuid on creating a new announcement
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Announcement $model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }
}
