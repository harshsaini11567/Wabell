<?php

namespace App\Domains\Api\SubscriptionPlan\Controllers;
use App\Http\Controllers\APIController;
use App\Domains\Core\Subscription\Models\Plan;
use App\Domains\Core\Subscription\Models\UserSubscription;
use App\Domains\Core\Subscription\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Domains\Admin\SubscriptionPlan\Mail\SubscriptionSuccessfulMail;
use App\Domains\Admin\SubscriptionPlan\Mail\NewSubscriptionNotificationMail;
use Illuminate\Support\Facades\Mail;
use App\Domains\Core\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SubscriptionPlanController extends APIController
{
    public function subscriptionList(Request $request){
        try{
            $locale = App::getLocale();
            $master = $request->user();
            /* $activeSubscription = UserSubscription::where('user_id', $master->id)->where('end_date', '>', now())->where('status', 'active')->latest()->first(); */
            
            $activeSubscription = $master->activeSubscription;

            $plans = Plan::with('planImage','verifiedIcon')->where('is_active', 1)->get()->map(function ($plan) use ($locale, $activeSubscription, $master) {
                $isActive = $activeSubscription && $activeSubscription->plan_id == $plan->id;
                $remainingDays = null;
                if ($isActive) {
                    if (now()->lt($activeSubscription->end_date)) {
                        // Force integer difference in days
                        $daysLeft = round(now()->diffInRealDays($activeSubscription->end_date));
                        if ($locale === 'ar') {
                            // Arabic doesn’t use plural in the same way as English
                            $dayWord = $daysLeft == 1 ? __('messages.day_name.day') : __('messages.day_name.days');
                            $remainingDays = $daysLeft . ' ' . $dayWord;
                        } else {
                            // English
                            $dayWord = \Illuminate\Support\Str::plural(__('messages.day_name.day'), $daysLeft);
                            $remainingDays = $daysLeft . ' ' . $dayWord;
                        }
                    } else {
                        $remainingDays = $activeSubscription->start_date->translatedFormat('d F Y') . ' to ' .$activeSubscription->end_date->translatedFormat('d F Y');
                    }
                }
                $transaction = $isActive ? Transactions::where('subscription_id', $activeSubscription->id)->latest()->first() : null;
                    
                // Extract payment details if available
                $paymentBrand = null;
                $lastDigits = null;
                $paymentIcon = null;
                if ($transaction) {
                    $paymentData = is_array($transaction->payment_data) ? $transaction->payment_data : json_decode($transaction->payment_data, true);
                    $paymentBrand = $paymentData['paymentBrand'] ?? null;
                    $lastDigits   = $paymentData['card']['last4Digits'] ?? null;
                    $brandIcons = config('constant.brand_icons');
                    if ($paymentBrand && isset($brandIcons[strtoupper($paymentBrand)])) {
                        $paymentIcon = asset($brandIcons[strtoupper($paymentBrand)]);
                    }
                }

                $data = [
                    'plan_id' => $plan->id,
                    'user_id' => $master->uuid,
                ];

                $signature = hash_hmac('sha256', implode('|', $data), config('app.key'));
                $paymentLink = route('hyperpay.payment.form').'?' . http_build_query([
                    'plan_id' => $plan->id,
                    'user_id' => $master->uuid,
                    'signature' => $signature,
                ]);

                if(!$activeSubscription || ($activeSubscription && $plan->plan_slug != 'basic_plan')){
                    return [
                        'id' => $plan->id,
                        'name' => $plan->{"name_{$locale}"},
                        'description' => $plan->{"features_{$locale}"},
                        'monthly_price' => $plan->monthly_price,
                        'yearly_price' => $plan->yearly_price,
                        'plan_image' => $plan->plan_image_url,
                        'verified_icon' => $plan->verified_icon_url,
                        'start_date' => $isActive ? $activeSubscription->start_date->translatedFormat('d F Y') : null,
                        'end_date' => $isActive ? $activeSubscription->end_date->translatedFormat('d F Y') : null,
                        'remaining_days' => $remainingDays,        
                        'status'    => ($plan->plan_slug == 'basic_plan' ? 'avitve' : ($isActive ? $activeSubscription->status : null)),
                        'is_active' => $plan->plan_slug == 'basic_plan' ? true : $isActive,
                        'payment_brand' => $paymentBrand,
                        'card_last_digit' => $lastDigits,
                        'payment_icon'    => $paymentIcon,
                        'payment_link'  => $plan->plan_slug == 'basic_plan' ? '' : $paymentLink,
                        'basic_plan' => $plan->plan_slug == 'basic_plan' ? true : false,
                    ];
                }
            })->filter()->values();

            return $this->apiSuccess(['plans' => $plans]);
        }
        catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function subscriptionStore(Request $request){
        try{
            $request->validate([
                'billing_cycle' => ['required', 'in:'.implode(',', array_keys(config('constant.plan_billing_cycle')))],
                'plan_id'  => ['required','exists:plans,id,deleted_at,NULL'],
                'payment_id'     => ['required', 'string', 'max:255'],
                'registration_id' => ['nullable', 'string', 'max:225'],
                'amount'         => ['required', 'numeric'],
                'payment_status' => ['required', 'string'],
                'payment_data'   => ['nullable'],
            ],[],[
                'billing_cycle' => trans('cruds.api.billing_cycle'),
                'plan_id' => trans('cruds.api.plan_id'),
                'payment_id' => trans('cruds.api.payment_id'),
                'registration_id' => trans('cruds.api.registration_id'),
                'amount' => trans('cruds.api.amount'),
                'payment_status' => trans('cruds.api.payment_status'),
                'payment_data' => trans('cruds.api.payment_data'),
            ]);
            $master = $request->user();
            $language = $master['language'];
            $isMaster = $master->roles()->where('name_en', 'Master')->exists();
            if (!$isMaster) {
                return $this->apiError(trans('messages.only_master_can_add_subscription'));
            }
            $masterDetails = $master->load('masterDetail');
            /* $activeSubscription = UserSubscription::where('user_id', $master->id)->where('end_date', '>', now())->latest()->first(); */
            
            $activeSubscription = $master->activeSubscription;

            if ($activeSubscription) {
                $activeSubscription->update(['end_date' => now(), 'status'   => 'cancelled']);
            }
            $startDate = now();
            $price = '';
            $plan = Plan::where('id', $request->plan_id)->first();
            if ($request->billing_cycle === 'monthly') {
                $endDate = $startDate->copy()->addDays(30);
                $price = $plan->monthly_price;
            } elseif ($request->billing_cycle === 'yearly') {
                $endDate = $startDate->copy()->addDays(365);
                $price = $plan->yearly_price;
            } else {
                $endDate = $startDate; // default (or handle other cases)
                $price = '';
            }
            $userSubscription = UserSubscription::create([
                'user_id' => $request->user()->id,
                'plan_id'  => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'billing_cycle' => $request->billing_cycle,
                'price' => $price,
                'plan_data' => $plan->toArray(),
                'user_data' => $masterDetails->toArray(),
            ]);
            $userSubscriptionId = $userSubscription->id;
            $transaction = Transactions::create([
                'user_id' => $request->user()->id,
                'subscription_id' => $userSubscriptionId,
                'payment_id' => $request->payment_id,
                'registration_id' => $request->registration_id ?? null,
                'amount' => $request->amount,
                'payment_status' => $request->payment_status,
                'payment_data'   => $request->payment_data,
                'user_data' => $masterDetails->toArray(),
            ]);
            if ($master->is_ban == 1) {
                $master->update(['is_ban' => 0]);

                sendUserNotification(
                    $master->id,
                    'master_unbanned_title',
                    'master_sub_unbanned_message',
                    'subscription_ban_status',
                );
            }
            Mail::to($master->email)->send(
                new SubscriptionSuccessfulMail(
                    $master,
                    $plan,
                    $request->billing_cycle,
                    $startDate,
                    $endDate,
                    $price
                )
            );
            $superAdmin = User::whereHas('roles', function ($query) {
                    $query->where('name_en', 'Super Admin');
                })->first();
            Mail::to($superAdmin['email'])->send(
                new NewSubscriptionNotificationMail(
                    $superAdmin['name'],
                    $master,
                    $plan,
                    $request->billing_cycle,
                    $startDate,
                    $endDate,
                    $price
                )
            );
            return $this->apiSuccess(trans('messages.subscription_payment_added', ['plan_name' => $plan->{'name_'.$language}]));            
        }
        catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function verifyApplePurchase(Request $request){
        $request->validate([
            'receipt_data' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $url = config('services.apple.url')."verifyReceipt";

            $receiptData = $request->receipt_data;

            Log::info('receiptData Data:', ['receiptData' => $receiptData]);

             // Verify with Apple API
            $response = Http::post($url, [
                'receipt-data' => $receiptData,
                'password' => config('services.apple.shared_secret'),
                'exclude-old-transactions' => true
            ]);

            $data = $response->json();

            // Handle sandbox redirect
            if ($data['status'] == 21007) {
                $response = Http::post($url, [
                    'receipt-data' => $receiptData,
                    'password' => config('services.apple.shared_secret'),
                ]);
                $data = $response->json();
            }

            if ($data['status'] !== 0) {
                return response()->json(['error' => 'Invalid receipt'], 400);
            }

            Log::info('data:', ['data' => $data]);

            $latest = collect($data['receipt']['in_app'])->sortByDesc('purchase_date_ms')->first();
            $productId = $latest['product_id'];

            Log::info('latest Data:', ['latest' => $latest]);

            $startDate = Carbon::createFromTimestampMs($latest['purchase_date_ms'], 'UTC')->setTimezone(config('app.timezone'));
            $endDate   = Carbon::createFromTimestampMs($latest['expires_date_ms'], 'UTC')->setTimezone(config('app.timezone'));

            $master     = $request->user();
            $language   = $master->language;
            $billingCycle = "monthly";

            $isMaster = $master->roles()->where('name_en', 'Master')->exists();
            if (!$isMaster) {
                return $this->apiError(trans('messages.only_master_can_add_subscription'));
            }
            $masterDetails = $master->load('masterDetail');

            

            $plan = Plan::where('ios_product_id', $productId)->firstOrFail();
            $price = $plan->monthly_price;

            $activeSubscription = UserSubscription::where('user_id', $master->id)->where('status', 'active')->latest()->first();
            if ($activeSubscription && $activeSubscription->plan_id !== $plan->id) {
                $activeSubscription->update([
                    'status' => 'active_cancelled',
                ]);
            }

            // Create Subscription
            $userSubscription = UserSubscription::create([
                'user_id' => $master->id,
                'plan_id'  => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'billing_cycle' => $billingCycle,
                'original_transaction_id' => $latest['original_transaction_id'],
                'transaction_id' => $latest['transaction_id'],
                'price' => $price,
                'plan_data' => $plan->toArray(),
                'user_data' => $masterDetails->toArray(),
            ]);

            // create Transaction Record
            $userSubscriptionId = $userSubscription->id;
            Transactions::create([
                'user_id' => $master->id,
                'subscription_id' => $userSubscriptionId,
                'payment_id' => $latest['transaction_id'],
                'registration_id' => $latest['transaction_id'] ?? null,
                'amount' => $price,
                'payment_status' => 'paid',
                'payment_method' => 'app_store',
                'payment_data'   => $latest,
                'user_data' => $masterDetails->toArray(),
            ]);

            // Update if Master Ban
            if ($master->is_ban == 1) {
                $master->update(['is_ban' => 0]);

                sendUserNotification($master->id, 'master_unbanned_title', 'master_sub_unbanned_message', 'subscription_ban_status');
            }

            DB::commit();

            // Send Mail To Master
            Mail::to($master->email)->send(new SubscriptionSuccessfulMail($master, $plan, $billingCycle, $startDate, $endDate, $price));
            $superAdmin = User::whereHas('roles', function ($query) {
                $query->where('name_en', 'Super Admin');
            })->first();

            // Send Mail to Superadmin
            Mail::to($superAdmin['email'])->send(new NewSubscriptionNotificationMail($superAdmin['name'], $master, $plan, $billingCycle, $startDate, $endDate, $price));

            return $this->apiSuccess(trans('messages.subscription_payment_added', ['plan_name' => $plan->{'name_'.$language}]));
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info('Error:', ['error: ' => $th->getMessage(). ' line: '. $th->getLine()]);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function cancelPlan(Request $request, $id){
        try{
            $master = $request->user();
            $language = $master['language'];
            $isMaster = $master->roles()->where('name_en', 'Master')->exists();
            if (!$isMaster) {
                return $this->apiError(trans('messages.only_master_can_remove_subscription'));
            }
            /* $activeSubscription = UserSubscription::where('user_id', $master->id)->where('plan_id', $id)->where('end_date', '>', now())->latest()->first(); */
            
            $activeSubscription = $master->activeSubscription;
            if (!$activeSubscription) {
                return $this->apiError(trans('messages.no_active_subscription_found'));
            }
            $activeSubscription->update(['status' => 'active_cancelled', /* // 'end_date' => now(),'auto_renew' => 0, */]);    
            return $this->apiSuccess(trans('messages.subscription_cancelled_successfully',['plan_name' => $activeSubscription->plan->{'name_'.$language}]));
        }
        catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }

    }

    public function historyList(Request $request){
        try{
            $locale = App::getLocale();
            $master = $request->user();
            $isMaster = $master->roles()->where('name_en', 'Master')->exists();
                if (!$isMaster) {
                    return $this->apiError(trans('messages.only_master_can_see_history'));
                }
            $subscriptions = UserSubscription::with(['plan', 'plan.planImage', 'transactions'])
            ->where('user_id', $master->id)
            ->latest()
            ->get()
            ->map(function ($subscription) use ($locale) {
                $remainingDays = null;
                $isActive = false;

                if ($subscription->status === 'active') {
                    if (now()->lte($subscription->end_date)) {
                        $daysLeft = round(now()->diffInRealDays($subscription->end_date));
                         if ($locale === 'ar') {
                            // Arabic doesn’t use plural in the same way as English
                            $dayWord = $daysLeft == 1 ? trans('messages.day_name.day') : trans('messages.day_name.days');
                            $remainingDays = $daysLeft . ' ' . $dayWord;
                        } else {
                            // English
                            $dayWord = \Illuminate\Support\Str::plural(trans('messages.day_name.day'), $daysLeft);
                            $remainingDays = $daysLeft . ' ' . $dayWord;
                        }
                        // $remainingDays = $daysLeft . ' ' . \Illuminate\Support\Str::plural('day', $daysLeft);
                        $isActive = true;
                    } else {
                        if ($subscription->start_date->year === $subscription->end_date->year) {
                            $remainingDays = $subscription->start_date->translatedFormat('d F') . ' ' . trans('messages.to') . ' ' .
                                        $subscription->end_date->translatedFormat('d F Y');
                        }
                        else{
                            $remainingDays = $subscription->start_date->translatedFormat('d F Y') . ' ' . trans('messages.to') . ' ' .
                                        $subscription->end_date->translatedFormat('d F Y');
                        }
                    }
                } else {
                    // inactive or cancelled → show date range
                   if ($subscription->start_date->year === $subscription->end_date->year) {
                        $remainingDays = $subscription->start_date->translatedFormat('d F') . ' ' . trans('messages.to') . ' ' .
                                    $subscription->end_date->translatedFormat('d F Y');
                    }
                    else{
                        $remainingDays = $subscription->start_date->translatedFormat('d F Y') . ' ' . trans('messages.to') . ' ' .
                                    $subscription->end_date->translatedFormat('d F Y');
                    }
                }
                $paymentBrand = null;
                $lastDigits = null;
                $brandIconUrl = null;
               
                if ($subscription->transactions && $subscription->transactions->payment_data) {
                    $paymentDataRaw = $subscription->transactions->payment_data;
                    if (is_string($paymentDataRaw)) {
                        // $paymentData = json_decode($paymentDataRaw, true);
                        $paymentData = is_array($paymentDataRaw) ? $paymentDataRaw : json_decode($paymentDataRaw, true);
                    } else {
                        $paymentData = []; // fallback
                    }

                    if ($paymentData) {
                       $brandIcons = config('constant.brand_icons');

                        $paymentBrand = $paymentData['paymentBrand'] ?? null;
                        $lastDigits   = $paymentData['card']['last4Digits'] ?? null;

                        $brandIconUrl = $paymentBrand && isset($brandIcons[strtoupper($paymentBrand)])
                                        ? asset($brandIcons[strtoupper($paymentBrand)])
                                        : null;
                    }
                }

                return [
                    'id' => $subscription->plan->id ?? null,
                    'name' => $subscription->plan->{"name_{$locale}"} ?? null,
                    'description' => $subscription->plan->{"features_{$locale}"} ?? null,
                    'monthly_price' => $subscription->plan->monthly_price ?? null,
                    'yearly_price' => $subscription->plan->yearly_price ?? null,
                    'plan_image' => optional($subscription->plan)->plan_image_url  
                        ?? url('storage/plan/default.png'),
                    'start_date' => $subscription->start_date->translatedFormat('d F Y'),
                    'end_date' => $subscription->end_date->translatedFormat('d F Y'),
                    'remaining_days' => $remainingDays,
                    'status' => config('constant.subscription_status.' . $subscription->status),
                    'is_active' => $isActive,
                    'payment_brand'   => $paymentBrand,
                    'card_last_digit' => $lastDigits,
                    'payment_icon'    => $brandIconUrl,
                ];
            });
            return $this->apiSuccess(['history_list' => $subscriptions]);
        }
        catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function getCheckoutId(Request $request){
        try{
            $master = $request->user(); // current logged-in user
            $planId = $request->plan_id;
            $alreadySubscribed = UserSubscription::where('user_id', $master->id)
                ->where('plan_id', $planId)
                ->where('status', 'active')
                ->exists();

            if ($alreadySubscribed) {
                return $this->apiError(trans('messages.already_have_subscription'));
            }

            $paymentMethod = $request->payment_method ?? 'visa'; // Default to Visa if not provided
            // $entityId = $paymentMethod === 'mada' ? config('services.hyperpay.entity_id_mada') : config('services.hyperpay.entity_id_visa');
            switch ($paymentMethod) {
                case 'mada':
                    $entityId = config('services.hyperpay.entity_id_mada');
                    $paymentBrand = 'MADA';
                    break;
                case 'visa':
                    $entityId = config('services.hyperpay.entity_id_visa');
                    $paymentBrand = 'VISA';
                    break;
                case 'mastercard':
                    $entityId = config('services.hyperpay.entity_id_visa');
                    $paymentBrand = 'MASTER';
                    break;
                case 'applepay':
                    $entityId = config('services.hyperpay.entity_id_applepay');
                    $paymentBrand = 'APPLEPAY';
                    break;
                default:
                    $entityId = config('services.hyperpay.entity_id');
                    $paymentBrand = 'VISA';
            }

            $fullName = trim($master->name);

            $parts = explode(' ', $fullName, 2);

            $billingData = [
                'billing.street1' => $request->billing_address,
                'billing.city'    => $request->billing_city,
                'billing.state'   => $request->billing_state,
                'billing.country' => $request->billing_country, // must be 2-letter code
                'billing.postcode'=> $request->billing_postcode,
                'customer.givenName' => $parts[0] ?? $fullName,
                'customer.surname'   => $parts[1] ?? $fullName,
                'customer.email'     => $master->email,
            ];

            $payload  = [
                    'entityId'      => $entityId,
                    'amount'      => $request->amount,
                    'currency'    => config('services.hyperpay.currency'),
                    'paymentType' => 'DB',
                    'notificationUrl' => route('hyperpay.webhook'), // <-- Laravel helper
                    // 'merchantTransactionId' => $master->uuid, // optional but recommended
                    'merchantTransactionId' => Str::uuid(), // optional but recommended
                    'createRegistration' => true,
                    'paymentBrand'          => $paymentBrand,
                ];  
            if (in_array($paymentBrand, ['VISA', 'MASTER'])) {
                $payload['recurringType'] = 'INITIAL';
                $payload['descriptor'] = 'Wabell Subscription';
            }
            
            $payload = array_merge($payload, $billingData);

            if (config('services.hyperpay.mode') === 'Test') {
                $payload['customParameters[3DS2_enrolled]'] = 'true';
                $payload['customParameters[3DS2_flow]'] = 'challenge';
            }

            // dd($payload);
            $response = Http::withToken(config('services.hyperpay.access_token'))
                ->asForm()
                ->post(rtrim(config('services.hyperpay.base_url'), '/') . '/v1/checkouts', $payload);

            $result = $response->json();
            $data = [
                'id' => $result['id'] ?? null,
                // 'result' => $result
            ];
            return $this->apiSuccess($data);
        }
        catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function getPaymentStatus(Request $request, $checkoutId)
    {
        try{
            $paymentMethod = strtolower($request->payment_method ?? 'visa');
            // $entityId = $paymentMethod === 'mada'
            //         ? config('services.hyperpay.entity_id_mada')
            //         : config('services.hyperpay.entity_id_visa');
            switch ($paymentMethod) {
                case 'mada':
                    $entityId = config('services.hyperpay.entity_id_mada');
                    break;
                case 'visa':
                    $entityId = config('services.hyperpay.entity_id_visa');
                    break;
                case 'mastercard':
                    $entityId = config('services.hyperpay.entity_id_visa');
                    break;
                case 'applepay':
                    // fallback if not set
                    $entityId = config('services.hyperpay.entity_id_applepay');
                    break;
                default:
                    $entityId = config('services.hyperpay.entity_id');
            }
            Log::info('HyperPay entityId used', [$entityId]);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.hyperpay.access_token'),
            ])->get(config('services.hyperpay.base_url') . "/v1/checkouts/{$checkoutId}/payment", [
                'entityId' => $entityId,
            ]);

            return $this->apiSuccess($response->json());
        }
        catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function webhook(Request $request)
    {
        Log::info('HyperPay Webhook', $request->all());

        // Extract data
        $paymentId = $request->input('id');
        $resultCode = $request->input('result.code');
        $registrationId = $request->input('registrationId');

        // Find subscription by token
        $transaction = Transactions::where('registration_id', $registrationId)->latest()->first();

        if ($transaction) {
            // Create new transaction record
            Transactions::create([
                'user_id' => $transaction->user_id,
                'subscription_id' => $transaction->subscription_id,
                'payment_id' => $paymentId,
                'amount' => $transaction->amount,
                'payment_status' => ($resultCode === '000.000.000') ? 'paid' : 'failed',
                'payment_data' => $request->all(),
                'registration_id' => $registrationId,
            ]);

            // Update subscription end_date if success
            if ($resultCode === '000.000.000') {
                $subscription = $transaction->subscription;
                $subscription->update([
                    'end_date' => now()->addMonth(), // or yearly
                    'status' => 'active',
                ]);
            }
        }

        return response()->json(['message' => 'Webhook received']);
    }
}