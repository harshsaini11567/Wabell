<?php

namespace App\Domains\Api\Auth\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ThirdPartyUpgradeNotice;

class UserBanStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $name;
    public $language;
    public $subject;
    public $supportEmail;

    public function __construct($name, $language,$subject, $supportEmail)
    {

        $this->name = $name;
        $this->language = $language;
        $this->subject = $subject;
        $this->supportEmail;
    }

    public function build()
    {
        return $this->markdown('Layouts::emails.auth.user_ban_status', [
                'name' => $this->name,
                'language' => $this->language,
                'supportEmail' => $this->supportEmail,
            ])->subject($this->subject);
    }
}
