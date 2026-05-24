<?php

namespace App\Domains\Core\City\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Core\City\Models\City;
use Illuminate\Support\Str;

class Neighborhood extends Model
{
    use  SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'city_id',
        'name_en',
        'name_ar',
        'lat', 'lng',
        'status',
        'created_by',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function boot ()
    {
        parent::boot();
        static::creating(function(Neighborhood $model) {
            $model->uuid = Str::uuid();
        });
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

}
