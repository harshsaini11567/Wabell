<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Domains\Core\Subscription\Models\UserSubscription;
use App\Domains\Core\Subscription\Models\Transactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Domains\Admin\SubscriptionPlan\Mail\SubscriptionSuccessfulMail;
use App\Domains\Admin\SubscriptionPlan\Mail\NewSubscriptionNotificationMail;
use App\Domains\Core\Subscription\Models\Plan;
use App\Domains\Core\User\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;

class RenewSubscriptions extends Command
{
    use Dispatchable;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:renew-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically renew subscription';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiringSubs = UserSubscription::with('plan')->where('status', 'active')
            ->whereDate('end_date', '<=', now()->addDays(1)) // expiring today or tomorrow
            ->where('auto_renew', 1)                       // only those who enabled auto-renew
            ->whereHas('plan', function ($q) {
                $q->where('is_active', 1);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         // only if plan is still active
            })
            ->get();
        foreach ($expiringSubs as $subscription) {
            $transaction = $subscription->transactions;
            if (!$transaction || empty($transaction->payment_data)) {
                Log::warning('No previous transaction/payment_data for subscription', ['subscription_id' => $subscription->id]);
                continue;
            }
            $paymentData = is_array($transaction->payment_data)
                    ? $transaction->payment_data
                    : (json_decode($transaction->payment_data, true) ?? []);

            $token = $paymentData['registrationId'] ?? $transaction['registration_id'] ?? null;       
            if (!$token) {
                Log::warning('Missing registration token for subscription', ['subscription_id' => $subscription->id]);
                continue;
            }
            $this->chargeRecurring($subscription, $transaction, $token);
        }
    }

    private function chargeRecurring($subscription, $transaction, $token)
    {
        $paymentData = is_array($transaction->payment_data)
                    ? $transaction->payment_data
                    : (json_decode($transaction->payment_data, true) ?? []);
        $paymentBrand = strtoupper($paymentData['paymentBrand'] ?? 'VISA');
        $entityId = match ($paymentBrand) {
            'MADA' => config('services.hyperpay.entity_id_mada'),
            default => config('services.hyperpay.entity_id_visa'), // for VISA, MASTER, etc.
        };

        $amount = $subscription->billing_cycle === 'monthly'
                ? $subscription->plan->monthly_price
                : $subscription->plan->yearly_price;

        $url = rtrim(config('services.hyperpay.base_url'), '/') . "/v1/registrations/{$token}/payments";

        $payload = [
            'entityId'    => $entityId,
            'amount'      => $amount,
            'currency'    => config('services.hyperpay.currency'),
            'paymentType' => 'DB',
            'recurringType' => 'REPEATED',
            'standingInstruction.type'   => 'UNSCHEDULED',
            'standingInstruction.mode'   => 'REPEATED',
            'standingInstruction.source' => 'MIT',
        ];

        if (config('services.hyperpay.payment_mode') === 'Test') {
            $payload['testMode'] = 'EXTERNAL';
        }

        $response = Http::withToken(config('services.hyperpay.access_token'))
        ->asForm()
        ->post($url, $payload);

        $result = json_decode($response, true);
        Log::info('Recurring payment result', [
            'subscription_id' => $subscription->id,
            'result' => $result,
        ]);
        
        $transactionId = $result['id'];
        $response = Http::withToken(config('services.hyperpay.access_token'))
                ->get(config('services.hyperpay.base_url')."/v1/payments/{$transactionId}?entityId=" . $entityId);     
        $data = json_decode($response, true);
        Log::info('Recurring payment status', $data); 

        if (isset($data['result']['code']) && str_starts_with($data['result']['code'], '000.100')) {
            $subscription->update([
                'status' => 'expired',
                'end_date' => now()
            ]);

            $startDate = now();
            $endDate = match ($subscription->billing_cycle) {
                'monthly' => $startDate->copy()->addMonth(),
                'yearly'  => $startDate->copy()->addYear(),
                default   => $startDate->copy()->addMonth(),
            };

            $newSubscription = UserSubscription::create([
                'user_id'       => $subscription->user_id,
                'plan_id'       => $subscription->plan_id,
                'status'        => 'active',
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'billing_cycle' => $subscription->billing_cycle,
                'price'         => $amount,
                'plan_data'     => $subscription->plan_data,
                'user_data'     => $subscription->user_data,
            ]);

            Transactions::create([
                'user_id' => $subscription->user_id,
                'subscription_id'=> $newSubscription->id,
                'payment_id' => $result['id'] ?? null,
                'registration_id' => $result['registrationId'] ?? $token,
                'amount' => $amount,
                'payment_status' => 'paid',
                'payment_data'  => $result,
                'user_data' => $subscription->user->toArray(),
            ]);
            $plan = Plan::where('id', $subscription->plan_id)->first();
            $master = User::where('id', $subscription->user_id)->first();
            Mail::to($master->email)->send(
                new SubscriptionSuccessfulMail(
                    $master,
                    $plan,
                    $subscription->billing_cycle,
                    $startDate,
                    $endDate,
                    $amount
                )
            );
            $superAdmin = User::whereHas('roles', function ($query) {
                    $query->where('name_en', 'Super Admin');
                })->first();
            Mail::to(getSetting('support_email'))->send(
                new NewSubscriptionNotificationMail(
                    $superAdmin['name'],
                    $master,
                    $plan,
                    $subscription->billing_cycle,
                    $startDate,
                    $endDate,
                    $amount
                )
            );
        }
        elseif (str_starts_with($data['result']['code'], '000.200')) {
            // Payment Pending
            Transactions::create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'payment_id' => $data['id'] ?? null,
                'registration_id' => $token,
                'amount' => $amount,
                'payment_status' => 'pending',
                'payment_data' => $result,
                'user_data' => $subscription->user->toArray(),
            ]);
            self::dispatch($subscription->id, $token)
                    ->delay(now()->addMinutes(2));
        }
        else {
            // Payment failed
            $subscription->update(['status' => 'cancelled']);

            Transactions::create([
                'user_id'         => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'payment_id'      => $result['id'] ?? null,
                'registration_id' => $token,
                'amount'          => $amount,
                'payment_status'  => 'failed',
                'payment_data'    => $result,
                'user_data'       => $subscription->user->toArray(),
            ]);
        }
    }
}
