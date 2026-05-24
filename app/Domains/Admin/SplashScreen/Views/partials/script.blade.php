<script src="{{asset('admin-assets/vendor/select2/js/select2.min.js')}}"></script>
<script src="{{asset('admin-assets/vendor/dropify/dropify.min.js')}}"></script>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
<script>

$(document).on('shown.bs.modal', '.modal', function () {
    const $modal = $(this);
    $modal.find('.select2').each(function () {
        const $select = $(this);
        // Prevent double initialization
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            width: '100%',
            dropdownParent: $('.modal-body'),
            dropdownPosition: 'below',
            selectOnClose: false,
        });
    });

    $('.dropify').dropify();
    $('.dropify-errors-container').remove();
    $('.dropify-wrapper').find('.dropify-clear').hide();
});



@can('splash_screen_create')
    $(document).on("click", ".btnAddSplashScreen", function() {
        pageLoader('show');
        let url = $(this).data('create-url');
        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#AddSplashScreen').modal('show');
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

    $(document).on('submit','#addSplashScreenForm', function(e) {
        e.preventDefault();
        pageLoader('show', true);

        $('.validation-error-block').remove();
        let form = $(this)[0]; 
        var formData = new FormData(form);
        let url = $(form).data('store-url');
        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            processData: false, 
            contentType: false,
            success: function (response) {
                if(response.success) {
                    $('#AddSplashScreen').modal('hide');
                    toasterAlert('success',response.message);

                    setTimeout(function() {                  
                        window.location.reload();
                    }, 1000);
                }
                else {
                    toasterAlert('error', response.error);
                }
            },
            error: function (response) {
                if(response.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',response.responseJSON.error);
                } else {
                    var errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = `<span class="validation-error-block">${item[0]}</span>`;

                        $("input[name='" + key + "']").after(errorLabelTitle);
                        $("textarea[name='" + key + "']").after(errorLabelTitle);

                         $("#"+key).siblings('.select2').after(errorLabelTitle);
                    });
                }
            },
            complete: function(xhr){
                pageLoader('hide', true);
            }
        });
    }); 
@endcan

@can('splash_screen_show')
    $(document).on("click", ".btnViewSplashScreen", function() {
        pageLoader('show');
        var url = $(this).data('href');

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#ViewSplashScreen').modal('show');
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

@can('splash_screen_edit')
    $(document).on("click", ".btnEditSplashScreen", function() {
        pageLoader('show');
        var url = $(this).data('href');

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#editSplashScreen').modal('show');
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

    $(document).on('submit','#editSplashScreenForm', function(e) {
        e.preventDefault();
       pageLoader('show', true);

        $('.validation-error-block').remove();
        var formData = new FormData(this);

        var url = $(this).data('href');

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            processData: false, 
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('#editSplashScreen').modal('hide');
                    toasterAlert('success',response.message);
                    
                    setTimeout(function() {                  
                        window.location.reload();
                    }, 1000);
                }
                else {
                    toasterAlert('error', response.error);
                }
            },
            error: function (response) {
                if(response.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',response.responseJSON.error);
                }else if(response.responseJSON.error_type == 'validation_error') {
                    // ✅ Show validation error under Select2
                    var errorLabelTitle = '<span class="validation-error-block">'+response.responseJSON.error+'</span>';
                    $("#splash_screen_status").next('.select2').after(errorLabelTitle);
                } 
                else {
                    var errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = '<span class="validation-error-block">'+item[0]+'</span>';

                        $("input[name='" + key + "']").after(errorLabelTitle);
                        $("textarea[name='" + key + "']").after(errorLabelTitle);

                        $("#"+key).siblings('.select2').after(errorLabelTitle);
                    });
                }
            },
            complete: function(xhr){
                pageLoader('hide', true);
            }
        });
    }); 
@endcan

@can('splash_screen_delete')
    $(document).on("click",".deleteSplashScreenBtn", function() {
        var url = $(this).data('href');
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
                            toasterAlert('success',response.message);

                            setTimeout(function() {                  
                                window.location.reload();
                            }, 1000);
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

// let page = 1;
// let loading = false;

// $(window).scroll(function() {
//     if(loading) return;
    
//     if($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
//         loading = true;
//         page++;

//         $('#loading').show();

//         $.ajax({
//             url: '?page=' + page,
//             type: 'get',
//             success: function(data) {
//                 if(data.trim().length === 0) {
//                     $('#loading').html('No more records').show();
//                     setTimeout(function() {
//                         $('#loading').fadeOut();
//                     }, 1000);
                    
//                     return;
//                 }
//                 $('.splash_screen_list').append(data);
//                 $('#loading').hide();
//                 loading = false;
//             },
//             error: function() {
//                 $('#loading').hide();
//                 loading = false;
//             }
//         });
//     }
// });

</script>

<script>
    $(function () {
        $('#sortable_screens').sortable({
            handle: '.drag-handle', // drag by header
            update: function (event, ui) {
                let ordered = [];

                $('#sortable_screens .faq_inner').each(function (index) {
                    ordered.push({
                        id: $(this).data('id'),
                        position: index // 0 to 9
                    });
                });

                $.ajax({
                    url: '{{ route("splash-screens.sort") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        order: ordered
                    },
                    success: function (res) {
                        console.log(res.message);
                    },
                    error: function () {
                        alert("Error updating order");
                    }
                });
            }
        });
    });
</script>