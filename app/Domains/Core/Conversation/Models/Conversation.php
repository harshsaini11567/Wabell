<?php

namespace App\Domains\Core\Conversation\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Core\User\Models\User;

class Conversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'conversation_type',
        'is_blocked',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if($model->messages()->count() > 0){
                foreach($model->messages as $message){
                    $message->delete();
                }
            }
            $model->participants()->sync([]);
        });
    }


    public function messages(){
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }

    public function participants(){
        return $this->belongsToMany(User::class, 'conversation_participants');
    }
    
    public function customer()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
