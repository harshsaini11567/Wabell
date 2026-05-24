<?php

namespace App\Domains\Core\ContentManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    public $table = 'pages';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    protected $fillable = [
        'name_en',
        'name_ar',
        'slug',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function sections(){
        return $this->hasMany(Section::class, 'page_id', 'id');
    }
}
