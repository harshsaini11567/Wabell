<script src="{{asset('admin-assets/vendor/select2/js/select2.min.js')}}"></script>

<script>

@can('specialties_create')
    $(document).on("click", ".btnAddSpecialty", function() {
        pageLoader('show');
        var url = $(this).data('href');

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#AddSpecialty').modal('show');
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

    $(document).on('submit','#AddSpecialtyForm', function(e) {
        e.preventDefault();
        pageLoader('show', true);

        $('.validation-error-block').remove();
        var formData = new FormData(this);

        var url = $(this).data('href');

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (response) {
                if(response.success) {
                    $('#AddSpecialty').modal('hide');
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

@can('specialties_view')
    $(document).on("click", ".btnViewSpecialty", function() {
        pageLoader('show');
        var url = $(this).data('href');

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#ViewSpecialty').modal('show');
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

@can('specialties_edit')
    $(document).on("click", ".btnEditSpecialty", function() {
        pageLoader('show');
        var url = $(this).data('href');

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#editSpecialty').modal('show');
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

    $(document).on('submit','#editSpecialtyForm', function(e) {
        e.preventDefault();
       pageLoader('show', true);

        $('.validation-error-block').remove();
        var formData = new FormData(this);

        var url = $(this).data('href');

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (response) {
                if(response.success) {
                    $('#editSpecialty').modal('hide');
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

@can('specialties_delete')
    $(document).on("click",".deleteSpecialtyBtn", function() {
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


$(document).on('click', '.btnGetChildSpecialty', function(e){
    e.preventDefault();

    let _this = $(this);
    let i_tag = _this.find('i');
    let url = _this.data('href');

    if(_this.data('child_exist') == 'no'){
        i_tag.removeClass().html('<img src="{{ asset(config("constant.default.page_loader")) }}" width="18" />');
        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            data: { _token: "{{ csrf_token() }}" },
            success: function (response) {
                if(response.success) {
                    if(response.specialty_count > 0){
                        _this.closest('.specialty_inner').find('.child-specialty-main').html(response.viewHTML);

                        i_tag.removeClass('ri-arrow-down-s-line arrow_down_icon');
                        i_tag.addClass('ri-arrow-up-s-line arrow_up_icon').html('');

                        _this.data('child_exist', 'yes');
                    }
                }
                else {
                    toasterAlert('error',response.error);
                    i_tag.removeAttr('style')
                         .removeClass()
                         .addClass('ri-arrow-down-s-line arrow_down_icon').html('');
                }
            },
            error: function(res){
                toasterAlert('error',res.responseJSON.error);
                i_tag.removeAttr('style')
                     .removeClass()
                     .addClass('ri-arrow-down-s-line arrow_down_icon').html('');
            },
            complete: function(xhr){
                
            }
        });
    } else {
        if(i_tag.hasClass('arrow_down_icon')){
            i_tag.removeClass('ri-arrow-down-s-line arrow_down_icon');
            i_tag.addClass('ri-arrow-up-s-line arrow_up_icon');
            
            _this.closest('.specialty_inner').find('.child-specialty-main').removeClass('d-none');
        } else {
            i_tag.removeClass('ri-arrow-up-s-line arrow_up_icon');
            i_tag.addClass('ri-arrow-down-s-line arrow_down_icon');

            _this.closest('.specialty_inner').find('.child-specialty-main').addClass('d-none');
        }
    }
});

let page = 1;
let loading = false;

$(window).scroll(function() {
    if(loading) return;
    
    if($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
        loading = true;
        page++;

        $('#loading').show();

        $.ajax({
            url: '?page=' + page,
            type: 'get',
            success: function(data) {
                if(data.trim().length === 0) {
                    $('#loading').html('No more records').show();
                    setTimeout(function() {
                        $('#loading').fadeOut();
                    }, 2000); // hide after 2 seconds (2000 ms)
                    
                    return;
                }
                $('.specialty_list').append(data);
                $('#loading').hide();
                loading = false;
            },
            error: function() {
                $('#loading').hide();
                loading = false;
            }
        });
    }
});
</script>

<script>
$(document).ready(function () {
    $('#specialtySearch').on('keyup', function () {
        var value = $(this).val().toLowerCase().trim();
        var anyVisible = false;

        $('.specialty_main .specialty_inner').each(function () {
            var text = $(this).find('.specialty_text').text().toLowerCase();

            if (text.indexOf(value) > -1) {
                $(this).show();
                anyVisible = true;
            } else {
                $(this).hide();
            }
        });

        // Handle "No Match Found" message
        if (!anyVisible) {
            if ($('.no-matches-found').length === 0) {
                $('.specialty_main').append(`
                    <div class="row specialty_main_row no-matches-found">
                        <div class="col-md-12 specialty__row text-center">
                            No record found.
                        </div>
                    </div>
                `);
            } else {
                $('.no-matches-found').show();
            }
        } else {
            $('.no-matches-found').hide();
        }
    });
});

$(document).on('click', '#RemoveSpecialtyIconBtn', function(e){
    var specialtyId = $(this).data('specialty_id');
    Swal.fire({
        title: "{{ trans('global.areYouSure') }}",
        text: "{{ trans('messages.crud.onceClickedRecordDeleted') }}",
        icon: "warning",
        showDenyButton: true,  
        //   showCancelButton: true,  
        confirmButtonText: "{{ trans('global.swl_confirm_button_text') }}",  
        denyButtonText: "{{ trans('global.swl_deny_button_text') }}",
    })
    .then(function(result) {
        if (result.isConfirmed) {  
            $('.loader-div').show();
            $.ajax({
                type: 'post',
                url: "{{ route('remove.specialty-icon') }}",
                dataType: 'json',
                data: {  _token: "{{ csrf_token() }}", 'specialtyId':specialtyId },
                success: function (response) {
                    if(response.success) {
                        $('.img-prevarea img').attr('src', response.specialty_icon);
                        if(response.specialty_icon){
                            $('.specialty-img').attr('src', response.specialty_icon);
                        }
                        toasterAlert('success',response.message);
                        $('.remove-specialty-icon-main').html('');
                        $('.img-prevarea').removeClass('active');
                        // $('#editSpecialty').modal('hide');
                    }
                    else {
                        toasterAlert('error',response.error);
                    }
                },
                error: function(res){
                    toasterAlert('error',res.responseJSON.error);
                },
                complete: function(xhr){
                    $('.loader-div').hide();
                }
            });
        }
    });
});

$(document).on('change', ".fileInputBoth",function(e){
        var files = e.target.files;
        for (var i = 0; i < files.length; i++) {
            var reader2 = new FileReader();
            reader2.onload = function(e) {
                $('.img-prevarea img').attr('src', e.target.result);
            };
            reader2.readAsDataURL(files[i]);
        }
});

$(document).ready(function() {
    $('#importForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('import.csv') }}",
            type: "POST",
            data: formData,
            processData: false, // prevent jQuery from automatically transforming data
            contentType: false,
            beforeSend: function() {
                // optional: show loader or disable button
                $('button[type=submit]').prop('disabled', true).text('Importing...');
            },
            success: function(response) {
                toasterAlert('success',response.message);

                setTimeout(function() {                  
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr) {
                if (xhr.responseJSON?.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat().join("\n");
                    toasterAlert('error',errors);
                } else {
                    toasterAlert('error','Something went wrong!');
                }
            },
            complete: function() {
                $('button[type=submit]').prop('disabled', false).text('Import');
                $('#csv_file').val('');
            }
        });
    });
});
</script>