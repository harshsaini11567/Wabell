<?php

namespace App\Domains\Core\User\Models;

use App\Domains\Api\Auth\Emails\SendResetPasswordOtpMail;
use App\Domains\Core\City\Models\City;
use App\Domains\Core\City\Models\Neighborhood;
use App\Domains\Core\Role\Models\Role;
use App\Domains\Core\Specialty\Models\Specialty;
use App\Domains\Core\Upload\Models\Uploads;
use App\Domains\Core\User\Models\MasterDetail;
use App\Domains\Core\User\Models\Review;
use App\Domains\Core\Conversation\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;
use App\Domains\Core\Subscription\Models\UserSubscription;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'city_id',
        'neighborhood_id',
        'country_code',
        'phone',
        'phone_varified',
        'is_ban',
        'is_approved',
        'approval_status',
        'is_available',
        'till_offline',
        'login_type',
        'language',
        'date_of_birth',
        'gender',
        'social_user_id',
        'device_token',
        'user_status',
        'about_user',
        'user_interest',
        'learning_mode',
        'gender_preference',
        'last_access_date_time'
       // 'refresh_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'gender_preference' => 'array',
        'last_access_date_time' => 'datetime',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot ()
    {
        parent::boot();
        static::creating(function(User $model) {
            $model->uuid = Str::uuid();
        });
    }

    public function sendPasswordResetOtpNotification($user, $token, $subject, $expiretime)
    {
        Mail::to($user->email)->send(new SendResetPasswordOtpMail($user, $token, $subject, $expiretime));
    }

    // Get identifier
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Return any custom claims (can be empty)
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($roleName)
    {
        return $this->roles()->where('name_en', $roleName)->exists();
    }

    public function getIsSuperAdminAttribute()
    {
        return $this->roles()->where('id', config('constant.roles.super_admin'))->exists();
    }

    public function getIsAdminAttribute()
    {
        return $this->roles()->where('role_type', 'admin')->exists();
    }
    public function uploads()
    {
        return $this->morphMany(Uploads::class, 'uploadsable');
    }

    public function profileImage()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type', 'user_profile');
    }

    public function getProfileImageUrlAttribute()
    {
        if ($this->profileImage) {
            return $this->profileImage->file_url;
        }
        return "";
    }

    public function masterDetail()
    {
        return $this->hasOne(MasterDetail::class,'user_id');
    }
    public function city()
    {
        return $this->belongsTo(City::class,'city_id');
    }
    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class, 'neighborhood_id');
    }

    public function idFiles()
    {
        return $this->uploads()->where('type', 'id_file');
    }
 
    public function getIdFilesUrlsAttribute()
    {
        $IdFiles = [];
        if ($this->IdFiles) {
            foreach($this->IdFiles()->get() as $IdFile){
                $IdFiles[] = $IdFile->file_url;
            }
            return $IdFiles;
        }
        return "";
    }
     public function certificateFiles()
    {
        return $this->uploads()->where('type', 'certificate_file');
    }
 
    public function getCertificateFilesUrlsAttribute()
    {
        $CertificateFiles = [];
        if ($this->CertificateFiles) {
            foreach($this->CertificateFiles()->get() as $CertificateFile){
                $CertificateFiles[] = $CertificateFile->file_url;
            }
            return $CertificateFiles;
        }
        return "";
    }

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class)->withPivot('level');
    }

    // Customer's favorites
    public function favoriteMasters()
    {
        return $this->belongsToMany(User::class, 'master_favorites', 'customer_id', 'master_id')->withTimestamps();
    }

    // Masters who are favorited by customers
    public function favoritedByCustomers()
    {
        return $this->belongsToMany(User::class, 'master_favorites', 'master_id', 'customer_id')->withTimestamps();
    }

    // Reviews
    public function givenReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewed_id');
    }

    // For Masters: who viewed me
    public function viewsReceived()
    {
        return $this->hasMany(MasterView::class, 'master_id');
    }

    // For Customers: whose profiles did I view
    public function viewsGiven()
    {
        return $this->hasMany(MasterView::class, 'customer_id');
    }

    // Conversation
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants');
    }

    // Customer request 
    public function customerRequest(){
        return $this->belongsToMany(User::class, 'customer_requests', 'master_id', 'customer_id')->withTimestamps()->withPivot('request_type');;
    }

    public function requestedMasters()
    {
        return $this->belongsToMany(User::class, 'customer_requests', 'customer_id', 'master_id')
            ->withTimestamps()
            ->withPivot('request_type');
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class, 'user_id')
            ->whereIn('status', ['active', 'active_cancelled', 'in_grace'])
            ->where('end_date', '>', now())
            ->whereHas('plan', function($query) {
                $query->where('plan_slug', config('constant.plan_name.premium'));
            })
            ->latestOfMany();
    }

}
