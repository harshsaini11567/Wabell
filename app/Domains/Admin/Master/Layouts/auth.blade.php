<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{{ getSetting('site_title') ? getSetting('site_title') : config('app.name') }} | @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta content="A fully responsive admin theme which can be used to build CRM, CMS,ERP etc." name="description" />
    <meta content="Techzaa" name="author" /> -->

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ getSetting('favicon') ? getSetting('favicon') : asset(config('constant.default.favicon')) }}">

    <!-- App css -->
    <link href="{{ asset('admin-assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="{{ asset('admin-assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/fontawesome/css/fontawesome-all.min.css') }}" />
    
    <!-- Main css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/css/style.css') }}">

    @yield('custom_css')
</head>

<body class="authentication-bg position-relative">
    <div class="account-pages position-relative">
        <div class="container">
            @yield('main-content')
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->


    @include('Layouts::partials.fscript')

    @yield('custom_js')
</body>
</html>