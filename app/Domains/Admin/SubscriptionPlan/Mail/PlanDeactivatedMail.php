<?php

namespace App\Domains\Admin\SubscriptionPlan\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PlanDeactivatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $plan;
    public $user;
    public $endDate;
    public $language;
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
        return $this->subject(trans('emails.subscription_plan_deactivated_master.subject',[],$this->language))
                    ->view('Layouts::emails.subscription.plan_deactivated')
                    ->with([
                        'plan'    => $this->plan,
                        'user'    => $this->user,
                        'endDate' => $this->endDate,
                        'language' => $this->language,
                    ])
                    ;
    }
}
