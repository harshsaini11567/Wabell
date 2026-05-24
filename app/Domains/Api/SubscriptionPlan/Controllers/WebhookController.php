<?php

namespace App\Domains\Api\SubscriptionPlan\Controllers;

use App\Domains\Admin\SubscriptionPlan\Mail\NewSubscriptionNotificationMail;
use App\Domains\Admin\SubscriptionPlan\Mail\SubscriptionCancelledMail;
use App\Domains\Admin\SubscriptionPlan\Mail\SubscriptionExpiredMail;
use App\Domains\Admin\SubscriptionPlan\Mail\SubscriptionExpiredRenewMail;
use App\Domains\Admin\SubscriptionPlan\Mail\SubscriptionSuccessfulMail;
use App\Http\Controllers\APIController;
use App\Domains\Core\Subscription\Models\Plan;
use App\Domains\Core\Subscription\Models\UserSubscription;
use App\Domains\Core\Subscription\Models\Transactions;
use Illuminate\Http\Request;
use App\Domains\Core\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class WebhookController extends APIController
{
    public function handle(Request $request)
    {
        // Optional security check
        if ($request->password !== config('services.apple.shared_secret')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $type = $request->notification_type;
        $info = $request->latest_receipt_info;

        if (!$info) {
            return response()->json(['status' => 'ignored']);
        }

        // Only process renewal / purchase events
        if (!in_array($type, ['DID_RENEW', 'INTERACTIVE_RENEWAL'])) {
            return response()->json(['status' => 'ignored']);
        }

        $subscription = UserSubscription::where('transaction_id', $info['transaction_id'])->first();

        if (!$subscription) {
            return response()->json(['status' => 'not_found']);
        }
        

        switch ($type) {
            case 'DID_RENEW':
            case 'INTERACTIVE_RENEWAL':
                $this->updateSubscription($info);

                break;

            /**
             * ❌ FAIL (Billing issue)
             * Keep active until expiry
             */
            case 'DID_FAIL_TO_RENEW':
                $subscription = UserSubscription::where('transaction_id', $info['transaction_id'])->where('status', 'active');
                $userId = $subscription->user_id;
                $subscription->update(['status' => 'in_grace']);

                $master = User::with('masterDetail')->find($userId);

                // send mail to client regarding subscription expired due to renew fail
                Mail::to($master->email)->send(new SubscriptionExpiredRenewMail($master));  

                break;

            /**
             * ⏰ EXPIRED
             */
            case 'EXPIRED':
                $subscription = UserSubscription::where('transaction_id', $info['transaction_id'])->where('status', 'active');
                $userId = $subscription->user_id;
                $subscription->update(['status' => 'expired']);

                $master = User::with('masterDetail')->find($userId);

                // send mail to client regarding subscription expired due to subscription expire
                Mail::to($master->email)->send(new SubscriptionExpiredMail($master));  

                break;

            /**
             * 🚫 CANCEL
             * User cancelled auto-renew
             * Access continues until expires_at
             */
            /* case 'CANCEL':

                $subscription = UserSubscription::where('transaction_id', $info['transaction_id'])->where('status', 'active');
                $userId = $subscription->user_id;
                $subscription->update(['status' => 'cancelled']);

                $master = User::with('masterDetail')->find($userId);

                // send mail to client regarding subscription expired due to subscription cancelled
                Mail::to($master->email)->send(new SubscriptionCancelledMail($master));  
                break; */

            default:
                return response()->json(['status' => 'ignored']);
        }
        return response()->json(['status' => 'ok']);
    }


    private function updateSubscription($info){
         // Prevent duplicate renewal
        if (UserSubscription::where('transaction_id', $info['transaction_id'])->exists()) {
            return response()->json(['status' => 'duplicate']);
        }

        $startDate = Carbon::createFromTimestampMs($info['purchase_date_ms'], 'UTC')->setTimezone('UTC')->setTimezone(config('app.timezone'));
        $endDate = Carbon::createFromTimestampMs($info['expires_date_ms'], 'UTC')->setTimezone('UTC')->setTimezone(config('app.timezone'));

        // Expire previous active subscription
        $subscription = UserSubscription::where('original_transaction_id', $info['original_transaction_id'])->where('status', 'active')->first();
        $userId = $subscription->user_id;
        $subscription->update(['status' => 'expired']);

        $master = User::with('masterDetail')->find($userId);
        $isMaster = $master->roles()->where('name_en', 'Master')->exists();
        if (!$isMaster) {
            return $this->apiError(trans('messages.only_master_can_add_subscription'));
        }
        $masterDetails = $master->masterDetail;

        $plan = Plan::where('ios_product_id', $info['product_id'])->first();

        $price = $plan->monthly_price;
        $billingCycle = "monthly";

        // Create Subscription
        $userSubscription = UserSubscription::create([
            'user_id' => $userId,
            'plan_id'  => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'billing_cycle' => $billingCycle,
            'original_transaction_id' => $info['original_transaction_id'],
            'transaction_id' => $info['transaction_id'],
            'price' => $price,
            'plan_data' => $plan->toArray(),
            'user_data' => $masterDetails->toArray(),
        ]);

        // create Transaction Record
        $userSubscriptionId = $userSubscription->id;
        Transactions::create([
            'user_id' => $userId,
            'subscription_id' => $userSubscriptionId,
            'payment_id' => $info['transaction_id'],
            'registration_id' => $info['transaction_id'] ?? null,
            'amount' => $price,
            'payment_status' => 'paid',
            'payment_method' => 'app_store',
            'payment_data'   => $info,
            'user_data' => $masterDetails->toArray(),
        ]);

        // Update if Master Ban
        if ($master->is_ban == 1) {
            $master->update(['is_ban' => 0]);

            sendUserNotification($master->id, 'master_unbanned_title', 'master_sub_unbanned_message', 'subscription_ban_status');
        }

        // Send Mail To Master
        Mail::to($master->email)->send(new SubscriptionSuccessfulMail($master, $plan, $billingCycle, $startDate, $endDate, $price));        
        
        // Send Mail To Super Admin
        $superAdmin = User::whereHas('roles', function ($query) { $query->where('name_en', 'Super Admin');})->first();
        Mail::to(getSetting('support_email'))->send(new NewSubscriptionNotificationMail($superAdmin['name'], $master, $plan, $billingCycle, $startDate, $endDate, $price));
    }
}