<?php

namespace App\Domains\Api\Common\Controllers;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use App\Domains\Core\Specialty\Models\Specialty;
use App\Domains\Core\User\Models\Review;
use App\Domains\Core\Faq\Models\Faq;
use App\Domains\Core\Setting\Models\Setting;
use App\Domains\Core\User\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\App;

class CommonController extends APIController
{
    public function dashboard(){
        try {
            $authUser = JWTAuth::user();
            $authUser->update(['last_access_date_time' => now()]);
            $userRoles = $authUser->roles()->pluck('id')->toArray();
            $locale = app()->getLocale();
            if(in_array(config('constant.roles.master'), $userRoles)){  // master
                $requestsCount = $authUser->customerRequest()->wherePivot('request_type', 'chat')->count();    // Request Count
                $favoriteCount = $authUser->favoritedByCustomers()->count();        // favorite count
                $viewsCount = $authUser->viewsReceived()->count();                  // View Count  
                $cityId = $authUser->city_id;
                $specificationIds = $authUser->specialties()->pluck('id')->toArray(); 

                $masterInfo = DB::table('users')
                            ->join('role_user', 'users.id', '=', 'role_user.user_id')
                            ->join('roles', 'role_user.role_id', '=', 'roles.id')
                            ->join('specialty_user', 'users.id', '=', 'specialty_user.user_id')
                            ->join('specialties', 'specialty_user.specialty_id', '=', 'specialties.id')
                            ->join('specialties as parents', 'specialties.parent_specialty_id', '=', 'parents.id') // join to get parent level
                            ->whereNull('parents.parent_specialty_id') // ensure parent is top-level (so current is 2nd level)
                            ->where('roles.name_en', 'Master')
                            ->where('users.city_id', $cityId)
                            ->where('users.id', '!=', $authUser->id)
                            ->whereIn('specialties.id', $specificationIds) // list of IDs you care about
                            ->select(
                                DB::raw('COUNT(DISTINCT users.id) as master_count'),
                                DB::raw("GROUP_CONCAT(DISTINCT specialties.name_$locale ORDER BY specialties.name_$locale SEPARATOR ', ') as specialty_names")
                            )
                            ->first();
                $masterData =  ['count' => $masterInfo->master_count ?? 0,
                                'specilities'   =>  $masterInfo->specialty_names ?? '' ];      
                $recentRequests = $authUser->customerRequest()                      // Recent request data
                                ->wherePivot('request_type', 'chat')
                                ->latest('pivot_created_at')
                                ->take(5)
                                ->get()
                                ->map(function ($customer) {
                                    $reviews = $customer->receivedReviews; 
                                    return [
                                        'id'            => $customer->id,
                                        'name'          => $customer->name,
                                        'profile_image' => $customer->profile_image_url,
                                        'avg_rating'    => round($reviews->avg('rating'), 1) ?? 0,
                                        'request_type'  => $customer->pivot->request_type,
                                        'review_count'  => $reviews->count(),
                                        'requested_at'  => $customer->pivot->created_at->format('Y-m-d H:i'),
                                    ];
                                });
                $data = [
                    'requests_count' => $requestsCount,
                    'favorite_count' => $favoriteCount,
                    'views_count' => $viewsCount,
                    'master_count_data' => $masterData,
                    'recent_requests' => $recentRequests,
                    'is_available'  => (bool) $authUser->is_available,
                ] + (!$authUser->is_available ? ['till_offline' => $authUser->till_offline] : []);
                // profile_completed key 
                $profileCompletion = [];
                $profileCompleted  = false;
                $profileCompletion = [
                    'date_of_birth' => !empty($authUser->date_of_birth),
                    'certificate' => $authUser->uploads()->where('type', 'certificate_file')->exists(),
                    'specialty'   => $authUser->specialties()->exists(),
                ];

                // Add master profile_completed key
                $profileCompleted = $profileCompletion['date_of_birth'] && $profileCompletion['certificate'] && $profileCompletion['specialty'];

                $data['complete_profile_status'] = $profileCompleted;
                $data['complete_profile_fields_status'] = $profileCompletion;

                // $activeSubscription = UserSubscription::where('user_id', $authUser->id)->whereStatus('active')->where('end_date', '>', now())->latest()->first();
                $activeSubscription = $authUser->activeSubscription;
                $data['is_basic_subscription'] = $activeSubscription ? false : true;
            } else {  //customer
                $favoriteMasterIds = $authUser->favoriteMasters()->pluck('users.id')->toArray();
                $featuredMaster = [];
                $featuredMaster = User::whereHas('roles', fn($q) =>
                                    $q->where('role_type', 'app')
                                    ->where('name_en', 'Master')
                                )
                                ->whereHas('activeSubscription', fn($q) =>
                                    $q->whereHas('plan', fn($q2) =>
                                        $q2->where('plan_slug', config('constant.plan_name.premium'))
                                    )
                                )
                                ->where('is_approved', 1)
                                ->where('user_status', 'active')
                                ->where('is_ban', 0)
                                ->with([
                                    'masterDetail',
                                    'receivedReviews',
                                    'specialties',
                                    'city',
                                    'neighborhood',
                                    'activeSubscription.plan' // include plan details if needed
                                ])
                                ->withCount('viewsReceived')
                               ->take(5)
                                ->get();

                $featuredMaster = $featuredMaster->transform(function ($master) use ($favoriteMasterIds) {
                        $local = app()->getLocale();
                        $reviews = $master->receivedReviews;   //  review 
                        return $master = [
                            'id'               => $master->id,
                            'name'             => $master->name,
                            'profile_image'    => $master->profile_image_url,
                            // 'city_name'        => optional($master->city)?->{'name_' . $local},
                            // 'neighborhood_name'=> optional($master->neighborhood)?->{'name_' . $local},
                            // 'city_name'        => $master->city_id,
                            // 'neighborhood_name'=> $master->neighborhood_id,
                            'city_name' => ($master?->city_id == 0)
                                            ? trans('constant.other', [], $master->language ?? app()->getLocale())
                                            : ($master?->city?->{'name_' . ($local ?? 'en')} ?? ''),
                            'neighborhood_name'  => ($master?->neighborhood_id == 0)
                                                ? trans('constant.other', [], $master->language ?? app()->getLocale())
                                                : ($master?->neighborhood?->{'name_' . ($local ?? 'en')} ?? ''),              
                            'education'        => optional($master->masterDetail)->education ?? [],
                            'experience'       => optional($master->masterDetail)->experience,
                            'avg_rating'       => round($reviews->avg('rating'), 1) ?? 0,
                            'review_count'     => $reviews->count(),
                            'specialty'        => $master->specialties->filter(fn($s) => is_null($s->parent_specialty_id))->map(fn($s) => $s->{'name_' . $local})->values()->toArray(),
                            'subjects'         => $master->specialties->filter(fn($s) => !is_null($s->parent_specialty_id) && is_null($s->parentSpecialty?->parent_specialty_id))->map(fn($s) => $s->{'name_' . $local})->values()->toArray(),
                            'price_per_hour'   => optional($master->masterDetail)->price_per_hour,
                            'view_count'       => $master->views_received_count ?? 0,
                            'is_favorited'     => in_array($master->id, $favoriteMasterIds),
                            'is_subscribed'    => $master->activeSubscription !== null,
                            'is_available'      => (bool) $master->is_available,
                            'verified_icon'    => optional($master->activeSubscription?->plan)->verified_icon_url ?? "",
                        ] + (!$master->is_available ? ['till_offline' => $master->till_offline] : []);
                    });

                $data = [
                    'feaured_master' => $featuredMaster,
                ];
            }            
            return $this->apiSuccess($data);
        } catch (\Throwable $th) {
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function searchRequestedUser(Request $request)
    {
         try{
            $keyword = $request->query('search');
            if (empty($keyword)) {
                return $this->apiSuccess(['requested_user' => []]);
            }
            $master = $request->user();
            $customers = $master->customerRequest()
                        ->wherePivot('request_type', 'chat')
                        ->whereHas('roles', function ($q) {
                            $q->where('role_type', 'app')
                            ->where('name_en', 'Learner');
                        })
                        ->whereHas('specialties', function ($specQ) use ($keyword) {
                            $specQ->where(function ($q) use ($keyword) {
                                $q->where('name_en', 'LIKE', "%$keyword%")
                                ->orWhere('name_ar', 'LIKE', "%$keyword%");
                            });
                        })
                        // ->where('is_approved', 1)
                        ->where('user_status', 'active')
                        ->where('is_ban', 0)
                        ->with(['specialties.parentSpecialty', 'receivedReviews', 
                        'city', 'neighborhood'
                        ])
                        ->get()
                        ->map(function ($customer) {
                            $local = app()->getLocale();
                            $reviews = $customer->receivedReviews;
                            return [
                                'id'                => $customer->id,
                                'name'              => $customer->name,
                                'profile_image'     => $customer->profile_image_url,
                                'avg_rating'        => round($reviews->avg('rating'), 1) ?? 0,
                                'review_count'      => $reviews->count(),
                                'request_type'      => $customer->pivot->request_type,
                                'requested_at'      => $customer->pivot->created_at->format('Y-m-d H:i'),
                            ];
                        });
            return $this->apiSuccess(['customers' => $customers]);
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function updateAvailability(Request $request){
        try{
            $request->validate([
                'is_available' => 'required|boolean',
                // 'till_offline' => 'nullable|date_format:Y-m-d|after_or_equal:today|required_if:is_available,0',
                'till_offline' => 'nullable|date_format:Y-m-d|after_or_equal:today',
            ],[],[
                'is_available' => trans('cruds.api.is_available'),
                'till_offline' => trans('cruds.api.till_offline')
            ]);
            $user = JWTAuth::user();

            $user->is_available = $request->is_available;
            $user->till_offline = $request->is_available ? null : $request->till_offline;
            $user->save();
            $data = [
                'is_available' => (bool) $user->is_available,
                'till_offline' => $user->till_offline,
            ];
            return $this->apiSuccess([$data],trans('messages.user_availability'));
        }
        catch(\Throwable $th) {
           DB::rollBack();
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }
    
    public function changeUserLanguage(Request $request){
        $request->validate([
            'language' => ['required', 'in:ar,en']
        ],[],[
            'language' => trans('cruds.api.language')
        ]);

        try {
            DB::beginTransaction();

            $authUser = JWTAuth::user();

            $authUser->update(['language' => $request->language]);
            App::setLocale($request->language);
            DB::commit();
            return $this->apiSuccess([], trans('messages.language_update'));
        } catch (\Throwable $th) {
            DB::rollBack();
            App::setLocale($request->language ?? 'en');
            //throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function searchSpecialty(Request $request)
    {
        try{
            $query = $request->query('search');
            $locale = App::getLocale();
            $otherLocale = $locale === 'en' ? 'ar' : 'en';
            // $specialties = Specialty::whereNull('parent_specialty_id')->where('name_'.$locale, 'LIKE', "%{$query}%")->with('specialtyIcon')
            // ->get()
            // ->map(function ($specialty) use ($locale) {
            //     return [
            //         'id' => $specialty->id,
            //         'uuid' => $specialty->uuid,
            //         'name' => $specialty->{'name_' . $locale},
            //         'specialty_status' => $specialty->specialty_status,
            //         'parent_specialty_id' => $specialty->parent_specialty_id,
            //         'icon_url' => optional($specialty->specialtyIcon)->file_url,
            //     ];
            // });
            $specialties = Specialty::where(function ($q) use ($query, $locale, $otherLocale) {
                $q->where('name_'.$locale, 'LIKE', "%{$query}%")
                ->orWhere('name_'.$otherLocale, 'LIKE', "%{$query}%");
            })
            ->with('specialtyIcon')
            ->get()
            ->map(function ($specialty) use ($locale) {
                return [
                    'id' => $specialty->id,
                    'uuid' => $specialty->uuid,
                    'name' => $specialty->{'name_' . $locale},  
                    'specialty_status' => $specialty->specialty_status,
                    'parent_specialty_id' => $specialty->parent_specialty_id,
                    'icon_url' => optional($specialty->specialtyIcon)->file_url,
                ];
            });
            return $this->apiSuccess(['specialties' => $specialties]);
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function reviewList()
    {
        try{
            $authUser = JWTAuth::user();
            $data = $authUser->receivedReviews()
                ->latest()
                ->get()
                ->filter(function ($review) {
                    // Only keep if reviewer exists
                    if (in_array($review->reviewer_type, ['customer', 'master'])) {
                        return User::find($review->reviewer_id) !== null;
                    }
                    return false;
                })
                ->map(function ($review) {
                    $reviewer = User::find($review->reviewer_id);

                    $reviewerReviews = $reviewer?->receivedReviews ?? collect();

                    return [
                        'id'         => $review->id,
                        'rating'     => $review->rating,
                        'review'     => $review->review,
                        'created_at' => $review->created_at->toDateTimeString(),
                        'reviewer'   => [
                            'id'            => $reviewer->id,
                            'name'          => $reviewer->name,
                            'profile_image' => $reviewer->profile_image_url ?? null,
                            'avg_rating'    => $reviewerReviews->isNotEmpty() ? round($reviewerReviews->avg('rating'), 1) : 0,
                            'review_count'  => $reviewerReviews->count(),
                        ],
                    ];
                });
            return $this->apiSuccess(['review' => $data]);
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function storeReview(Request $request)
    {
        try{
            $authUser = JWTAuth::user();
            $request->validate([
                'reviewed_id' => 'required|exists:users,id',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:1000',
            ],[],[
                'reviewed_id' => trans('cruds.api.reviewed_id'),
                'rating' => trans('cruds.api.rating'),
                'review' => trans('cruds.api.review'),
            ]);
            $existingReview = Review::where('reviewer_id', $authUser->id)
            ->where('reviewed_id', $request->reviewed_id)
            ->first();
            if ($existingReview) {
                return $this->apiError(trans('messages.review_already_submitted')); 
            }
            $review = Review::create([
                'reviewer_id' => $request->user()->id,
                'reviewer_type' => $request->user()->hasRole('Master') ? 'master' : 'customer',
                'reviewed_id' => $request->reviewed_id,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);
            $receiverUser = $request->user()::find($request->reviewed_id);
            sendUserNotification(
                $receiverUser->id,
                'review_notification_title',
                'review_notification_body',
                'review',
                null,
                false,
                ['sender' => $request->user()->name],
            );

            return $this->apiSuccess(trans('messages.review_submit'));
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function updateReview(Request $request, $id)
    {
        try{
            $authUser = JWTAuth::user();
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:1000',
            ],[],[
                'rating' => trans('cruds.api.rating'),
                'review' => trans('cruds.api.review'),
            ]);

            $review = Review::findOrFail($id);

            if ($review->reviewer_id !== $request->user()->id) {
                return $this->apiError(trans('messages.error_message'));
            }

            $review->update([
                'rating' => $request->rating,
                'review' => $request->review,
                'is_edited' => 1,
            ]);

            return $this->apiSuccess(trans('messages.review_update'));
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function deleteReview(Request $request, $id)
    {
        try{
            $authUser = JWTAuth::user();
            $review = Review::findOrFail($id);

            if ($review->reviewer_id !== $request->user()->id) {
                return $this->apiError(trans('messages.error_message'));
            }
            $review->delete();

            return $this->apiSuccess(trans('messages.review_delete'));
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function faq(){
        // try {
        //     if($authUser = JWTAuth::user()){
        //         $isCustomer = $authUser->roles()->where('name_en', 'Learner')->exists();
        //         $faqType = $isCustomer ? 'customer' : 'master';
        //     } else {
        //         $faqType = 'master';
        //         request()->query('user_type') === 'customer' ? $faqType = 'customer' : null;
        //     };
        //     $locale = App::getLocale();
        //     $data = Faq::select('id', 'question_'.$locale, 'answer_'.$locale)->where('faq_status','active')->where('faq_type', $faqType)->whereNull('deleted_at')->get();
        //     return $this->apiSuccess(['faq' => $data]);
        // } catch (\Throwable $th) {
        //     return $this->apiError(trans('messages.error_message'));
        // }

        try {
            $authUser = null;

            // Optional JWT
            try {
                $authUser = JWTAuth::parseToken()->authenticate();
            } catch (\Exception $e) {
                $authUser = null; // guest
            }

            if ($authUser) {
                // Authenticated → role based
                $isCustomer = $authUser->roles()->where('name_en', 'Learner')->exists();
                $faqType = $isCustomer ? 'customer' : 'master';
            } else {
                // Open → query param based
                $faqType = request()->query('user_type') === 'customer'
                    ? 'customer'
                    : 'master';
            }

            $locale = App::getLocale();

            $data = Faq::select(
                    'id',
                    "question_{$locale}",
                    "answer_{$locale}"
                )
                ->where('faq_status', 'active')
                ->where('faq_type', $faqType)
                ->whereNull('deleted_at')
                ->get();

            return $this->apiSuccess(['faq' => $data]);
        } catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function aboutUs(){
        try {
            $authUser = JWTAuth::user();
            $locale = App::getLocale();
            $aboutUsKey = 'about_us_' . $locale;
            $settings = Setting::whereIn('key', [$aboutUsKey, 'support_email', 'support_contact'])
            ->whereNull('deleted_at')
            ->get()
            ->pluck('value', 'key');            

            $data = [
                'about_us' => $settings[$aboutUsKey] ?? null,
                'support_email' => $settings['support_email'] ?? null,
                'support_contact' => $settings['support_contact'] ?? null,
            ];
            return $this->apiSuccess($data);
        } catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function deleteAccount(){
        try {
            $authUser = JWTAuth::user();

            $authUser->specialties()->detach();
            $authUser->roles()->detach();                   
            $authUser->masterDetail()->delete();                
            $authUser->givenReviews()->delete();                 
            $authUser->notifications()->delete();                
            $authUser->favoriteMasters()->delete();              
            DB::table('customer_requests')->where('customer_id', $authUser->id)->delete();            
            $authUser->conversations()->delete();               
            DB::table('conversation_participants')->where('user_id', $authUser->id)->delete();
            DB::table('master_views')->where('customer_id', $authUser->id)->orWhere('master_id', $authUser->id)->delete();

            $authUser->delete();
            return $this->apiSuccess(trans('messages.account_delete'));
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function getAuthenticatedUser(Request $request){
        try {
            return $this->apiSuccess($request->user());
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function userSpecialty(Request $request){
        try{
            $authUser = JWTAuth::user();
            $email = $authUser->email;
            $language = $authUser->language ?? $request->header('language', 'en');
            // $userSpecialties = DB::table('specialty_requests as sr')
            // ->where('sr.user_info->user_email', $email)
            // ->select(
            //     'sr.id',
            //     'sr.status',
            //     $language === 'ar' ? 'sr.name_ar as specialty_name' : 'sr.name_en as specialty_name'
            // )
            // ->get();
            $userSpecialties = DB::table('specialty_requests as sr')
            ->where('sr.user_info->user_email', $email)
            ->whereBetween('sr.created_at', [
                now()->subDays(30)->startOfDay(),
                now()->endOfDay()
            ])
            ->select(
                'sr.id',
                'sr.status',
                'sr.created_at',
                DB::raw(
                    $language === 'ar'
                        ? "COALESCE(NULLIF(TRIM(sr.name_ar), ''), sr.name_en) as specialty_name"
                        : "COALESCE(NULLIF(TRIM(sr.name_en), ''), sr.name_ar) as specialty_name"
                )
            )
            ->orderByDesc('sr.created_at')
            ->get();
            $userSpecialties->transform(function ($item) use ($language) {
                $item->specialty_status = @trans("constant.customer_request_status.{$item->status}", [], $language);
                return $item;
            });
            return $this->apiSuccess(['requested_specialties'=> $userSpecialties]);
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }
}
