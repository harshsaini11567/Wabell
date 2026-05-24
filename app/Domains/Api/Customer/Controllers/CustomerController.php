<?php

namespace App\Domains\Api\Customer\Controllers;
use App\Http\Controllers\APIController;
use App\Domains\Core\User\Models\User;
use App\Domains\Core\User\Models\MasterView;
use App\Domains\Core\Specialty\Models\Specialty;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use App\Domains\Core\Conversation\Models\Conversation;
use App\Domains\Core\Conversation\Models\Message;
use App\Domains\Core\User\Models\MasterDetail;

class CustomerController extends APIController
{
    public function masterList(Request $request){ 
        try{
            $customer = $request->user();
            $favoriteMasterIds = $customer->favoriteMasters()->pluck('users.id')->toArray();
            
            $masters = User::whereHas('roles', fn($q) =>
                $q->where('role_type', 'app')
                ->where('name_en', 'Master')
            )
            ->where('is_approved', 1)
            ->where('user_status', 'active')
            ->where('is_ban', 0)
            ->with('masterDetail','receivedReviews','specialties', 'city', 'neighborhood')
            ->withCount('viewsReceived')
            ->orderByRaw('CASE WHEN city_id = ? THEN 0 ELSE 1 END', [$customer->city_id])
            ->paginate(9);

            // ->map(function ($master) use ($favoriteMasterIds) {
            $masters->setCollection(
            $masters->getCollection()->transform(function ($master) use ($favoriteMasterIds) {
                $local = app()->getLocale();
                $reviews = $master->receivedReviews;   //  review 
                return $master = [
                    'id'               => $master->id,
                    'name'             => $master->name,
                    'profile_image'    => $master->profile_image_url,
                    'city_name' => ($master?->city_id == 0)
                                    ? trans('constant.other', [], $master->language ?? app()->getLocale())
                                    : ($master?->city?->{'name_' . ($local ?? 'en')} ?? ''),
                    'neighborhood_name' => ($master?->neighborhood_id == 0)
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
                    'verified_icon'    => optional($master->activeSubscription?->plan)->verified_icon_url ?? "",
                    'is_available'      => (bool) $master->is_available,
                ] + (!$master->is_available ? ['till_offline' => $master->till_offline] : []);
            })
        );

            return $this->apiSuccess(['masters' => $masters]);
        }
        catch(\throwable $th){
            return $this->apiError(trans('messages.error_message'));
        }
    }

     public function featuredMasterList(Request $request){ 
        try{
            $customer = $request->user();
            $favoriteMasterIds = $customer->favoriteMasters()->pluck('users.id')->toArray();
            $masters = User::whereHas('roles', fn($q) =>
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
                        ->paginate(10);

            // ->map(function ($master) use ($favoriteMasterIds) {
            $masters->setCollection(
            $masters->getCollection()->transform(function ($master) use ($favoriteMasterIds) {
                $local = app()->getLocale();
                $reviews = $master->receivedReviews;   //  review 
                return $master = [
                    'id'               => $master->id,
                    'name'             => $master->name,
                    'profile_image'    => $master->profile_image_url,
                    'city_name' => ($master?->city_id == 0)
                                    ? trans('constant.other', [], $master->language ?? app()->getLocale())
                                    : ($master?->city?->{'name_' . ($local ?? 'en')} ?? ''),
                    'neighborhood_name' => ($master?->neighborhood_id == 0)
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
                    'verified_icon'    => optional($master->activeSubscription?->plan)->verified_icon_url,
                    'is_available'      => (bool) $master->is_available,
                ] + (!$master->is_available ? ['till_offline' => $master->till_offline] : []);
            })
        );

            return $this->apiSuccess(['featured_masters' => $masters]);
        }
        catch(\throwable $th){
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function searchMasters(Request $request){   // search masters by specialty
        try{
            $keyword = $request->query('search');
            if (empty($keyword)) {
                return $this->apiSuccess(['masters' => []]);
            }
            $customer = $request->user();
            $favoriteMasterIds = $customer->favoriteMasters()->pluck('users.id')->toArray();
         
            $masters = User::whereHas('roles', function ($q) {
                    $q->where('role_type', 'app')
                    ->where('name_en', 'Master');
                })
                ->whereHas('specialties', function ($specQ) use ($keyword) {
                    $specQ->where('name_en', 'LIKE', "%$keyword%")
                        ->orWhere('name_ar', 'LIKE', "%$keyword%");
                })
                ->where('is_approved', 1)
                ->where('user_status', 'active')
                ->where('is_ban', 0)
                ->with(['masterDetail', 'specialties.parentSpecialty' ,'receivedReviews'])
                ->withCount('viewsReceived')
                ->get()
                ->map(function ($master) use ($favoriteMasterIds) {
                    $local = app()->getLocale();

                    $reviews = $master->receivedReviews;

                    return $master = [
                        'id'               => $master->id,
                        'name'             => $master->name,
                        'profile_image'    => $master->profile_image_url,
                        'city_name' => ($master?->city_id == 0) ? trans('constant.other', [], $master->language ?? app()->getLocale()) : ($master?->city?->{'name_' . ($local ?? 'en')} ?? ''),
                        'neighborhood_name' => ($master?->neighborhood_id == 0) ? trans('constant.other', [], $master->language ?? app()->getLocale()) : ($master?->neighborhood?->{'name_' . ($local ?? 'en')} ?? ''),
                        'education'        => optional($master->masterDetail)->education ?? [],
                        'experience'       => optional($master->masterDetail)->experience,
                        'avg_rating'       => round($reviews->avg('rating'), 1) ?? 0,
                        'review_count'     => $reviews->count(),
                        'specialty'        => $master->specialties->filter(fn($s) => is_null($s->parent_specialty_id))->map(fn($s) => $s->{'name_' . $local})->values()->toArray(),
                        'subjects'         => $master->specialties->filter(fn($s) => !is_null($s->parent_specialty_id) && is_null($s->parentSpecialty?->parent_specialty_id))->map(fn($s) => $s->{'name_' . $local})->values()->toArray(),
                        'price_per_hour'   => optional($master->masterDetail)->price_per_hour,
                        'view_count'       => $master->views_received_count ?? 0,
                        'verified_icon'    => optional($master->activeSubscription?->plan)->verified_icon_url ?? "",
                        'is_favorited'     => in_array($master->id, $favoriteMasterIds),
                        'is_available'      => (bool) $master->is_available,
                    ] + (!$master->is_available ? ['till_offline' => $master->till_offline] : []);
                    return $master;
                });
            return $this->apiSuccess(['masters' => $masters]);
        }
        catch(\throwable $th){
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function favoriteMaster($masterId, Request $request)  // toggle for master fav/Unfav
    {
        try{
            $customer = $request->user();
            $isCustomer = $customer->roles()->where('name_en', 'Learner')->exists();
            if (!$isCustomer) {
                return $this->apiError(trans('messages.only_customer_can_add_favorite'));
            }

            // Check if Master exists and has role "Master"
            $master = User::where('id', $masterId)
                ->whereHas('roles', function ($q) {
                    $q->where('name_en', 'Master');
                })->first();

            if (!$master) {
                return $this->apiError(trans('messages.master_not_found'));
            }

            $isFavorited = DB::table('master_favorites')
                ->where('customer_id', $customer->id)
                ->where('master_id', $masterId)
                ->exists();

            if ($isFavorited) {
                // Unfavorite
                DB::table('master_favorites')
                    ->where('customer_id', $customer->id)
                    ->where('master_id', $masterId)
                    ->delete();

                return $this->apiSuccess(trans('messages.unfav_master'));
            } else {
                // Favorite
                DB::table('master_favorites')->insert([
                    'uuid'        => Str::uuid(),
                    'customer_id' => $customer->id,
                    'master_id'   => $masterId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                sendUserNotification(
                    $masterId,
                    'favorite_notification_title',
                    'favorite_notification_body',
                    'favorite',
                    null,
                    false,
                    ['customer' => $customer->name],
                );
                return $this->apiSuccess(trans('messages.fav_master'));
            }
        }
        catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function listFavoritesMaster(Request $request)
    {
        try{
            $customer = $request->user();
            $favorites = $customer->favoriteMasters()
                ->where('is_approved', 1)
                ->where('user_status', 'active')
                ->where('is_ban', 0)
                ->with('masterDetail','receivedReviews','specialties.parentSpecialty') // optional: if you have master profile details
                ->withCount('viewsReceived')
                ->get()
                ->map(function ($master) {
                    $local = app()->getLocale();

                    $reviews = $master->receivedReviews;                
                    return $master = [
                        'id'               => $master->id,
                        'name'             => $master->name,
                        'profile_image'    => $master->profile_image_url,
                        'city_name' => ($master?->city_id == 0)
                                        ? trans('constant.other', [], $master->language ?? app()->getLocale())
                                        : ($master?->city?->{'name_' . ($local ?? 'en')} ?? ''),
                        'neighborhood_name' => ($master?->neighborhood_id == 0)
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
                        'is_favorited'     => true,                 // All are favorited, since fetched from favorites
                        'verified_icon'    => optional($master->activeSubscription?->plan)->verified_icon_url ?? "",
                        'is_available'      => (bool) $master->is_available,
                    ] + (!$master->is_available ? ['till_offline' => $master->till_offline] : []);
                    
                    return $master;
                });

            return $this->apiSuccess(['favorites' => $favorites]);
        }
        catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function masterProfile(Request $request, $masterId){
        try {
            $customer = $request->user();

            // Prevent self-view
            if ($customer->id == $masterId) {
                return $this->apiError(trans('messages.cannot_add_own_profile'));
            }

            // Record the view
            $view = MasterView::updateOrCreate(
                ['customer_id' => $customer->id, 'master_id' => $masterId],
                ['viewed_at' => Carbon::now()]
            );
            if ($view->wasRecentlyCreated) {
                sendUserNotification(
                    $masterId,
                    'profile_view_notification_title',
                    'profile_view_notification_body',
                    'profile_view'
                );
            }
            // Load Master with filters
            $user = User::with(['specialties', 'favoritedByCustomers', 'masterDetail','receivedReviews.reviewer'])
            ->withCount(['favoritedByCustomers', 'receivedReviews', 'viewsReceived'])
            ->where('id', $masterId)
            ->where('is_approved', 1)
            ->where('user_status', 'active')
            ->where('is_ban', 0)
            // ->where('is_available', 1)
            ->first();
            if (!$user) {
                return $this->apiError(trans('messages.master_not_found'));
            }
            $isFavorited = $user->favoritedByCustomers->contains('id', $customer->id);
            // personal info
            $personalInfo = [
                'name'          => $user?->name,
                'email'         => $user?->email,
                'country_code'  => $user?->country_code,
                'phone'         => $user?->phone,
                'date_of_birth' => $user?->date_of_birth,
                'gender'        => $user?->gender,
                'profile_image' => $user?->profile_image_url,
                'city'          => ($user?->city_id == 0) ? trans('constant.other', [], $user->language ?? app()->getLocale()) : ($user?->city?->{'name_' . ($user->language ?? 'en')} ?? ''),
                'neighborhood'  => ($user?->neighborhood_id == 0) ? trans('constant.other', [], $user->language ?? app()->getLocale()) : ($user?->neighborhood?->{'name_' . ($user->language ?? 'en')} ?? ''),
                'is_favorited'  => $isFavorited,
                'verified_icon'    => optional($user->activeSubscription?->plan)->verified_icon_url ?? "",
                'is_available'  => (bool) $user->is_available,
            ] + (!$user->is_available ? ['till_offline' => $user->till_offline] : []);
              
            // Reviews 
            $mappedReviews = $user->receivedReviews->map(function ($review) {
                return [
                    'name'          => $review->reviewer?->name,
                    'image'         => $review->reviewer?->profile_image_url,
                    'review_count'  => $review->reviewer?->receivedReviews()->count() ?? 0,
                    'avg_rating'    => $review->reviewer?->receivedReviews()->avg('rating') ?? 0,
                    'rating'        => $review->rating,
                    'review'        => $review->review,
                    'created_at'    => $review->created_at->toDateTimeString(),
                ];
            });
            // $avgRating = round($user?->receivedReviews()->avg('rating'), 1) ?? 0;
            $avgRating = number_format((float) $user?->receivedReviews()->avg('rating') ?? 0, 1, '.', '');
            $callCount = 0;
            $biography = $user?->masterDetail?->biography;
            
        
            // work prefernces
            $availableDays = [];
            if (is_array($user?->masterDetail?->available_day)) {
                $availableDays = array_map(function($day) use ($customer){
                    return trans('constant.available_day', [], $customer['language'])[$day];
                }, $user?->masterDetail?->available_day);
            }
            
            $availableTimes = [];
            if (is_array($user?->masterDetail?->available_time)) {
                $availableTimes = array_map(function($time) use ($customer){
                    return trans('constant.available_time', [], $customer?->language)[$time];
                }, $user?->masterDetail?->available_time);
            }
            $workPrefernces = [
                'price_per_hour' => $user?->masterDetail?->price_per_hour,
                'available_time' => $availableTimes,
                'available_day' => $availableDays
            ];
            
            // education
            $educations = [];
            if (is_array($user?->masterDetail?->education)) {
                $educations = array_map(function($education) use ($customer){
                    return trans('constant.education',[], $customer?->language)[$education];
                }, $user?->masterDetail?->education);
            }
            /* $activeSubscription = UserSubscription::where('user_id', $user->id)->where('end_date', '>', now())->latest()->first(); */
            $activeSubscription = $user->activeSubscription;

            $isPlanActive = false;
            if ($activeSubscription) {
                $isPlanActive = true;
            }

            $specialties = $user->specialties->map(function ($specialty) use ($user) {
                $locale = $customer->language ?? app()->getLocale();
                // Translate level
                $translatedLevel = trans('constant.specialty_level.' . $specialty->pivot->level, [], $locale);

                // Replace pivot level with translated one
                $specialty->pivot->level = $translatedLevel;

                return $specialty;
            });

            // Response
            $data = [
                // "profile_data" => $user,
                'personal_inforamation' => $personalInfo,
                'biography' => $biography,
                'specialties' => $specialties,
                'certificates' => $user?->certificate_files_urls,
                'work_prefernces' => $workPrefernces,
                'experience' => trans('constant.experience.' . ($user?->masterDetail?->experience ?? ''), [], $customer->language),
                'education' => $educations,
                'review'    => $mappedReviews,
                'reviewCount'  => $user?->received_reviews_count,
                'favoriteCount' => $user?->favorited_by_customers_count,
                'viewCount'     => $user?->views_received_count,
                'avgRating'     => $avgRating,
                'callCount'     => $callCount,
                'is_plan_active' => $isPlanActive
            ];
            return $this->apiSuccess($data);
        } catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function getFilterData()
    {
        try{
            $locale = app()->getLocale();
            $specialities = Specialty::with('childrenRecursive')->with('specialtyIcon')->where('specialty_status', 'active')->whereNull('parent_specialty_id')->get();
            $mapped = $specialities->map(fn($s) => $this->mapSpecialtyRecursive($s, $locale));
            $max_price = MasterDetail::max('price_per_hour');
            
            $data = [
                'specialities' => $mapped,
                'max_price' => $max_price
            ];
            // Return JSON response
            return $this->apiSuccess($data);
        }
        catch (\Throwable $th) {
                // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }
    private function mapSpecialtyRecursive($specialty, $locale, $visited = [])
    {
        // Prevent circular recursion
        if (in_array($specialty->id, $visited)) {
            return null;
        }

        $visited[] = $specialty->id;

        return [
            'id' => $specialty->id,
            'uuid' => $specialty->uuid,
            'name' => $specialty->{'name_' . $locale} ?? $specialty->name_en,
            'specialty_icon_url' => $specialty->specialty_icon_url,
            'parent_specialty_id' => $specialty->parent_specialty_id,
            'children_recursive' => collect($specialty->childrenRecursive)
                            ->map(fn($child) => $this->mapSpecialtyRecursive($child, $locale, $visited))
                            ->filter() // remove null (circular)
                            ->values(),
        ];
    }
    public function filterMaster(Request $request){
        try{
            $customer = $request->user();
            $favoriteMasterIds = $customer->favoriteMasters()->pluck('users.id')->toArray();
            $query = User::whereHas('roles', function ($q) {
                $q->where('name_en', 'Master');
            })
            ->where('is_approved', 1)
            ->where('user_status', 'active')
            ->where('is_ban', 0)
            ->with(['masterDetail', 'receivedReviews','specialties'])
            ->withCount('viewsReceived')
            ->withAvg('receivedReviews as average_rating', 'rating');

            // Filter by Category (Specialty)
            if ($request->filled('category_id')) {
                $categoryIds = is_array($request->category_id) ? $request->category_id : [$request->category_id];
                $query->whereHas('specialties', function ($q) use ($categoryIds) {
                    $q->whereNull('parent_specialty_id') // root specialties
                    ->whereIn('id', $categoryIds);
                });
            }

           // Filter by Subject (Child Specialty)
            if ($request->filled('subject_id')) {
                $subjectIds = is_array($request->subject_id) ? $request->subject_id : [$request->subject_id];
                $query->whereHas('specialties', function ($q) use ($subjectIds) {
                    $q->whereNotNull('parent_specialty_id') // child specialties
                    ->whereIn('id', $subjectIds);
                });
            }

            // Filter by Budget (Min and Max)
            if ($request->filled('min_budget') || $request->filled('max_budget')) {
                $query->whereHas('masterDetail', function ($q) use ($request) {
                    if ($request->filled('min_budget')) {
                        $q->where('price_per_hour', '>=', $request->min_budget);
                    }
                    if ($request->filled('max_budget')) {
                        $q->where('price_per_hour', '<=', $request->max_budget);
                    }
                });
            }

            // Filter by Rating (e.g., 3 means 3 and above)
            if ($request->filled('rating')) {
                $rating = (int) $request->rating;

                // Sanity check: ensure rating is between 1 and 5
                if ($rating >= 1 && $rating <= 5) {
                    if ($rating < 5) {
                        $query->having('average_rating', '>=', $rating)->having('average_rating', '<', $rating + 1);
                    } else {
                        $query->havingRaw('ROUND(average_rating, 1) = 5.0');
                    }
                }
            }

            // Filter via distance
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $latitude = $request->latitude;
                $longitude = $request->longitude;

                $haversine = "(6371 * acos(
                                cos(radians(?)) *
                                cos(radians(users.latitude)) *
                                cos(radians(users.longitude) - radians(?)) +
                                sin(radians(?)) *
                                sin(radians(users.latitude))
                            ))";

                $query->selectRaw("users.*, $haversine AS distance",
                        [
                            $latitude,
                            $longitude,
                            $latitude
                        ]);

                if ($request->filled('max_distance')) {
                    $query->havingRaw("distance <= ?", [$request->max_distance]);
                }

                if ($request->filled('min_distance')) {
                    $query->havingRaw("distance >= ?", [$request->min_distance]);
                }
            }

            // Sorting
            if ($request->filled('sort_by') && $request->filled('sort_direction')) {
                $direction = strtolower($request->sort_direction) === 'asc' ? 'asc' : 'desc';
                switch ($request->sort_by) {
                    case 'rating':
                        $query->orderBy('average_rating', $direction);
                        break;

                    case 'budget':
                        $query->join('master_details', 'users.id', '=', 'master_details.user_id')
                            ->orderBy('master_details.price_per_hour', $direction)
                            ->addSelect('users.*');
                        break;

                    case 'distance':
                        if ($request->sort_by === 'distance') {
                            $query->orderBy('distance', $direction);
                        }
                        break;    
                }
            }

            $masters = $query->get()
            ->map(function ($master) use ($favoriteMasterIds) {
                    $local = app()->getLocale();
                    $reviews = $master->receivedReviews;
                    return $master = [
                        'id'               => $master->id,
                        'name'             => $master->name,
                        'profile_image'    => $master->profile_image_url,
                        'city_name'         => ($master?->city_id == 0) ? trans('constant.other', [], $master->language ?? app()->getLocale()) : ($master?->city?->{'name_' . ($local ?? 'en')} ?? ''),
                        'neighborhood_name' => ($master?->neighborhood_id == 0) ? trans('constant.other', [], $master->language ?? app()->getLocale()) : ($master?->neighborhood?->{'name_' . ($local ?? 'en')} ?? ''),                
                        'education'        => optional($master->masterDetail)->education ?? [],
                        'experience'       => optional($master->masterDetail)->experience,
                        'avg_rating'       => round($reviews->avg('rating'), 1) ?? 0,
                        'review_count'     => $reviews->count(),
                        'specialty'        => $master->specialties->filter(fn($s) => is_null($s->parent_specialty_id))->map(fn($s) => $s->{'name_' . $local})->values()->toArray(),
                        'subjects'         => $master->specialties->filter(fn($s) => !is_null($s->parent_specialty_id) && is_null($s->parentSpecialty?->parent_specialty_id))->map(fn($s) => $s->{'name_' . $local})->values()->toArray(),
                        'price_per_hour'   => optional($master->masterDetail)->price_per_hour,
                        'view_count'       => $master->views_received_count ?? 0,
                        'is_favorited'     => in_array($master->id, $favoriteMasterIds),
                        'distance'         => round($master->distance ?? 0, 2) . ' km',
                        'verified_icon'    => optional($master->activeSubscription?->plan)->verified_icon_url ?? "",
                        'is_available'     => (bool) $master->is_available,
                    ] + (!$master->is_available ? ['till_offline' => $master->till_offline] : []);
                    return $master;
                });
                if ($masters->isEmpty()) {
                    return $this->apiSuccess(['masters'=> $masters, 'message'=> trans('messages.master_not_found')]);
                }
            return $this->apiSuccess(['masters' => $masters]);
        }
        catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }

    }

    public function requestToMaster(Request $request, $masterId)
    {
        $request->validate([
            'request_type' => 'required|in:call,chat',
        ],[],[
            'request_type' => trans('cruds.api.request_type'),
        ]);
        try{
            $masterExists =User::where('id', $masterId)->exists();
            if (!$masterExists) {
                return $this->apiError(trans('messages.master_not_found')); // You can localize this
            }
            $customer = $request->user();
            $isCustomer = $customer->roles()->where('name_en', 'Learner')->exists();

            if (!$isCustomer) {
                return $this->apiError(trans('messages.only_customer_can_add_request'));
            }

             $recentRequestExists = DB::table('customer_requests')
                ->where('customer_id', $customer->id)
                ->where('master_id', $masterId)
                ->where('request_type', $request->request_type)
                ->whereIn('request_type', ['call', 'chat'])
                ->exists();

            if ($recentRequestExists) {
                return $this->apiError(trans('messages.request_already_sent_recently'));
            }


            DB::table('customer_requests')->insert([
                'customer_id' => $customer->id,
                'master_id'   => $masterId,
                'request_type' => $request->request_type,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            sendUserNotification(
                $masterId,
                'request_notification_title',
                'request_notification_body',
                'request',
                null,
                false,
                ['sender' => $customer->name],
            );
            return $this->apiSuccess(trans('messages.request_added'));
        }
        catch(\throwable $th){
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function getSpecialities(){
        try{
            $locale = app()->getLocale();
            $column = 'name_'.$locale;
            $specialties = Specialty::select('id', 'uuid', 'parent_specialty_id', $column)->where('specialty_status', 'active')->whereNull('parent_specialty_id')
            ->with(['childrenRecursive' => function ($query) use ($column) {
                $this->applyRecursiveSelect($query, $column);
            }])
            ->with('specialtyIcon')->get();
            $transformed = $this->renameNameKey($specialties->toArray(), $column);
            return $this->apiSuccess(['specialties' => $transformed]);
        }
        catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }

    }

    private function applyRecursiveSelect($query, $column, $level = 0)
    {
        $query->select('id', 'uuid', 'parent_specialty_id', $column)
            ->with(['childrenRecursive' => function ($q) use ($column, $level) {
                if ($level < 10) { // prevent infinite loop
                    $this->applyRecursiveSelect($q, $column, $level + 1);
                }
            }])
            ->with('specialtyIcon');
    }

    private function renameNameKey(array $items, $column)
    {
        return array_map(function ($item) use ($column) {
            $transformed = [
                'id' => $item['id'],
                'uuid'  => $item['uuid'],
                'name' => $item[$column] ?? null,
                'parent_specialty_id' => $item['parent_specialty_id'] ?? null,
                'specialty_icon_url' => $item['specialty_icon_url'] ?? null,
                'children_recursive' => [],
            ];

            if (!empty($item['children_recursive']) && is_array($item['children_recursive'])) {
                $transformed['children_recursive'] = $this->renameNameKey($item['children_recursive'], $column);
            }

            return $transformed;
        }, $items);
    }

    public function getChildSpecialities(Request $request){
        try{
            $id = $request->query('search');
            $locale = app()->getLocale();
            $column = 'name_'.$locale;
            $specialities = Specialty::select('id',$column)->where('parent_specialty_id',$id)->with([
                'childSpecialties' => function ($query) use ($column) {
                    $query->select('id', 'parent_specialty_id', $column);
                },
                'specialtyIcon', // for parent
                'childSpecialties.specialtyIcon' // for child
            ])->get();
            return $this->apiSuccess(['specialities' => $specialities]);
        }
        catch (\Throwable $th) {
            // throw $th;
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function searchMastersBySpecialities(Request $request){
        try {
            $specialtyIds = $request->input('specialty_ids', []);
            if (empty($specialtyIds)) {
                return $this->apiSuccess(['masters' => []]);
            }
            $customer = $request->user();
            $favoriteMasterIds = $customer->favoriteMasters()->pluck('users.id')->toArray();

            $masters = User::whereHas('roles', function ($q) {
                    $q->where('role_type', 'app')
                    ->where('name_en', 'Master');
                })
                ->when(!empty($specialtyIds), function ($query) use ($specialtyIds) {
                    $query->whereHas('specialties', function ($specQ) use ($specialtyIds) {
                        $specQ->whereIn('id', $specialtyIds);
                    });
                })
                ->where('is_approved', 1)
                ->where('user_status', 'active')
                ->where('is_ban', 0)
                ->with(['masterDetail', 'specialties.parentSpecialty', 'receivedReviews'])
                ->withCount('viewsReceived')
                ->get()
                ->map(function ($master) use ($favoriteMasterIds) {
                    $local = app()->getLocale();
                    $reviews = $master->receivedReviews;

                    return [
                        'id'                => $master->id,
                        'name'              => $master->name,
                        'profile_image'     => $master->profile_image_url,
                        'city_name'         => ($master?->city_id == 0) ? trans('constant.other', [], $master->language ?? app()->getLocale()) : ($master?->city?->{'name_' . ($local ?? 'en')} ?? ''),
                        'neighborhood_name' => ($master?->neighborhood_id == 0)  ? trans('constant.other', [], $master->language ?? app()->getLocale()) : ($master?->neighborhood?->{'name_' . ($local ?? 'en')} ?? ''),
                        'education'         => optional($master->masterDetail)->education ?? [],
                        'experience'        => optional($master->masterDetail)->experience,
                        'avg_rating'        => round($reviews->avg('rating'), 1) ?? 0,
                        'review_count'      => $reviews->count(),
                        'specialty'         => $master->specialties->filter(fn($s) => is_null($s->parent_specialty_id))->map(fn($s) => $s->{'name_' . $local})->values()->toArray(),
                        'subjects'         => $master->specialties->filter(fn($s) => !is_null($s->parent_specialty_id) && is_null($s->parentSpecialty?->parent_specialty_id))->map(fn($s) => $s->{'name_' . $local})->values()->toArray(),
                        'price_per_hour'    => optional($master->masterDetail)->price_per_hour,
                        'view_count'        => $master->views_received_count ?? 0,
                        'is_favorited'      => in_array($master->id, $favoriteMasterIds),
                        'verified_icon'    => optional($master->activeSubscription?->plan)->verified_icon_url ?? "",
                        'is_available'      => (bool) $master->is_available,
                    ] + (!$master->is_available ? ['till_offline' => $master->till_offline] : []);
                });

            return $this->apiSuccess(['masters' => $masters]);

        } catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    // for customer chat & call list
    public function listChatRequestedMaster(Request $request){
        try{
            $customerId = $request->user()->id;
            $masters = DB::table('customer_requests')
                ->where('customer_requests.customer_id', $customerId)
                ->where('customer_requests.request_type', 'chat')
                ->join('users', 'users.id', '=', 'customer_requests.master_id')
                ->leftJoin('reviews', 'reviews.reviewed_id', '=', 'users.id')
                ->leftJoin('uploads', function ($q) {
                    $q->on('uploads.uploadsable_id', '=', 'users.id')
                    ->where('uploads.type', '=', 'user_profile');
                })
                ->select(
                    'users.id',
                    'users.name',
                    DB::raw("CONCAT('" . asset('storage') . "/', uploads.file_path) as user_profile"),
                    DB::raw("ROUND(AVG(reviews.rating), 1) as avg_rating"),
                    DB::raw("COUNT(reviews.id) as review_count"),
                    'customer_requests.request_type',
                    DB::raw("DATE_FORMAT(customer_requests.created_at, '%Y-%m-%d %H:%i') as requested_at")
                )
                ->where('users.user_status', 'active')
                ->where('users.is_ban', 0)
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('role_user')
                        ->join('roles', 'roles.id', '=', 'role_user.role_id')
                        ->where('roles.role_type', 'app')
                        ->where('roles.name_en', 'Master')
                        ->whereRaw('role_user.user_id = users.id');
                })
                ->groupBy(
                    'users.id',
                    'users.name',
                    'uploads.file_path',
                    'customer_requests.request_type',
                    'customer_requests.created_at'
                )
                ->get();

            return $this->apiSuccess(['masters' => $masters]);
        }
        catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function listCallRequestedMaster(Request $request){
        try{
            $customerId = $request->user()->id;
            $masters = DB::table('customer_requests')
                ->where('customer_requests.customer_id', $customerId)
                ->where('customer_requests.request_type', 'call')
                ->join('users', 'users.id', '=', 'customer_requests.master_id')
                ->leftJoin('reviews', 'reviews.reviewed_id', '=', 'users.id')
                ->leftJoin('uploads', function ($q) {
                    $q->on('uploads.uploadsable_id', '=', 'users.id')
                    ->where('uploads.type', '=', 'user_profile');
                })
                ->select(
                    'users.id',
                    'users.name',
                    DB::raw("CONCAT('" . asset('storage') . "/', uploads.file_path) as user_profile"),
                    DB::raw("ROUND(AVG(reviews.rating), 1) as avg_rating"),
                    DB::raw("COUNT(reviews.id) as review_count"),
                    'customer_requests.request_type',
                    DB::raw("DATE_FORMAT(customer_requests.created_at, '%Y-%m-%d %H:%i') as requested_at")
                )
                ->where('users.user_status', 'active')
                ->where('users.is_ban', 0)
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('role_user')
                        ->join('roles', 'roles.id', '=', 'role_user.role_id')
                        ->where('roles.role_type', 'app')
                        ->where('roles.name_en', 'Master')
                        ->whereRaw('role_user.user_id = users.id');
                })
                ->groupBy(
                    'users.id',
                    'users.name',
                    'uploads.file_path',
                    'customer_requests.request_type',
                    'customer_requests.created_at'
                )
                ->get();
            return $this->apiSuccess(['masters' => $masters]);
        }
        catch (\Throwable $th) {
            // dd($th);
            return $this->apiError(trans('messages.error_message'));
        }
    }

    // For master start listing
    public function listFavCustomer(Request $request){
        try{
                $master = $request->user();
                $customers = $master->favoritedByCustomers()
                ->whereHas('roles', function ($q) {
                    $q->where('role_type', 'app')->where('name_en', 'Learner');
                })
                ->where('user_status', 'active')
                ->where('is_ban', 0)
                ->with(['receivedReviews', 'uploads'])
                ->get()
                ->map(function ($customer) {
                    $reviews = $customer->receivedReviews;
                    return [
                        'id'             => $customer->id,
                        'name'           => $customer->name,
                        'profile_image'  => $customer->profile_image_url, // assuming this accessor exists
                        'avg_rating'     => round($reviews->avg('rating'), 1) ?? 0,
                        'review_count'   => $reviews->count(),
                        'favorited_at'   => optional($customer->pivot->created_at)->format('Y-m-d H:i'),
                    ];
                });

            return $this->apiSuccess(['customers' => $customers]);
        }
        catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }

    }

    public function listViewCustomer(Request $request){
        try{
                $master = $request->user();
                $views = $master->viewsReceived()
                    ->with('customer.receivedReviews', 'customer.uploads')
                    ->latest()
                    ->get()
                    ->groupBy('customer_id')
                    ->map(function ($views) {
                        $view = $views->first();
                        $customer = $view->customer;

                        if (!$customer || $customer->user_status !== 'active' || $customer->is_ban) {
                            return null;
                        }

                        // Make sure the user has the role 'Learner'
                        if (!$customer->roles->contains(function ($role) {
                            return $role->role_type === 'app' && $role->name_en === 'Learner';
                        })) {
                            return null;
                        }

                        $reviews = $customer->receivedReviews;

                        return [
                            'id'            => $customer->id,
                            'name'          => $customer->name,
                            'profile_image' => $customer->profile_image_url,
                            'avg_rating'    => round($reviews->avg('rating'), 1) ?? 0,
                            'review_count'  => $reviews->count(),
                            'viewed_at'     => $view->created_at->format('Y-m-d H:i'),
                        ];
                    })
                    ->filter()
                    ->values();
            return $this->apiSuccess(['viewers' => $views]);
        }
        catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }

    }

    public function listRequestedCustomer(Request $request){
        try{
            $master = $request->user();
            $customers = $master->customerRequest()
                        ->wherePivot('request_type', 'chat')
                        ->whereHas('roles', function ($q) {
                            $q->where('role_type', 'app')
                            ->where('name_en', 'Learner');
                        })
                        ->where('user_status', 'active')
                        ->where('is_ban', 0)
                        ->with(['receivedReviews'])
                        ->get()
                        ->map(function ($customer) use ($master) {
                            $local = app()->getLocale();
                            $reviews = $customer->receivedReviews;

                            $conversation = Conversation::where('created_by', $customer->id)
                                        ->whereHas('participants', function ($q) use ($master) {
                                            $q->where('user_id', $master->id);
                                        })->first();
                            
                            $conversationId = optional($conversation)->id;

                            $lastReadAt = null;
                            $unreadCount = 0;

                            if ($conversation) {
                                $participant = $conversation->participants()
                                    ->where('user_id', $master->id)
                                    ->first();

                                $lastReadAt = optional($participant)->last_read_at;

                                // 🧠 Step 3: Count unread messages (from customer to master)
                                $unreadCount = Message::where('conversation_id', $conversation->id)
                                    ->where('sender_id', $customer->id)
                                    ->when($lastReadAt, function ($q) use ($lastReadAt) {
                                        $q->where('created_at', '>', $lastReadAt);
                                    })
                                    ->count();
                            }

                            return [
                                'id'                => $customer->id,
                                'name'              => $customer->name,
                                'profile_image'     => $customer->profile_image_url,
                                'avg_rating'        => round($reviews->avg('rating'), 1) ?? 0,
                                'review_count'      => $reviews->count(),
                                'request_type'      => $customer->pivot->request_type,
                                'requested_at'      => $customer->pivot->created_at->format('Y-m-d H:i'),
                                'unread_message_count'  => $unreadCount,
                                'is_read'               => $unreadCount == 0,
                            ];
                        });
            return $this->apiSuccess(['requested_customers'=>$customers]);
        }
        catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }
    // For master end listing

    public function listChatRequestedCustomer(Request $request){
        try{
            $master = $request->user();
            $customers = $master->customerRequest()
                        ->wherePivot('request_type', 'chat')
                        ->whereHas('roles', function ($q) {
                            $q->where('role_type', 'app')
                            ->where('name_en', 'Learner');
                        })
                        ->where('user_status', 'active')
                        ->where('is_ban', 0)
                        ->with(['receivedReviews'])
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
        catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }

    public function listCallRequestedCustomer(Request $request){
        try{
            $master = $request->user();
            $customers = $master->customerRequest()
                        ->wherePivot('request_type', 'call')
                        ->whereHas('roles', function ($q) {
                            $q->where('role_type', 'app')
                            ->where('name_en', 'Learner');
                        })
                        ->where('user_status', 'active')
                        ->where('is_ban', 0)
                        ->with(['receivedReviews'])
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
        catch (\Throwable $th) {
            return $this->apiError(trans('messages.error_message'));
        }
    }
}
