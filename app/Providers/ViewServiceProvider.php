<?php

namespace App\Providers;

use App\Domains\Core\ContentManagement\Models\Page;
use App\Domains\Core\Specialty\Models\SpecialtyRequest;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Domains\Core\User\Models\User;
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Admin layout and partials blade files
        View::addNamespace('Layouts', base_path('app/Domains/Admin/Master/Layouts'));
        View::addNamespace('Auth', base_path('app/Domains/Admin/Auth/Views'));

        View::addNamespace('Dashboard', base_path('app/Domains/Admin/Dashboard/Views'));
        View::addNamespace('Role', base_path('app/Domains/Admin/Role/Views'));
        View::addNamespace('Specialty', base_path('app/Domains/Admin/Specialty/Views'));
        View::addNamespace('City', base_path('app/Domains/Admin/City/Views'));
        View::addNamespace('Setting', base_path('app/Domains/Admin/Setting/Views'));
        View::addNamespace('User', base_path('app/Domains/Admin/User/Views'));
        View::addNamespace('Faq', base_path('app/Domains/Admin/Faq/Views'));
        View::addNamespace('SplashScreen', base_path('app/Domains/Admin/SplashScreen/Views'));
        View::addNamespace('SubscriptionPlan', base_path('app/Domains/Admin/SubscriptionPlan/Views'));
        View::addNamespace('Announcement', base_path('app/Domains/Admin/Announcement/Views'));
        View::addNamespace('ContentManagement', base_path('app/Domains/Admin/ContentManagement/Views'));

        // Share pages with sidebar layout
        View::composer('Layouts::partials.sidebar', function ($view) {
            $pages = Page::where('status', 'active')->orderBy('id')->get();
            $view->with('pages', $pages);
        });

        View::composer('Layouts::partials.sidebar', function ($view) {
            $countData = [];
            // Become Partner Requests Count
            $countData['unverified_count'] = User::whereHas('roles', function($q) {
                                                $q->where('role_type', 'app')
                                                ->where('name_en', 'Master');
                                            })
                                            ->where(function($q) {
                                                $q->whereNull('date_of_birth')
                                                ->orWhereDoesntHave('uploads', function($q2) {
                                                    $q2->where('type', 'certificate_file');
                                                })
                                                ->orWhereDoesntHave('specialties');
                                            })
                                            ->count();

            $countData['pending_specialty_request_count'] = SpecialtyRequest::where('status','pending')->count();
            $countData['other_city_neighborhood_request_user_count'] = User::where(function ($query) {
                                                                        $query->where('city_id', '0')
                                                                            ->orWhere('neighborhood_id', '0');
                                                                    })
                                                                    ->count();
            $view->with('countData', $countData);
        });
    }
}
