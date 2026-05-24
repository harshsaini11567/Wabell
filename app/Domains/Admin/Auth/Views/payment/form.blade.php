@extends('Layouts::auth')
@section('title', __('global.login'))
@section('main-content')

<div class="l_content">
    <div class="container px-0">
        <div class="row items-center justify-center">
            <div class="col-xl-5 col-lg-6">
                <div class="log-register-block">
                    <form action="{{ $returnUrl }}" class="paymentWidgets" data-brands="{{ $brand }}"></form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('custom_js')
    <script>
    var wpwlOptions = {
        paymentTarget: "_top",
        applePay: {
        displayName: "MyStore",
        total: { label: "COMPANY, INC." },
        supportedNetworks: ["masterCard", "visa", "mada"],
        supportedCountries: ["SA"]
        }
    };
    </script>

    <style>
    .wpwl-apple-pay-button {
        -webkit-appearance: -apple-pay-button !important;
    }
    </style>
    <script src="{{ config('services.hyperpay.base_url') }}/v1/paymentWidgets.js?checkoutId={{ $checkoutId }}"></script>
@endsection