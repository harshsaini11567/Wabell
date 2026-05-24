<?php

namespace App\Domains\Admin\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $subject, $language;
    public $reset_password_url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user,$reset_password_url,$subject)
    {
        $this->user = $user;
        $this->reset_password_url = $reset_password_url;
        $this->subject = $subject;
        $this->language = $user->language;

    }

   /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Auth::emails.auth.reset-password', [
                'user' => $this->user,
                'reset_password_url' => $this->reset_password_url,
                'language' => $this->language,
            ])->subject($this->subject);
    }
}
