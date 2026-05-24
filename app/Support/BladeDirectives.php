<?php

namespace App\Support;

use Illuminate\Support\Facades\Blade;

class BladeDirectives
{
    public static function register()
    {
        static::btnLoader();
    }

    protected static function btnLoader()
    {
        Blade::directive('btnLoader', function () {
            return '<i class="fa fa-spinner fa-spin btn_loader d-none"></i>';
        });
    }
}