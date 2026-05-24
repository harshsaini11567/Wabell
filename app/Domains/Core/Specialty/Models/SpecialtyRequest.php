<?php

namespace App\Domains\Core\Specialty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Core\User\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Domains\Admin\Specialty\Mail\NewSpecialtyMail;
class SpecialtyRequest extends Model
{
    use SoftDeletes;
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'uuid',
        'name_en',
        'name_ar',
        'message_en',
        'message_ar',
        'user_info',
        'user_role',
        'created_by',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function boot ()
    {
        parent::boot();
        static::creating(function(SpecialtyRequest $model) {
            $model->uuid = Str::uuid();

            // $model->created_by = auth('api')->user()->id;
        });
    }

    public function notifyUsersOnAcceptance($user_email,$user_name, $user_language, $newSpecialty)
    {
        $superAdmin = User::whereHas('roles', function ($query) {
            $query->where('name_en', 'Super Admin');
        })->first();
        $superAdminEmail = $superAdmin['email'] ?? '';
        $requestingUser = User::where('email', $user_email)
            ->whereHas('roles', function($q) {
                $q->whereIn('id', [
                    config('constant.roles.master'),
                    config('constant.roles.customer'),
                ]);
            })->first();

        if ($requestingUser) {
            $locale = $requestingUser['language'] ?? 'en';
            $subject = trans('emails.speciality_update_mail_user.subject',[],$locale);
            $column = 'name_' . $locale;
            $specialty = $newSpecialty[$column];
            $localizedName = $specialty;
            sendUserNotification(
                $requestingUser['id'],
                'specialty_request_accepted_title',
                'specialty_request_accepted_body',
                'specialty',
                null,
                false,
                ['name' => $localizedName]
            );
            Mail::to($requestingUser['email'])->send(new NewSpecialtyMail($user_name, $superAdminEmail, $subject, $localizedName, $locale));

        }
        else {
            // Guest
            $subject = trans('emails.speciality_update_mail_user.subject',[],$user_language);
            $column = 'name_'.$user_language;
            $localizedName = $newSpecialty[$column];
            Mail::to($user_email)->send(new NewSpecialtyMail($user_name, $superAdminEmail, $subject, $localizedName, $user_language));
        }

        // Notify all other masters
        $masters = User::whereHas('roles', function($q) {
            $q->where('id', config('constant.roles.master'));
        })->when($requestingUser?->id, function ($q) use ($requestingUser) {
            $q->where('id', '!=', $requestingUser->id);
        })->get();

        foreach ($masters as $master) {
            $locale = $master->language ?? 'en';
            $column = 'name_' . $locale;
            $localizedName = $this->$column;

            sendUserNotification(
                $master->id,
                'specialty_notification_title',
                'specialty_notification_body',
                'specialty',
                null,
                false,
                ['name' => $localizedName]
            );
        }
    }
}
