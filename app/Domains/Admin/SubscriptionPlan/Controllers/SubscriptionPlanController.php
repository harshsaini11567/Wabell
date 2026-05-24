<?php

namespace App\Domains\Admin\SubscriptionPlan\Controllers;

use App\Domains\Admin\SubscriptionPlan\DataTables\SubscriptionPlanDataTable;
use App\Domains\Admin\SubscriptionPlan\DataTables\TransactionDataTable;
use App\Domains\Core\Subscription\Models\Plan;
use App\Domains\Admin\SubscriptionPlan\Requests\SubscriptionPlanUpdateRequest;
use App\Domains\Core\Subscription\Models\Transactions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Domains\Core\Subscription\Models\UserSubscription;
use Illuminate\Support\Facades\Mail;
use App\Domains\Admin\SubscriptionPlan\Mail\PlanDeactivatedMail;
use App\Domains\Admin\SubscriptionPlan\Mail\PlanActivatedMail;
use Illuminate\Support\Facades\Log;
class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SubscriptionPlanDataTable $dataTable)
    {
        abort_if(Gate::denies('plan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            return $dataTable->render('SubscriptionPlan::index');
        } catch (\Exception $e) {
            // dd($e);
            abort(500);
        }
    }

    public function create()
    {
    }

    public function store()
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        abort_if(Gate::denies('plan_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if($request->ajax()) {
            try{
                $subscriptionPlan = Plan::where('id',$id)->first();
                $viewHTML = view('SubscriptionPlan::show', compact('subscriptionPlan'))->render();
                return response()->json(array('success' => true, 'htmlView'=>$viewHTML));
            }
            catch (\Exception $e) {
                // dd($e);
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }

    public function edit($id)
    {
        abort_if(Gate::denies('plan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {

            $subscriptionPlan = Plan::where('id', $id)->first();

            $planImage = null;
            if ($subscriptionPlan && $subscriptionPlan->planImage && $subscriptionPlan->planImage->file_path) {
                $filePath = $subscriptionPlan->planImage->file_path;

                $fileNameArray = explode('/', $filePath);
                $fileName = end($fileNameArray);

                $MediaImage = Storage::disk('public')->exists($filePath)
                    ? asset('storage/' . $filePath)
                    : '';

                $planImage = [
                    'id'           => $subscriptionPlan->planImage->id,
                    'src'          => $MediaImage,
                    'documentType' => $subscriptionPlan->planImage->extension,
                    'fileName'     => $fileName,
                ];
            }

            $viewHTML = view('SubscriptionPlan::edit', compact('subscriptionPlan'))->render();
            return response()->json([
                'success' => true, 
                'htmlView' => $viewHTML,
                'planImage' => $planImage,
            ]);
        } catch (\Exception $e) {
            // dd($e);
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    public function update(SubscriptionPlanUpdateRequest $request, Plan $subscription_plan)
    {
        // dd($subscription_plan);
        abort_if(Gate::denies('plan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        DB::beginTransaction();
        try {
            $input = $request->only('name_en', 'name_ar', 'monthly_price', 'yearly_price', 'features_en', 'features_ar','is_active');
            $subscription_plan->update($input);
             if($subscription_plan){         
                if($request->has('plan_image')){
                    $uploadId = null;
                    $actionType = 'save';
                    if($planImageRecord = $subscription_plan->planImage){
                        $uploadId = $planImageRecord->id;
                        $actionType = 'update';
                    }
                    uploadImage($subscription_plan, $request->plan_image, 'plan/plan-images',"plan_image", 'original', $actionType, $uploadId);
                }
                if($request->has('verified_icon')){
                    $uploadId = null;
                    $actionType = 'save';
                    if($verifiedIconRecord = $subscription_plan->verifiedIcon){
                        $uploadId = $verifiedIconRecord->id;
                        $actionType = 'update';
                    }
                    uploadImage($subscription_plan, $request->verified_icon, 'plan/plan-images',"verified_icon", 'original', $actionType, $uploadId);
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => trans('messages.crud.update_record'),
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
    }

    public function listTransaction(TransactionDataTable $dataTable){
        abort_if(Gate::denies('transaction_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        try {
            return $dataTable->render('SubscriptionPlan::Transaction.index');
        } catch (\Exception $e) {
            // dd($e);
            abort(500);
        }
    }

    public function showTransaction(Request $request, $id){
        abort_if(Gate::denies('transaction_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if($request->ajax()) {
            try{
                $transaction = Transactions::with(['user', 'subscription.plan'])->where('id',$id)->first();
                $viewHTML = view('SubscriptionPlan::Transaction.show', compact('transaction'))->render();
                return response()->json(array('success' => true, 'htmlView'=>$viewHTML));
            }
            catch (\Exception $e) {
                // dd($e);
                return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
            }
        }
        return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
    }

    public function changeStatus(Request $request){
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'id'     => [
                    'required',
                    'exists:plans,id',
                ],
            ]);
            if (!$validator->passes()) {
                return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()->toArray(),'message'=>'Error Occured!'],400);
            }else{
                DB::beginTransaction();
                try{
                    $plan = Plan::where('id', $request->id)->first();
                    if($plan->is_active == '0'){
                        $status = '1';
                    } else {
                        $status = '0';
                    }
                    $plan->update(['is_active' => $status]);
                    if ($status == '0') {
                        $activeSubscriptions = UserSubscription::with('user')
                            ->where('plan_id', $request->id)
                            ->where('status','active')
                            ->where('end_date', '>', now()) // still active
                            ->get();

                        foreach ($activeSubscriptions as $subscription) {
                            $subscription->update(['auto_renew' => 0]);

                            // send email to the user
                            try {
                                Mail::to($subscription->user->email)->queue(new PlanDeactivatedMail($plan, $subscription->user));
                            } catch (\Exception $e) {
                                Log::error("Mail sending failed for user ID {$subscription->user_id}: " . $e->getMessage());
                            }
                        }    
                    }else {
                        $subscribedUsers = UserSubscription::with('user')
                            ->where('plan_id', $request->id)
                           ->whereIn('id', function($query) use ($request) {
                                $query->selectRaw('MAX(id)')
                                    ->from('user_subscriptions')
                                    ->where('plan_id', $request->id)
                                    ->groupBy('user_id');
                            })
                            ->get();

                        foreach ($subscribedUsers as $subscription) {
                            try {
                                Mail::to($subscription->user->email)->queue(new PlanActivatedMail($plan, $subscription->user));
                            } catch (\Exception $e) {
                                Log::error("Activation mail failed for user ID {$subscription->user_id}: " . $e->getMessage());
                            }
                        }
                    }
                    DB::commit();
                    $response = [
                        'status'    => 'true',
                        'message'   => trans('cruds.subscription_plan.title_singular').' '.trans('messages.crud.status_update'),
                    ];
                    return response()->json($response);
                } catch (\Exception $e) {
                    DB::rollBack();
                    // dd($e);
                    return response()->json(['success' => false, 'error_type' => 'something_error', 'error' => trans('messages.error_message')], 400 );
                }
            }
        }
    }

}
