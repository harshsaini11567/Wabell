<?php

namespace App\Domains\Core\Conversation\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Core\User\Models\User;
use App\Domains\Core\Upload\Models\Uploads;

class Message extends Model
{
    use SoftDeletes;

     protected $fillable = [
        'sender_id',
        'conversation_id',
        'content',
        'content_type',
        'read_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if($model->content_type != 'text'){
                if ($model->uploads) {
                    foreach($model->uploads()->select('id', 'file_path')->get() as $messageDoc){
                        $messageDoc->delete();
                    }
                }
            }
        });
    }

    public function sender(){
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function conversation(){
        return $this->belongsTo(Conversation::class, 'conversation_id', 'id');
    }

    public function uploads()
    {
        return $this->morphMany(Uploads::class, 'uploadsable');
    }

    public function messageImages()
    {
        return $this->morphMany(Uploads::class, 'uploadsable')->where('type', 'message_image');
    }

    public function getMessageImageUrlsAttribute()
    {
        $messageImages = [];
        if ($this->messageImages) {
            foreach($this->messageImages()->get() as $messageImage){
                $messageImages[] = $messageImage->file_url;
            }
            return $messageImages;
        }
        return "";
    }

    public function messageVideos()
    {
        return $this->morphMany(Uploads::class, 'uploadsable')->where('type', 'message_video');
    }

    public function getMessageVideoUrlsAttribute()
    {
        $messageVideos = [];
        if ($this->messageVideos) {
            foreach($this->messageVideos()->get() as $messageVideo){
                $messageVideos[] = $messageVideo->file_url;
            }
            return $messageVideos;
        }
        return "";
    }

    public function messageDocuments()
    {
        return $this->morphMany(Uploads::class, 'uploadsable')->where('type', 'message_document');
    }

    public function getMessageDocumentUrlsAttribute()
    {
        $messageDocuments = [];
        if ($this->messageVideos) {
            foreach($this->messageDocuments()->get() as $messageDocument){
                $messageDocuments[] = $messageDocument->file_url;
            }
            return $messageDocuments;
        }
        return "";
    }

    public function deletedByUsers()
    {
        return $this->belongsToMany(User::class, 'message_user_deletes')->withTimestamps();
    }
}
