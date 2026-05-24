<?php

namespace App\Domains\Api\Auth\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserSelectedOtherLocationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user,$subject, $role, $language;

    public function __construct($user, $language, $subject, $role)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->role = $role;
        $this->language = $language;
    }

    public function build()
    {
        $cityName = ($this->user->city_id == 0)
        ? trans('constant.other', [], $this->language)
        : ($this->user->city?->{'name_' . $this->language} ?? $this->user->city?->name_en);

        $neighborhoodName = ($this->user->neighborhood_id == 0)
        ? trans('constant.other', [], $this->language)
        : ($this->user->neighborhood?->{'name_' . $this->language} ?? $this->user->neighborhood?->name_en);

        return $this->markdown('Layouts::emails.auth.user_selected_other_location_mail', [
                'user' => $this->user,
                'role' => $this->role,
                'language' => $this->language,
                'city' => $cityName,
                'neighborhood' => $neighborhoodName,
            ])->subject($this->subject);
    }
}
