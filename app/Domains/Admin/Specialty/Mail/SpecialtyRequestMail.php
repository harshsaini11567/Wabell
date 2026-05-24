<?php

namespace App\Domains\Admin\Specialty\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SpecialtyRequestMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name, $email, $subject, $language;
    protected $specialty_request_url;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $language, $email, $subject, $specialty_request_url)
    {
        $this->name = $name;
        $this->language = $language;
        $this->email = $email;
        $this->subject = $subject;
        $this->specialty_request_url = $specialty_request_url;
    }

    public function build()
    {
        return $this->markdown('Layouts::emails.specialty.request-specialty', [
                'name' => $this->name,
                'language' =>$this->language,
                'specialty_request_url' => $this->specialty_request_url,
            ])->subject($this->subject);
    }
}
