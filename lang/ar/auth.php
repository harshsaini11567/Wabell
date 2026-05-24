<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'بيانات الاعتماد هذه لا تطابق سجلاتنا.',
    'password' => 'كلمة المرور المقدمة غير صحيحة.',
    'throttle' => 'عدد كبير جدًا من محاولات الدخول. يرجى المحاولة مرة أخرى خلال :seconds ثانية.',
    
    'messages' => [
        'account_approval'=> 'يرجى الانتظار حتى يتم الموافقة على حسابك للوصول.',
        'registeration' => [
            'success'               => 'تم التسجيل بنجاح.',
            'phone_unique'          => 'رقم الهاتف مستخدم من قبل.',
        ],
        'login' => [
            'success'               => 'تم تسجيل الدخول بنجاح.',
            'failed'                => 'بيانات اعتماد غير صالحة! يرجى المحاولة مرة أخرى.',
        ],
        'logout' => [
            'success'               => 'تم تسجيل الخروج بنجاح.',
            'failed'                => 'لقد قمت بتسجيل الخروج بالفعل.',
        ],
        'forgot_password' => [
            'success'               => 'لقد أرسلنا رسالة بريد إلكتروني تحتوي على رابط إعادة تعيين كلمة المرور. يرجى التحقق من بريدك الوارد!',
            'success_update'        => 'تم إعادة تعيين كلمة المرور بنجاح.',
            'otp_sent'              => 'لقد أرسلنا رمز التحقق إلى بريدك الإلكتروني. يرجى التحقق من بريدك الوارد!',
            'validation'            => [
                'phone_number_not_found'=> 'لا يمكننا العثور على مستخدم بهذا الرقم.',
                'verified_phone_number' => 'رقم الهاتف هذا تم التحقق منه بالفعل.',
                'email_not_found'       => 'لا يمكننا العثور على مستخدم بهذا البريد الإلكتروني.',
                'incorrect_password'    => 'كلمة المرور الحالية غير صحيحة! يرجى المحاولة مرة أخرى.',
                'invalid_otp'           => 'رمز التحقق غير صالح.',
                'expire_otp'            => 'انتهت صلاحية رمز التحقق.',
                'verified_otp'          => 'تم التحقق من رمز التحقق.',
                'expire_request'        => 'انتهت صلاحية طلب إعادة تعيين كلمة المرور.',
                'invalid_request'       => 'طلب إعادة تعيين كلمة المرور غير صالح.',
                'invalid_token_email'   => 'رمز أو بريد إلكتروني غير صالح!',
                'same_as_old_password'  => 'لا يمكنك تعيين كلمة المرور الجديدة نفس كلمة المرور القديمة.',
            ],
        ],
    ],

    'unauthorize'  => 'أنت غير مخول لتسجيل الدخول.',

];
