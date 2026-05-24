<?php

namespace App\Domains\Core\Role\Models;

use App\Domains\Core\Permission\Models\Permission;
use App\Domains\Core\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

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
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'role_type',
        'role_status',
    ];

    // Auto-generate uuid on creating a new role
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Role $model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permission');
    }
}
