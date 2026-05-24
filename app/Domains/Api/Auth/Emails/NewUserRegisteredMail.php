<?php

namespace App\Domains\Api\Auth\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $username;
    public $userEmail;
    public $subject;
    public $superAdminName;
    public $role;
    public $phoneNumber;
    public $language;

    public function __construct($superAdminName, $superAdminLanguage, $username, $userEmail, $role, $subject, $phoneNumber)
    {
        $this->superAdminName = $superAdminName;
        $this->username = $username;
        $this->userEmail = $userEmail;
        $this->subject = $subject;
        $this->role = $role;
        $this->phoneNumber = $phoneNumber;
        $this->language = $superAdminLanguage;
    }

    public function build()
    {
        return $this->markdown('Layouts::emails.auth.new_user_registered', [
                'name' => $this->superAdminName,
                'username' => $this->username,
                'userEmail' => $this->userEmail,
                'role'  => $this->role,
                'phone_number' => $this->phoneNumber,
                'language' => $this->language,
            ])->subject($this->subject);
    }
}
