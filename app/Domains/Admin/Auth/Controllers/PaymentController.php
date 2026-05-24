<?php

namespace App\Domains\Admin\Auth\Controllers;

use App\Domains\Admin\SubscriptionPlan\Mail\NewSubscriptionNotificationMail;
use App\Domains\Admin\SubscriptionPlan\Mail\SubscriptionSuccessfulMail;
use App\Domains\Core\Subscription\Models\Plan;
use App\Domains\Core\Subscription\Models\Transactions;
use App\Domains\Core\Subscription\Models\UserSubscription;
use App\Domains\Core\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    public function paymentForm(Request $request){

        $planId = $request->query('plan_id');
        $userId = $request->query('user_id');
        $cycle = $request->query('cycle');
        $checkoutId = $request->query('checkout_id');
        $language = $request->query('language');

        $signature = $request->query('signature');

        if (!$planId || !$userId || !$signature) {
            return abort(404);
        }

        $data = [
            'plan_id' => $planId,
            'user_id' => $userId
        ];

        $expectedSignature = hash_hmac('sha256', implode('|', $data), config('app.key'));
        if (!hash_equals($expectedSignature, $signature)) {
            return abort(403);
        }

        $planExists = Plan::where('id', $planId)->exists();
        if(!$planExists){
            return abort(404, "Plan Not found");
        }

        $user = User::where('uuid', $userId)->firstOrFail();
        if(!$user){
            return abort(404, "Master Not found");
        }
        

        $alreadySubscribed = UserSubscription::where('user_id', $user->id)
            ->where('plan_id', $planId)
            ->where('status', 'active')
            ->exists();

        if ($alreadySubscribed) {
            return abort(403, "Master already have this plan");
        }

        $paymentMethod = in_array($request->query('payment_method'), ['visa', 'mada', 'master', 'applepay']) ? $request->query('payment_method') : 'visa'; // Default to Visa if not provided
        switch ($paymentMethod) {
            case 'applepay':
                $brand = 'APPLEPAY';
                break;
            case 'mada':
                $brand = 'MADA';
                break;
            default:
                $brand = 'VISA MASTER';
                break;
        }

        $returnUrl = route('hyperpay.callback')."?plan_id={$planId}&user_id={$userId}&cycle={$cycle}&payment_method={$paymentMethod}&language={$language}";

        return view('Auth::payment.form', compact('checkoutId', 'returnUrl', 'brand'));
    }


    public function paymentCallback(Request $request)
    {
        $language = $request->input('language') ?? 'en';
        try {        
            $resourcePath = $request->input('resourcePath');
            
            $paymentMethod = $request->input('payment_method') ?? 'visa'; // Default to Visa if not provided
            // $entityId = $paymentMethod === 'mada' ? config('services.hyperpay.entity_id_mada') : config('services.hyperpay.entity_id_visa');
            switch ($paymentMethod) {
                case 'mada':
                    $entityId = config('services.hyperpay.entity_id_mada');
                    break;
                case 'visa':
                    $entityId = config('services.hyperpay.entity_id_visa');
                    break;
                case 'master':
                    $entityId = config('services.hyperpay.entity_id_visa');
                    break;
                case 'applepay':
                    $entityId = config('services.hyperpay.entity_id_applepay');
                    break;
                default:
                    $entityId = config('services.hyperpay.entity_id');
            }
            
            $planId = $request->input('plan_id');
            $userId = $request->input('user_id');
            $cycle = $request->input('cycle');
            
            
            // dd($request->all());
            $url = config('services.hyperpay.base_url')."{$resourcePath}?entityId={$entityId}";
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . config('services.hyperpay.access_token'),])->get($url);
                
            $result = $response->json();
            // $success = !empty($result['result']['code']) && str_starts_with($result['result']['code'], '000.');
            $success = !empty($result['result']['code']) && str_starts_with($result['result']['code'], '000.');
            // $description = $result['result']['description'];
            $errorCode = $result['result']['code'];

            // Get Hyperpay Message
            $description = trans('hyperpay_messages', [], $language)[$errorCode];

            if ($success) {
                $master = User::where('uuid', $userId)->firstOrFail();
                
                $masterDetails = $master->load('masterDetail');
                $activeSubscription = UserSubscription::where('user_id', $master->id)->where('end_date', '>', now())->latest()->first();
                if ($activeSubscription) {
                    $activeSubscription->update([
                        'end_date' => now(),
                        'status'   => 'cancelled',
                    ]);
                }
                $startDate = now();
                $price = '';
                $plan = Plan::where('id', $planId)->first();
                if ($cycle === 'monthly') {
                    $endDate = $startDate->copy()->addDays(30);
                    $price = $plan->monthly_price;
                } elseif ($cycle === 'yearly') {
                    $endDate = $startDate->copy()->addDays(365);
                    $price = $plan->yearly_price;
                } else {
                    $endDate = $startDate; // default (or handle other cases)
                    $price = '';
                }
                $userSubscription = UserSubscription::create([
                    'user_id' => $master->id,
                    'plan_id'  => $plan->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'billing_cycle' => $cycle,
                    'price' => $price,
                    'plan_data' => $plan->toArray(),
                    'user_data' => $masterDetails->toArray(),
                ]);
                $userSubscriptionId = $userSubscription->id;
                Transactions::create([
                    'user_id' => $master->id,
                    'subscription_id' => $userSubscriptionId,
                    'payment_id' => $result['id'],
                    'registration_id' => $result['registrationId'] ?? null,
                    'amount' => $price,
                    'payment_status' => 'paid',
                    'payment_data'   => json_encode($result),
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
                        $cycle,
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
                $successMessage = trans('hyperpay_messages.success', ['plan_name' => $plan->{'name_'.$language}], $language);
                return "<script>window.ReactNativeWebView.postMessage(JSON.stringify({status: true, error_code:'{$errorCode}',  message: '{$successMessage}'}));</script>";
            } else {
                return "<script>window.ReactNativeWebView.postMessage(JSON.stringify({status: false, message: '{$description}'}));</script>";
            }
        } catch (\Throwable $th) {
            $errorMessage = trans('hyperpay_messages.error', [], $language);
            return "<script>window.ReactNativeWebView.postMessage(JSON.stringify({status: false, error_code:500, message: '{$errorMessage}'}));</script>";
        }
    }
}