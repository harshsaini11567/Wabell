<?php

namespace App\Domains\Core\Setting\Models;

use App\Domains\Core\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Core\Upload\Models\Uploads;

class Setting extends Model
{
    use SoftDeletes;

    public $table = 'settings';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    protected $fillable = [
        'key',
        'value',
        'type',
        'display_name',
        'details',
        'group',
        'status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by',
    ];

    protected static function boot ()
    {
        parent::boot();
        static::creating(function(Setting $model) {
            $model->created_by = auth()->user()->id;
        });

    }

     public function uploads()
    {
        return $this->morphMany(Uploads::class, 'uploadsable');
    }

    public function image()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type','setting-image');
    }

    public function getImageUrlAttribute()
    {
        
        if($this->image){
            return $this->image->file_url;
        }
        return "";
    }

   /* public function doc()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type','setting-file');
    } */

    public function getDocUrlAttribute()
    {
        /* if($this->doc){
            return $this->doc->file_url;
        } */
        return "";
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }


}
