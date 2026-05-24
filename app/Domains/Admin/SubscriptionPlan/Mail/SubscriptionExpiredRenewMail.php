<?php

namespace App\Domains\Admin\SubscriptionPlan\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiredRenewMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $language;
    
    public function __construct($user)
    {
        $this->user = $user;
        $this->language = $user->language ?? 'en';
    }

    public function build()
    {
        return $this->subject(trans('emails.master_subscription_expired_renew_mail.subject', [], $this->language))
                    ->view('Layouts::emails.subscription.subscription_expired_renew');
    }
}
