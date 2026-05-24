<?php

namespace App\Domains\Admin\Specialty\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SpecialtyAddedMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name, $email, $subject, $language;
    protected $specialty_name;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $language, $email, $subject, $specialty_name)
    {
        $this->name = $name;
        $this->language = $language;
        $this->email = $email;
        $this->subject = $subject;
        $this->specialty_name = $specialty_name;
    }

    public function build()
    {
        return $this->markdown('Layouts::emails.specialty.new-specialty-added', [
                'name' => $this->name,
                'language' => $this->language,
                'specialty_name' => $this->specialty_name,
            ])->subject($this->subject);
    }
}
