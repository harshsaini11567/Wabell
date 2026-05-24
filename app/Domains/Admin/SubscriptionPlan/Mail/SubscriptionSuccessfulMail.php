<?php

namespace App\Domains\Admin\SubscriptionPlan\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionSuccessfulMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $plan;
    public $billingCycle;
    public $startDate;
    public $endDate;
    public $price;
    public $language;
    
    public function __construct($user, $plan, $billingCycle, $startDate, $endDate, $price)
    {
        $this->user = $user;
        $this->plan = $plan;
        $this->billingCycle = $billingCycle;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->price = $price;
        $this->language = $user->language;
    }

    public function build()
    {
        return $this->subject(trans('emails.subscription_activation_mail_master.subject', [], $this->language))
                    ->view('Layouts::emails.subscription.subscription_success');
    }
}
