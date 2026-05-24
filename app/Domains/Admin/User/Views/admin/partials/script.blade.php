<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>

<script src="{{asset('admin-assets/vendor/select2/js/select2.min.js')}}"></script>
<script src="{{asset('admin-assets/vendor/dropify/dropify.min.js')}}"></script>

{!! $dataTable->scripts() !!}

<script>

$(document).on('shown.bs.modal', '.modal', function () {
    $('.select2').select2({
        width: '100%',
        dropdownParent: $('.modal'),
        dropdownPosition: 'below',
        selectOnClose: false
    });

    $('.dropify').dropify();
    $('.dropify-errors-container').remove();
    $('.dropify-wrapper').find('.dropify-clear').hide();
});

@can('admin_create')
    $(document).on("click", ".btnAddAdmin", function() {
        pageLoader();
        let url = $(this).data('href');

        $.ajax({
            type: 'get',
            url: "{{ route('admins.create') }}",
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#AddAdmin').modal('show');
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

    $(document).on('submit','#AddAdminForm', function(e) {
        e.preventDefault();

        pageLoader('show', true);

        $('.validation-error-block').remove();
        // let formData = $(this).serialize();
        var formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: "{{route('admins.store')}}",
            data: formData,
            dataType: 'json',
            processData: false, // Prevent jQuery from processing the data
            contentType: false, // Prevent jQuery from setting content type
            success: function (response) {
                if(response.success) {
                    $('#AddAdmin').modal('hide');
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
                        if(inputElmt.attr('type') == 'password'){
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

@can('admin_show')
    $(document).on("click", ".btnViewAdmin", function() {
        let url = $(this).data('href');
        pageLoader();

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#ViewAdmin').modal('show');
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

@can('admin_edit')
    $(document).on("click", ".btnEditAdmin", function() {
        pageLoader('show');
        let url = $(this).data('href');

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#editAdmin').modal('show');
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

    $(document).on('submit','#editAdminForm', function(e) {
        e.preventDefault();
        pageLoader('show', true);

        $('.validation-error-block').remove();
        // let formData = $(this).serialize();

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
                    $('#editAdmin').modal('hide');
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

                        // $(`select[name='${key}']`).after(errorLabelTitle);
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

@can('admin_delete')
$(document).on("click",".deleteAdminBtn", function() {
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
                            $('#admin-table').DataTable().ajax.reload(null, false);
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

$(document).on('click','.admin_status_cb', function(){
    let $this = $(this);
    let userId = $this.data('admin_id');
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
                url: "{{ route('admins.status') }}",
                dataType: 'json',
                data: { _token: csrf_token, id: userId },
                success: function (response) {
                    if(response.status == 'true') {
                        toasterAlert('success',response.message);
                        $('#admin-table').DataTable().ajax.reload(null, false);
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
                        if(inputElmt.attr('type') == 'password'){
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

</script>