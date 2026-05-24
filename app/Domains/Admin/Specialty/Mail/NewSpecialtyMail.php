<?php

namespace App\Domains\Admin\Specialty\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewSpecialtyMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name, $email, $subject,$language, $specialty_name;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $email, $subject, $specialty_name, $language)
    {
        $this->name = $name;
        $this->email = $email;
        $this->subject = $subject;
        $this->specialty_name = $specialty_name;
        $this->language = $language;
    }

    public function build()
    {
        return $this->markdown('Layouts::emails.specialty.new-specialty-update', [
                'name' => $this->name,
                'specialty_name' => $this->specialty_name,
            ])->subject($this->subject);
    }
}
