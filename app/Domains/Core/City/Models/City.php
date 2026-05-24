<?php

namespace App\Domains\Core\City\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Core\User\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Core\City\Models\Neighborhood;
use Illuminate\Support\Str;

class City extends Model
{
    use  SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
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
        static::creating(function(City $model) {
            $model->uuid = Str::uuid();
        });
    }

    public function neighborhoods()
    {
        return $this->hasMany(Neighborhood::class);
    }

}
