<?php

namespace App\Domains\Api\Auth\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMailMaster extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $subject, $language;

    public function __construct($user, $subject)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->language = $user->language;
    }

    public function build()
    {
        return $this->markdown('Layouts::emails.auth.welcome_user_master', [
                'user' => $this->user,
                'language' => $this->language,
            ])->subject($this->subject);
    }
}
