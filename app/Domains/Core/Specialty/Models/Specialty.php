<?php

namespace App\Domains\Core\Specialty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Core\Upload\Models\Uploads;

class Specialty extends Model
{
    use SoftDeletes;
    protected $appends = ['specialty_icon_url'];
    protected $hidden = ['specialtyIcon'];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'uuid',
        'specialty_request_id',
        'name_en',
        'name_ar',
        'parent_specialty_id',
        'specialty_status',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function boot ()
    {
        parent::boot();
        static::creating(function(Specialty $model) {
            $model->uuid = Str::uuid();

            $model->created_by = auth('web')->user()->id;
        });
    }

    public function childSpecialties(){
        return $this->hasMany(Specialty::class, 'parent_specialty_id', 'id');
    }

    public function childrenRecursive()
    {
        return $this->childSpecialties()
                    ->where('specialty_status', 'active')
                    ->with('childrenRecursive')->with('specialtyIcon');
    }

    public function parentSpecialty(){
        return $this->belongsTo(Specialty::class, 'parent_specialty_id', 'id');
    }


    public function getLevelAttribute()
    {
        $level = 1;
        $parent = $this->parentSpecialty;
        
        while ($parent) {
            $level++;
            $parent = $parent->parentSpecialty;
        }

        return $level;
    }

    public function uploads()
    {
        return $this->morphMany(Uploads::class, 'uploadsable');
    }

    public function specialtyIcon()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type', 'specialty_icon');
    }

    public function getSpecialtyIconUrlAttribute()
    {
        if ($this->specialtyIcon) {
            return $this->specialtyIcon->file_url;
        }
        return "";
    }
    public function parent()
    {
        return $this->belongsTo(Specialty::class, 'parent_specialty_id');
    }

    public function specialtyRequest()
    {
        return $this->belongsTo(SpecialtyRequest::class);
    }
}
