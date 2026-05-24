<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>
<script src="{{asset('admin-assets/vendor/dropify/dropify.min.js')}}"></script>
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="{{asset('admin-assets/vendor/select2/js/select2.min.js')}}"></script>
{!! $dataTable->scripts() !!}

<script>
var certificateFilesDropzone, idFilesDropzone;
$(document).on('shown.bs.modal', '.modal', function () {
     $('.select2').select2({
        width: '100%',
        dropdownParent: $('.modal'),
        dropdownPosition: 'below',
        selectOnClose: false
    });
    $('.dropify').dropify();
      /////////////////  Highlight empty Dropify fields in orange////////////
    $('.dropify').each(function() {
        let $input = $(this);
        let $wrapper = $input.closest('.dropify-wrapper');

        // If no existing image (data-default-file empty)
        if (!$input.attr('data-default-file') || $input.attr('data-default-file').trim() === '') {
            $wrapper.addClass('border-orange'); // 🔶 add orange border class
        } else {
            $wrapper.removeClass('border-orange');
        }

        // When user uploads a new file
        $input.on('change', function() {
            if ($input.val()) {
                $wrapper.removeClass('border-orange');
            } else {
                $wrapper.addClass('border-orange');
            }
        });
    });
    ////////////////////END///////////////////
    $('.dropify-errors-container').remove();
    $('.dropify-wrapper').find('.dropify-clear').hide();
});

@can('customer_ban')
$(document).on('click','.customer_isban', function(){
    let $this = $(this);
    let userId = $this.data('customer_id');
    let isBanned = $this.data('is_ban');
    let action = isBanned ? "unban" : "ban";
    let confirmTemplate = "{{ __('global.ban_unban_confirm_text') }}";
    let confirmText = confirmTemplate
    .replace(':action', action)
    .replace(':user_type', 'Learner');
    let flag = true;
    let csrf_token = $('meta[name="csrf-token"]').attr('content');
    if($this.prop('checked')){
        flag = false;
    }
    Swal.fire({
            title: "{{ trans('global.areYouSure') }}",
            text: confirmText,
            icon: "warning",
            showDenyButton: true, 
            confirmButtonText: "{{ trans('global.swl_confirm_button_text') }}",  
            denyButtonText: "{{ trans('global.swl_deny_button_text') }}",
    })
    .then(function(result) {
        if (result.isConfirmed) { 
            pageLoader('show'); 
            $.ajax({
                type: 'POST',
                url: "{{ route('customers.isban') }}",
                dataType: 'json',
                data: { _token: csrf_token, id: userId },
                success: function (response) {
                    if(response.status == 'true') {
                        toasterAlert('success',response.message);
                        $('#customer-table').DataTable().ajax.reload(null, false);
                    }
                },
                error:function (response){
                    $this.prop('checked', flag);
                    toasterAlert('error',response.error);
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
@endcan

@can('customer_status')
$(document).on('click','.customer_status_cb', function(){
    let $this = $(this);
    let userId = $this.data('customer_id');
    let flag = true;
    let csrf_token = $('meta[name="csrf-token"]').attr('content');
    if($this.prop('checked')){
        flag = false;
    }
    Swal.fire({
            title: "{{ trans('global.areYouSure') }}",
            text: "{{ trans('global.want_to_change_status') }}",
            icon: "warning",
            showDenyButton: true, 
            confirmButtonText: "{{ trans('global.swl_confirm_button_text') }}",  
            denyButtonText: "{{ trans('global.swl_deny_button_text') }}",
    })
    .then(function(result) {
        if (result.isConfirmed) { 
            pageLoader('show'); 
            $.ajax({
                type: 'POST',
                url: "{{ route('customers.status') }}",
                dataType: 'json',
                data: { _token: csrf_token, id: userId },
                success: function (response) {
                    if(response.status == 'true') {
                        toasterAlert('success',response.message);
                        $('#customer-table').DataTable().ajax.reload(null, false);
                    }
                },
                error:function (response){
                    $this.prop('checked', flag);
                    toasterAlert('error',response.error);
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
@endcan

@can('customer_edit')
 $(document).on("click", ".btnEditCustomer", function() {
        pageLoader('show');
        let url = $(this).data('href');
        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#editCustomer').modal('show');
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
    });

    $(document).on('submit','#editCustomerForm', function(e) {
        e.preventDefault();
        pageLoader('show', true);

        $('.validation-error-block').remove();
        var formData = new FormData(this);
        let url = $(this).data('href');

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            processData: false, // Prevent jQuery from processing the data
            contentType: false, // Prevent jQuery from setting content type
            success: function (response) {
                if(response.success) {
                    $('#editCustomer').modal('hide');
                    $('#customer-table').DataTable().ajax.reload(null, false);
                    toasterAlert('success',response.message);
                }
                else {
                    toasterAlert('error', response.error);
                }
            },
            error: function (response) {
                if(response.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',response.responseJSON.error);
                } else {
                    let errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = `<span class="validation-error-block">${item[0]}</span>`;
                        $("#"+key).siblings('.select2').after(errorLabelTitle);
                        let inputElmt = $(`input[name='${key}']`);
                        if(inputElmt.attr('type') == 'password'){
                            inputElmt.closest('.input-group').after(errorLabelTitle);
                        } else {
                            inputElmt.after(errorLabelTitle);
                        }
                    });
                }
            },
            complete: function(xhr){
                pageLoader('hide', true);
            }
        });
    }); 
@endcan

@can('customer_edit')
$(document).on("click", ".btnChangePassword", function() {
        pageLoader();
        let url = $(this).data('href');

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#ChangePassword').modal('show');
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
    });

    $(document).on('submit','#ChangePasswordForm', function(e) {
        e.preventDefault();

        let url = $(this).data('href');

        pageLoader('show', true);

        $('.validation-error-block').remove();
        
        var formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            processData: false, // Prevent jQuery from processing the data
            contentType: false, // Prevent jQuery from setting content type
            success: function (response) {
                if(response.success) {
                    $('#ChangePassword').modal('hide');
                    $('#admin-table').DataTable().ajax.reload(null, false);
                    toasterAlert('success',response.message);
                }
                else {
                    toasterAlert('error', response.error);
                }
            },
            error: function (response) {
                if(response.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',response.responseJSON.error);
                } else {
                    let errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = `<span class="validation-error-block">${item[0]}</span>`;
                        $(`textarea[name='${key}']`).after(errorLabelTitle);

                        let inputElmt = $(`input[name='${key}']`);
                        if(inputElmt.attr('type') == 'password' || inputElmt.attr('type') == 'text'){
                            inputElmt.closest('.input-group').after(errorLabelTitle);
                        } else if(key == 'roles'){
                            $(`#roles`).after(errorLabelTitle);
                        } else {
                            inputElmt.after(errorLabelTitle);
                        }
                    });
                }
            },
            complete: function(xhr){
                pageLoader('hide', true);
            }
        });
}); 
@endcan

@can('customer_show')
    $(document).on("click", ".btnViewCustomer", function() {
        let url = $(this).data('href');
        pageLoader();

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#ViewCustomer').modal('show');
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
    });
@endcan

@can('customer_delete')
$(document).on("click",".deleteCustomerBtn", function() {
        let url = $(this).data('href');
        Swal.fire({
            title: "{{ trans('global.areYouSure') }}",
            text: "{{ trans('global.onceClickedRecordDeleted') }}",
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
                    type: 'DELETE',
                    url: url,
                    dataType: 'json',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if(response.success) {
                            $('#customer-table').DataTable().ajax.reload(null, false);
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
        });
    });
@endcan
</script>