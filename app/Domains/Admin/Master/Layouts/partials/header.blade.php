<div class="navbar-custom">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-1">

            <!-- Topbar Brand Logo -->
            <div class="logo-topbar">
                <!-- Logo light -->
                <a href="{{ route('admin.dashboard') }}" class="logo-light">
                    <span class="logo-lg">
                        <img src="{{ getSetting('site_logo') ? getSetting('site_logo') : asset(config('constant.default.logo')) }}" alt="logo">
                    </span>
                    <span class="logo-sm">
                        <!-- <img src="{{ getSetting('site_logo') ? getSetting('site_logo') : asset(config('constant.default.logo')) }}" alt="small logo"> -->
                         <img src="{{ getSetting('auth_logo') ? getSetting('auth_logo') : asset(config('constant.default.auth_logo')) }}" alt="logo">
                    </span>
                </a>
            </div>

            <!-- Sidebar Menu Toggle Button -->
            <button class="button-toggle-menu">
                <i class="ri-menu-line"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <div class="lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>

           
        </div>
        <div class="d-flex align-items-center">
            <div class="checkbox switch d-flex align-items-center gap-1">
                <span>Master Chat Status:</span>
                <label>
                    <input type="checkbox" class="switch-control tutor_chat_status_cb" id="tutor_chat_status_cb" data-href="{{ route('settings.tutor_chat_status') }}" {{getSetting('tutor_chat_status_cb') == 1 ? 'checked' : ''}}/>
                    <span class="switch-label"></span>
                </label>
            </div>

            <ul class="topbar-menu d-flex align-items-center gap-3">
                <li class="dropdown">
                    <a class="nav-link dropdown-toggle arrow-none nav-user" data-bs-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false">
                        <span class="account-user-avatar">
                            @if(auth()->user()->profile_image_url)
                            <img src="{{ auth()->user()->profile_image_url }}" alt="user-image" width="32" class="rounded-circle user-profile-img">
                            @else
                            <img src="{{ asset(config('constant.default.user_icon')) }}" alt="user-image" width="32" class="rounded-circle user-profile-img">
                            @endif
                        </span>
                        <span class="d-lg-block d-none">
                            <h5 class="my-0 fw-normal user_profile">
                                <span class="user-profile-name">{{ ucwords(auth()->user()->name) }}</span> 
                                <i class="ri-arrow-down-s-line d-none d-sm-inline-block align-middle"></i>
                            </h5>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
                        <!-- item-->
                        <div class=" dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome !</h6>
                        </div>

                        <!-- item-->
                        <a href="{{ route('show.profile') }}" class="dropdown-item">
                            <i class="ri-account-circle-line fs-18 align-middle me-1"></i>
                            <span>Profile</span>
                        </a>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item userLogoutBtn" data-href="{{ route('auth.logout') }}" title="Logout">
                            <i class="ri-logout-box-line fs-18 align-middle me-1"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>