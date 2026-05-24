@extends('Layouts::auth')
@section('title', trans('global.forgot_password_title'))
@section('main-content')

<div class="l_content">
    <div class="container px-0">
        <div class="row items-center justify-center">
            <div class="col-xl-5 col-lg-6">
                <div class="log-register-block">
                    <div class="text-center mb-3">
                        <a href="#" title="logo" class="header-logo">
                            {{-- <img src="{{asset('default/logo-dark.png')}}" alt="logo"> --}}
                            <img src="{{ getSetting('auth_logo') ? getSetting('auth_logo') : asset(config('constant.default.auth_logo')) }}" alt="logo">
                        </a>
                    </div>
                    <h2 class="text-center mb-1">Reset Your Password</h2>
                    <p class="text-center">Reset your password to continue</p>
                    <form id="reset_password_form">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" tabindex="1">
                                <div class="input-group-text toggle-password show-password" data-password="true">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password-confirm" class="form-label">Confirm Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password-confirm" name="password_confirmation" class="form-control" placeholder="Enter confirm password" tabindex="2">
                                <div class="input-group-text toggle-password show-password" data-password="true">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                        </div>
                        <div class="btn-block">
                            <button class="btn btn-soft-primary w-100" type="submit">
                                @lang('global.submit')
                                @btnLoader
                            </button>
                        </div>
                    </form>
                    <div class="text-center mt-2">
                        <a href="{{ route('login') }}" class="text-decoration-underline" style="color: #279EFF;"><i class="ri-arrow-left-line"></i> Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('custom_js')

<script>

// Reset Ajax
$(document).on('submit', '#reset_password_form', function(e){
    e.preventDefault();
    let formData = new FormData(this);

    $('.validation-error-block').remove();
    
    btnloader('show');
    $.ajax({
        type: 'post',
        url: '{{route("reset-new-password")}}',
        data: formData,
        dataType: "json",
        processData: false, // Prevent jQuery from processing the data
        contentType: false, // Prevent jQuery from setting content type
        success: function(response, textStatus, jqXHR){
            window.location.href=response.redirect_url;
        },
        error: function(response, textStatus, jqXHR){
            if(response.status === 400){
                toasterAlert('error',response.responseJSON.message);
            } else {                    
                var errorLabelTitle = '';
                $.each(response.responseJSON.errors, function (key, item) {
                    errorLabelTitle = '<span class="validation-error-block">'+item[0]+'</sapn>';
                    
                    // $(errorLabelTitle).insertAfter("input[name='"+key+"']");
                    let inputElmt = $(`input[name='${key}']`);
                    if(inputElmt.closest('.input-group').find('.password-eye')){
                        inputElmt.closest('.input-group').after(errorLabelTitle);
                    } else {
                        inputElmt.after(errorLabelTitle);
                    }
                });
            }
        },
        complete: function(response, textStatus, jqXHR){
            btnloader('hide');
        }
    });
});

</script>
@endsection