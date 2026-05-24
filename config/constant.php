<?php

return [
    'default' => [
        'logo' => 'default/logo.png',
        'auth_logo' => 'default/auth_logo.png',
        'favicon' => 'default/favicon.png',
        'no_image' => 'default/no-image.jpg',
        'staff-image' => 'default/staff-img.png',
        'building-image' => 'default/building-image.png',
        'help_pdf' => 'default/help_pdf.pdf',
        'user_icon' => 'default/user-icon.svg',
        'datatable_loader' => 'default/datatable_loader.gif',
        'group_icon' => 'images/groupIcon.svg',
        'firebase_json_file' => storage_path('app/firebase-auth.json'),
        'page_loader' => 'default/page-loader.gif',
        'learner_welcome_video' => 'default/learner_welcome_video.mp4',
        'master_welcome_video' => 'default/master_welcome_video.mp4',
        'email_logo' => 'default/email_logo.png',
    ],
    'profile_max_size' => 2048,
    'profile_max_size_in_mb' => '2MB',

    'role_types' =>['super_admin', 'admin', 'app'],

    'roles' => [
        'super_admin' => 1,
        'master' => 2,
        'customer' => 3
    ],

    'city_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive'
    ],
    
    'user_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive'
    ],

    'approval_status' => [
        '1' => 'Approved',
        '0' => 'Decline'
    ],

    'splash_screen_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive'
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive'
    ],

    'date_format' => [
        'date' => 'd M Y',
        'time' => 'h:i A',
        'date_time' => 'd M Y, h:i A'
    ],

    'search_date_format' => [ //$whereFormat = '%m/%d/%Y %h:%i %p';
        'date' => '%d %b %Y',
        'time' => '%h:%i %p',
        'date_time' => '%d %b %Y, %h:%i %p'
    ],

    'js_date_format' => [
        'date' => 'dd M yyyy',           // e.g., 19 May 2025
        'time' => 'hh:ii A',             // Requires timepicker support
        'date_time' => 'dd M yyyy, hh:ii A'
    ],

    'available_time' => [
        'all_day' => 'All Day',
        'morning' => 'Morning (08:00 AM - 11:59 AM)',
        'afternoon' => 'Afternoon (12:00 PM - 06:00 PM)',
        'evening' => 'Evening (06:00 PM - 12:00 AM)'
    ],
    'available_day' => [
        'all_days'       => "All Days",
        'sunday'    => 'Sunday',
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
        'saturday'  => 'Saturday'
    ],

    'experience' => [
        '1_5_year'  => "1-5 Years",
        '5_10_year'  => "5-10 Years",
        'more_10_year'  => "10+ Years",
    ],

    'education' => [
        "hi_school" => "High School",
        "university_degree" => "University Degree",
        "vocational_certificate" => "Vocational Certificate",
        "experince_only" => "Experience Only"
    ],

    'specialty_level' => [
        "beginner" => "Beginner",
        "intermediate" => "Intermediate",
        "advance" => "Advance"
    ],
    'specialties_request_status' => [
        'accept' => 'Accept',
        'pending'   => 'Pending',
        'archive'   => 'Archive'
    ],
    "gender" => [
        'male' => "Male",
        'female' => "Female",
        // 'other' => "Other",
    ],
    'currency' => 'SAR',
    'currency_symbol' => '﷼',

    'ratings' => [
        1 => '1 Star & above',
        2 => '2 Stars & above',
        3 => '3 Stars & above',
        4 => '4 Stars & above',
        5 => '5 Stars only',
    ],
    'customer_request_status' => [
        'accept' => 'Accept',
        'pending'   => 'Pending',
        'decline'   => 'Decline'
    ],
    'api_page_limit' => [
        'message' => 30,
        'conversation' => 20,
        'notification' => 15,
    ],
    'learning_mode' => [
        'online'    => 'Online (Virtual)',
        'offline'   => 'Personal',
        'both'      => 'Both',
    ],
    'gender_preference' => [
        'any'   => 'Any',
        'male'  => 'Male',
        'female' => 'Female',
    ],
    'faq_type' => [
        'customer' => 'customer',
        'master' => 'master',
        'web'   => 'web'
    ],
    'plan_billing_cycle' => [
        'monthly'   => 'Monthly',
        'yearly'    => 'Yearly'
    ],
    'subscription_status' => [
        'active' => 'Active', 
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'active_cancelled' => 'Active until Expiry',
        'in_grace' => 'Active (Payment issue)'
    ],
    'payment_status'    => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Redunded',
    ],
    'brand_icons' => [
        'VISA'       => 'default/visa.png',
        'MASTER' => 'default/mastercard.png',
        'MADA'       => 'default/mada.png',
    ],
    'plan_status' => [
        '1' => 'Active',
        '0' => 'Inactive'
    ],
    'plan_name' => [
        'basic' => 'basic_plan',
        'gold' => 'gold_plan',
        'premium' => 'premium_plan'
    ],

    'fake_count_for_web' => [
        'masters' => 1500,
        'learners' => 2500
    ]
];
