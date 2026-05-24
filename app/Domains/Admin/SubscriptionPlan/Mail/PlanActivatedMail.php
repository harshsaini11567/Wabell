<?php

namespace App\Domains\Admin\SubscriptionPlan\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PlanActivatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $plan;
    public $user;
    public $endDate, $language;
    /**
     * Create a new message instance.
     */
    public function __construct($plan, $user)
    {
        $this->plan = $plan;
        $this->user = $user;
        $this->endDate = optional(
            $user->activeSubscription()
                ->where('plan_id', $plan->id)
                ->where('end_date', '>', now())
                ->first()
        )->end_date;
        $this->language = $user['language'];
    }

    public function build()
    {
        return $this->subject(trans('emails.subscription_plan_activated_master.subject', [], $this->language))
                    ->view('Layouts::emails.subscription.plan_activated');
    }
}
