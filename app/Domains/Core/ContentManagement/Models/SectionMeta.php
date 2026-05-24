<?php

namespace App\Domains\Core\ContentManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionMeta extends Model
{

    public $table = 'section_metas';

    protected $dates = [
        'created_at',
        'updated_at',
    ];


    protected $fillable = [
        'section_id',
        'display_name_en',
        'display_name_ar',
        'meta_key',
        'meta_value',
        'field_type',
        'status',
        'created_at',
        'updated_at',
    ];

    public function section(){
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
}
