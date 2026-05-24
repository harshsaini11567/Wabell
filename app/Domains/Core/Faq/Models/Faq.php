<?php

namespace App\Domains\Core\Faq\Models;

use App\Domains\Core\Permission\Models\Permission;
use App\Domains\Core\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use SoftDeletes;

    protected $table = 'faqs';

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
        'question_en',
        'question_ar',
        'answer_en',
        'answer_ar',
        'faq_status',
        'faq_type',
    ];

    // Auto-generate uuid on creating a new role
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Faq $model) {
            $model->created_by = auth('web')->user()->id;
        });
    }

    public function createdBy()
    {
        return $this->belongsToMany(User::class, 'created_by', 'id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permission');
    }
}
