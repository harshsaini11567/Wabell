<?php

return [ 
    'user_register_welcome_mail_master' => [
        'subject' => 'Welcome to Wabell platform',
        'body' => [
            'line1' => 'Dear :user_name,',
            'line2' => "Welcome to Wabell platform! We are excited to have you on board. You can now log in and start presenting your experience and skills to enable potential students to reach you directly to achieve your goals.",
            'line3' => 'Thank you for joining us!',
        ]
    ],

    'user_register_welcome_mail_student' => [
        'subject' => 'Welcome to Wabell platform',
        'body' => [
            'line1' => 'Dear :user_name,',
            'line2' => "Welcome to Wabell platform! We are excited to have you on board. You can now log in and start exploring your potential Master near you to achieve your goals.",
            'line3' => 'Thank you for joining us!',
        ]
    ],

    'user_register_mail_super_admin' => [
        'subject' => 'A new user has registered',
        'body' => [
            'line1' => 'Hello,',
            'line2' => "A new user has just registered on the platform:",
            'line3' => '<strong>Name:</strong> :username',
            'line4' => '<strong>Email:</strong> :userEmail',
            'line5' => '<strong>Role:</strong> :role',
            'line6' => '<strong>Mobile Number: </strong> 0:phone_number',
            'line7' => 'Please review their profile if needed.',
        ]
    ],

    'speciality_request_mail_super_admin' => [
        'subject' => 'Specialty Request Mail',
        'body' => [
            'line1' => 'Hello,',
            'line2' => "You are receiving this email because a Wabell user sent a specialty request.",
            'line3' => 'Please click on the link below to perform an action :',
            'button'=> 'Specialty Request',
            'line4' => 'If you are having trouble clicking the "Specialty Request" button, copy and paste the URL below into your web browser: :specialty_request_url',
        ]
    ],

    'speciality_added_by_master_mail_student' => [
        'subject' => 'Specialty Added by Master',
        'body' => [
            'line1' => 'Dear :user_name,',
            'line2' => "Congrats! Your specialty request <strong>:specialty_name </strong>  has been accepted by Wabell! We hear you, and we're committed to supporting you to reach your goals.",
        ]
    ],

    'forgot_password_otp_mail_user' => [
        'subject' => 'Reset Password OTP',
        'body' => [
            'line1' => 'Dear :user_name,',
            'line2' => "We received a request to reset your password. Please use the following OTP to proceed:",
            'line3' => '<strong>Your OTP:</strong> :token',
            'line4' => 'This OTP will expire in :expiretime. If you did not request a password reset, please ignore this email.',
        ]
    ],

    'reset_password_mail_user' => [
        'subject' => 'Reset Password Notification',
        'body' => [
            'line1' => 'Dear :user_name,',
            'line2' => "your account:",
            'line3' => 'You recently asked to reset your password to an account registered under the username :email To do so, simply click the link below',
            'button'=> 'Reset Password',
            'line4' => 'If you are having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: :reset_password_url',
        ]
    ],
    
    'subscription_activation_mail_master' => [
        'subject' => 'Your Subscription is Activated',
        'body' => [
            'line1' => 'Hello :user_name,',
            'line2' => "Thank you for subscribing to our <strong>:plan_name</strong>.",
            'line3' => '<strong>Billing Cycle</strong>: :billing_cycle',
            'line4' => '<strong>Start Date</strong>: :start_date',
            'line5' => '<strong>End Date</strong>: :end_date',
            'line6' => '<strong>Amount Paid</strong>: :price',
            'line7' => 'Your subscription is now active. Enjoy all the features included in your plan!',
        ]
    ],

    'subscription_activation_mail_super_admin' => [
        'subject' => 'New Subscription Purchased',
        'body' => [
            'line1' => 'Hello,',
            'line2' => "A new subscription has been purchased by:",
            'line3' => '<strong>Name:</strong> :user_name',
            'line4' => '<strong>Email:</strong> :user_email',
            'line5' => '<strong>Mobile:</strong> 0:phone_number',
            'line6' => '<strong>Bundle:</strong> :plan_name',
            'line7' => '<strong>Billing Cycle:</strong> :billing_cycle',
            'line8' => '<strong>Amount Paid:</strong> :price',
            'line9' => '<strong>Start Date:</strong> :start_date',
            'line10' => '<strong>End Date:</strong> :end_date',
        ]
    ],

    'subscription_plan_deactivated_master' => [
        'subject' => 'Your subscription plan has been deactivated',
        'body' => [
            'line1' => 'Hello :user_name',
            'line2' => "We would like to inform you that your current subscription plan <strong>:plan_name</strong> has been deactivated by the administrator.",
            'line3' => 'You can continue using the plan until <strong>:end_date</strong>, but it will not auto-renew. Please choose a new plan to continue without interruption.',
        ]
    ],

    'subscription_plan_activated_master' => [
        'subject' => 'Good news! Your subscription plan is active again',
        'body' => [
            'line1' => 'Hello :user_name',
            'line2' => 'We\'re excited to let you know that your subscription plan <strong>:plan_name</strong> has been reactivated by the administrator.',
            'line3' => 'If your subscription expired earlier, you can purchase this plan again from your dashboard.',
        ]
    ],

    'speciality_update_mail_user' => [
        'subject' => 'Specialty Request Accepted',
        'body' => [
            'line1' => 'Dear :name,',
            'line2' => "You are receiving this email because Wabell accepts the <b>:specialty_name</b> specialty request.",
        ]
    ],

    'user_ban_status_mail' => [
        'subject' => 'Your Account Has Been Banned',
        'body' => [
            'line1' => 'Hello :name',
            'line2' => 'We regret to inform you that your account has been banned by the admin. If you believe this is a mistake or need further assistance, please contact our support team at :supportEmail.',
            'line3' => 'Thank you for your understanding',
        ]
    ],
    'user_selected_other_location_mail' => [
        'subject' => 'User selected Other city/neighborhood',
        'body' => [
            'line1' => 'Hello',
            'line2' => 'A new user has registered and selected <strong>"Other"</strong> for city or neighborhood.',
            'line3' => 'Name: :name',
            'line4' => 'Email: :email',
            'line5' => 'Phone: 0:phone',
            'line6' => 'City: :city',
            'line7' => 'Neighborhood: :neighborhood',
            'line8' => 'Role: :role',
        ]
    ],

    'master_subscription_expired_renew_mail' => [
        'subject' => 'Action needed: Update your payment to keep your subscription active',
        'body' => [
            'line1' => 'Hello :name',
            'line2' => 'We were unable to renew your subscription because of a payment issue with your App Store account.',
            'line3' => 'Good news: your subscription is still active for now, and you can continue enjoying all premium features without interruption.',
            'line4' => 'To avoid losing access in the future, please update your payment method in your Apple ID settings',
            'line5' => 'Apple will retry the payment automatically once your payment details are updated.',
            'line6' => 'The payment was declined by your bank',
            'line7' => 'If the payment is not completed before the end of the grace period, your subscription may expire.',
            'line8' => 'If you need any help, feel free to contact our support team.'
        ]
    ],

    'master_subscription_expired_mail' => [
        'subject' => 'Your subscription has been cancelled',
        'body' => [
            'line1' => 'Hello :name',
            'line2' => 'Your Wabell subscription has expired as the current billing period has ended.',
            'line3' => 'You no longer have access to premium features associated with your subscription.',
            'line4' => 'You can renew your subscription anytime by opening the app and selecting a plan that works best for you.',
            'line5' => 'We would love to have you back!',
        ]
    ],

    'master_subscription_cancelled_mail' => [
        'subject' => 'Your subscription has been cancelled',
        'body' => [
            'line1' => 'Hello :name',
            'line2' => 'Your Wabell subscription has expired because it was cancelled and has now reached the end of its active period.',
            'line3' => 'You will no longer have access to premium features, but you can resubscribe at any time through the app.',
            'line4' => 'Thank you for being a part of Wabell. We hope to see you again soon.',
        ]
    ],

    'regards'   => 'Regards',
    'team'      => 'Wabell',
    'copyright' => '© :year All Copyrights Reserved By Wabell',
    'wabell'    => 'Wabell',
    'footer_data' => 'Wabell Platform, For people, By People',
    'privacy_policy' => 'Privacy Policy',
    'terms_of_service' => 'Terms Of Service'
];


?>