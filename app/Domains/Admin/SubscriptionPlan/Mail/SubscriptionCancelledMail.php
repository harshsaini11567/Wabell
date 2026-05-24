<?php

namespace App\Domains\Admin\SubscriptionPlan\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelledMail extends Mailable
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
        return $this->subject(trans('emails.master_subscription_cancelled_mail.subject', [], $this->language))
                    ->view('Layouts::emails.subscription.subscription_cancelled');
    }
}
