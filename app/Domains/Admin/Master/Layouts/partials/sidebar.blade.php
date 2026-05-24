<div class="leftside-menu">

    <!-- Brand Logo Light -->
    <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
        <span class="logo-lg">
            <img src="{{ getSetting('site_logo') ? getSetting('site_logo') : asset(config('constant.default.logo')) }}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ getSetting('site_logo') ? getSetting('site_logo') : asset(config('constant.default.logo')) }}" alt="small logo">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="javascript::void(0);" class="logo logo-dark">
        <span class="logo-lg">
            <img src="{{ getSetting('site_logo') ? getSetting('site_logo') : asset(config('constant.default.logo')) }}" alt="dark logo">
        </span>
        <span class="logo-sm">
            <img src="{{ getSetting('site_logo') ? getSetting('site_logo') : asset(config('constant.default.logo')) }}" alt="small logo">
        </span>
    </a>

    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <!--- Sidemenu -->
        <ul class="side-nav">

            {{-- Dashboard Menu --}}
            <li class="side-nav-item {{ request()->is('dashboard') ? 'menuitem-active' : ''}}">
                <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ request()->is('dashboard') ? 'active' : ''}}">
                    <i class="ri-dashboard-3-line"></i>
                    <span> @lang('cruds.menus.dashboard') </span>
                </a>
            </li>

            @can('content_management_access')
                <li class="side-nav-item has-child-menu {{ request()->is('pages*') ? 'menuitem-active' : ''}}">
                    <a data-bs-toggle="collapse" href="#companyLayouts" aria-expanded="false" aria-controls="companyLayouts" class="side-nav-link collapsed">
                        <i class="ri-folder-3-line"></i>
                        <span class="has_tooltip">@lang('cruds.menus.content_management')</span>
                    </a>
                    <div class="collapse {{ request()->is('pages*') ? 'show' : ''}}" id="companyLayouts">
                        <ul class="side-nav-second-level">
                            @php
                                $name = "name_".app()->getLocale();
                            @endphp
                            @foreach ($pages as $page)
                                <li class="{{ request()->is('pages*') && (request()->is('pages/'.$page->slug)) ? 'menuitem-active' : ''}}">
                                    <a href="{{route('pages.index', $page->slug)}}" class="{{ request()->is('pages*') && (request()->is('pages/'.$page->slug)) ? 'active' : ''}}">{{ $page->$name }}</a>
                                </li>                            
                            @endforeach
                            <li class="{{ request()->routeIs('web-faqs.*') ? 'menuitem-active' : '' }}">
                                <a href="{{route('web-faqs.index')}}" class="{{ request()->routeIs('web-faqs.*') ? 'active' : '' }}">@lang('cruds.menus.web_faq')</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcan

           
            {{-- Specilities Menu --}}
            @can('specialties_access')
            <li class="side-nav-item {{ request()->is('specialties') ? 'menuitem-active' : ''}}">
                <a href="{{ route('specialties.index') }}" class="side-nav-link {{ request()->is('specialties') ? 'active' : ''}}">
                    <i class=" ri-star-line"></i>
                    <span> @lang('cruds.menus.specialties') </span>
                    </a>
            </li>
            @endcan

            {{-- Specility Request Menu --}}
            @can('specialties_request_access')
            <li class="side-nav-item {{ request()->is('specialty-requests*') ? 'menuitem-active' : ''}} {{ $countData['pending_specialty_request_count'] > 0 ? 'has-count' : '' }}">
                <a href="{{ route('specialty-requests.index') }}" class="side-nav-link {{ request()->is('specialty-requests*') ? 'active' : ''}}">
                    <i class=" ri-edit-line"></i>
                    <span> @lang('cruds.menus.specialties_request') </span>
                    @if($countData['pending_specialty_request_count'] > 0)
                        <span class="pending_circle sidebar_pending_specialty_request_count">{{ $countData['pending_specialty_request_count'] }}</span>
                    @endif
                </a>
            </li>
            @endcan

            {{-- Master Menu --}}
            @can('master_access')
            <li class="side-nav-item has-inner-count {{ request()->is('masters*') ? 'menuitem-active' : ''}} {{ $countData['unverified_count'] > 0 ? 'has-count' : '' }}">
                <a href="{{ route('masters.index') }}" class="side-nav-link {{ request()->is('masters*') ? 'active' : ''}}">
                    <i class=" ri-shield-user-line"></i>
                    <span> @lang('cruds.menus.all_master') </span>
                    @if($countData['unverified_count'] > 0)
                        <span class="pending_circle sidebar_user_unverified_count">{{ $countData['unverified_count'] }}</span>
                    @endif
                </a>
            </li>
            @endcan

            {{-- Verified Users Menu --}}
            @can('verified_master_access')
            <li class="side-nav-item has-inner-count {{ request()->is('verified_masters*') ? 'menuitem-active' : ''}} ">
                <a href="{{ route('verified-masters.index') }}" class="side-nav-link {{ request()->is('verified_masters*') ? 'active' : ''}}">
                    <i class="bi-shield-check"></i>
                    <span> @lang('cruds.menus.verified-master') </span>
                    {{-- @if($countData['unverified_count'] > 0)
                        <span class="pending_circle sidebar_user_unverified_count">{{ $countData['unverified_count'] }}</span>
                    @endif --}}
                </a>
            </li>
            @endcan

            {{-- Customer Menu --}}
            @can('customer_access')
            <li class="side-nav-item {{ request()->is('customers*') ? 'menuitem-active' : ''}}">
                <a href="{{ route('customers.index') }}" class="side-nav-link {{ request()->is('customers*') ? 'active' : ''}}">
                    <i class=" ri-team-line"></i>
                    <span> @lang('cruds.menus.customer') </span>
                    </a>
            </li>
            @endcan
            
            {{-- City Menu --}}
            @can('city_access')
            @php
                $isCityActive = request()->is('cities/*')
                    || request()->is('users-without-location')
                    || request()->is('cities/user/*');
            @endphp

            <li class="side-nav-item {{ $isCityActive ? 'menuitem-active' : '' }} {{ $countData['other_city_neighborhood_request_user_count'] > 0 ? 'has-count' : '' }}">
                <a href="{{ route('cities.index') }}" class="side-nav-link {{ $isCityActive ? 'active' : '' }}">
                    <i class="ri-earth-line"></i>
                    <span>@lang('cruds.menus.city')</span>
                    @if($countData['other_city_neighborhood_request_user_count'] > 0)
                        <span class="pending_circle sidebar_other_city_neighborhood_request_user_count">
                            {{ $countData['other_city_neighborhood_request_user_count'] }}
                        </span>
                    @endif
                </a>
            </li>
            @endcan

            {{-- Role Menu --}}
            @can('role_access')
            <li class="side-nav-item {{ request()->is('roles') ? 'menuitem-active' : ''}}">
                <a href="{{ route('roles.index') }}" class="side-nav-link {{ request()->is('roles') ? 'active' : ''}}">
                    <i class="ri-user-star-line"></i>
                    <span> @lang('cruds.menus.role') </span>
                </a>
            </li>
            @endcan

            {{-- Admin Menu --}}
            @can('admin_access')
            <li class="side-nav-item {{ request()->is('admins') ? 'menuitem-active' : ''}}">
                <a href="{{ route('admins.index') }}" class="side-nav-link {{ request()->is('admins') ? 'active' : ''}}">
                    <i class=" ri-user-settings-line"></i>
                    <span> @lang('cruds.menus.admin') </span>
                </a>
            </li>
            @endcan

            {{-- Faq Menu --}}
            @can('faq_access')
            <li class="side-nav-item {{ request()->routeIs('faqs.*') ? 'menuitem-active' : '' }}">
                <a href="{{ route('faqs.index') }}" class="side-nav-link {{ request()->routeIs('faqs.*') ? 'active' : '' }}">
                    <i class="ri-questionnaire-line"></i>
                    <span>@lang('cruds.menus.faq')</span>
                </a>
            </li>
            @endcan

             {{-- Faq Menu --}}
            @can('faq_access')
            <li class="side-nav-item {{ request()->routeIs('master-faqs.*') ? 'menuitem-active' : '' }}">
                <a href="{{ route('master-faqs.index') }}" class="side-nav-link {{ request()->routeIs('master-faqs.*') ? 'active' : '' }}">
                    <i class="ri-questionnaire-line"></i>
                    <span>@lang('cruds.menus.masterFaq')</span>
                </a>
            </li>
            @endcan

            {{-- Splash Screen Menu --}}
            @can('splash_screen_access')
            <li class="side-nav-item {{ request()->routeIs('splash-screens.*') ? 'menuitem-active' : '' }}">
                <a href="{{ route('splash-screens.index') }}" class="side-nav-link {{ request()->routeIs('splash-screens.*') ? 'active' : '' }}">
                    <i class="ri-slideshow-line"></i>
                    <span>@lang('cruds.menus.splashScreen')</span>
                </a>
            </li>
            @endcan

            {{-- Announcement Menu --}}
            @can('announcement_access')
            <li class="side-nav-item {{ request()->routeIs('announcements.*') ? 'menuitem-active' : '' }}">
                <a href="{{ route('announcements.index') }}" class="side-nav-link {{ request()->routeIs('announcements.*') ? 'active' : '' }}">
                    <i class="ri-megaphone-line"></i>
                    <span>@lang('cruds.menus.announcement')</span>
                </a>
            </li>
            @endcan

            {{-- Subscription Plan Menu --}}
            @can('plan_access')
            <li class="side-nav-item {{ request()->routeIs('subscription-plans.*') ? 'menuitem-active' : '' }}">
                <a href="{{ route('subscription-plans.index') }}" class="side-nav-link {{ request()->routeIs('subscription-plans.*') ? 'active' : '' }}">
                    <i class="ri-vip-crown-line"></i>
                    <span>@lang('cruds.menus.subscriptionPlan')</span>
                </a>
            </li>
            @endcan

            {{-- Transaction  Menu --}}
            @can('transaction_access')
            <li class="side-nav-item {{ request()->routeIs('transactions.*') ? 'menuitem-active' : '' }}">
                <a href="{{ route('transactions.list') }}" class="side-nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <i class="ri-bank-card-line"></i>
                    <span>@lang('cruds.menus.transaction')</span>
                </a>
            </li>
            @endcan

            {{-- Setting Menu --}}
            @can('setting_access')
            <li class="side-nav-item {{ request()->routeIs('settings.*') ? 'menuitem-active' : '' }}">
                <a href="{{ route('settings.index') }}" class="side-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="ri-settings-line"></i>
                    <span>@lang('cruds.menus.setting')</span>
                </a>
            </li>
            @endcan
        </ul>
        <!--- End Sidemenu -->

        <div class="clearfix"></div>
    </div>
</div>