<!-- Vendor js -->
<script src="{{ asset('admin-assets/js/vendor.min.js') }}"></script>

<!-- App js -->
<script src="{{ asset('admin-assets/js/app.min.js') }}"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $( document ).ajaxError(function( event, response, settings ) {
        if(response.status == 401){
            window.location.href = "{{ route('login') }}";
        }
    });

    $(document).on('click', '.toggle-password', function () {        
        let passwordInput = $(this).closest('.input-group').find('input');   
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            $(this).removeClass('show-password');
        } else {
            passwordInput.attr('type', 'password');
            $(this).addClass('show-password');
        }
    });

    function btnloader(type='show') {
        if(type === 'show'){
            $("button[type='submit']").attr('disabled', true);
            $('.btn_loader').removeClass('d-none');
        } else {
            $("button[type='submit']").attr('disabled', false);
            $('.btn_loader').addClass('d-none');
        }
    }

    function pageLoader(type='show', isForm=false){
        if(type === 'show'){
            $('.loader-div').show();
            if(isForm){
                $("button[type='submit']").attr('disabled', true);
            }
        } else {
            $('.loader-div').hide();
            if(isForm){
                $("button[type='submit']").attr('disabled', false);
            }
        }
    }

    $(document).on("click",".userLogoutBtn", function() {
        var url = $(this).data('href');
        Swal.fire({
            title: "{{ trans('global.areYouSure') }}",
            text: "{{ trans('messages.logout_confirmation') }}",
            icon: "warning",
            showDenyButton: true,  
            //   showCancelButton: true,  
            confirmButtonText: "{{ trans('global.swl_confirm_button_text') }}",  
            denyButtonText: "{{ trans('global.swl_deny_button_text') }}",
        })
        .then(function(result) {
            if (result.isConfirmed) {  
                pageLoader('show');
                $.ajax({
                    type: 'GET',
                    url: url,
                    dataType: 'json',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if(response.success) {
                            window.location.href = response.redirect_url;
                        }
                        else {
                            toasterAlert('error',response.error);
                        }
                    },
                    error: function(res){
                        toasterAlert('error',res.responseJSON.error);
                    },
                    complete: function(xhr){
                        pageLoader('hide');
                    }
                });
            }
        });
    });
  

    $(document).on("click",".tutor_chat_status_cb", function() {
        let $this = $(this);
        var url = $(this).data('href');
        let flag = true;
        if($this.prop('checked')){
            flag = false;
        }
        Swal.fire({
            title: "{{ trans('global.areYouSure') }}",
            text: "{{ trans('messages.updateChatStatus') }}",
            icon: "warning",
            showDenyButton: true,  
            //   showCancelButton: true,  
            confirmButtonText: "{{ trans('global.swl_confirm_button_text') }}",  
            denyButtonText: "{{ trans('global.swl_deny_button_text') }}",
        })
        .then(function(result) {
            if (result.isConfirmed) {  
                pageLoader('show');
                $.ajax({
                    type: 'GET',
                    url: url,
                    dataType: 'json',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if(response.success) {
                            toasterAlert('success',response.message);
                        }
                        else {
                            toasterAlert('error',response.error);
                        }
                    },
                    error: function(res){
                        toasterAlert('error',res.responseJSON.error);
                    },
                    complete: function(xhr){
                        pageLoader('hide');
                    }
                });
            }
            else {
                $this.prop('checked', flag);
            }
        });
    });


</script>

@include('Layouts::partials.alert')