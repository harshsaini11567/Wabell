<?php

namespace App\Domains\Core\ContentManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    public $table = 'sections';

    protected $dates = [
        'created_at',
        'updated_at',
    ];


    protected $fillable = [
        'page_id',
        'name_en',
        'name_ar',
        'section_key',
        'position',
        'status',
        'created_at',
        'updated_at',
    ];

    public function page(){
        return $this->belongsTo(Page::class, 'page_id', 'id');
    }

    public function sectionMetas(){
        return $this->hasMany(SectionMeta::class, 'section_id', 'id');
    }
}
