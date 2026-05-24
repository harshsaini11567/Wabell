<?php

namespace App\Domains\Api\Auth\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user,$token, $subject , $expiretime, $language;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user,$token, $subject , $expiretime)
    {
        $this->user = $user;
        $this->token = $token;
        $this->subject = $subject;
        $this->expiretime = $expiretime;
        $this->language = $user->language;
    }

   /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Auth::emails.auth.forgot_password_otp', [
            'user' => $this->user ,
            'token' => $this->token ,
            'language' => $this->language,
            'expiretime' => $this->expiretime])->subject($this->subject);
    }
}
