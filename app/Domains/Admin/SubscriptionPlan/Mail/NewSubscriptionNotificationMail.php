<?php

namespace App\Domains\Admin\SubscriptionPlan\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewSubscriptionNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $superadminName;
    public $user;
    public $plan;
    public $billingCycle;
    public $startDate;
    public $endDate;
    public $price;
    public $language;
    /**
     * Create a new message instance.
     */
    public function __construct($superadminName, $user, $plan, $billingCycle, $startDate, $endDate, $price)
    {
        $this->superadminName = $superadminName;
        $this->user = $user;
        $this->plan = $plan;
        $this->billingCycle = $billingCycle;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->price = $price;
        $this->language = 'ar';
    }

    public function build()
    {
        return $this->subject(trans('emails.subscription_activation_mail_super_admin.subject',[],$this->language))
                    ->view('Layouts::emails.subscription.new_subscription_notification');
    }
}
