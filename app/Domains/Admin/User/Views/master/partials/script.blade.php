<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>

<script src="{{asset('admin-assets/vendor/select2/js/select2.min.js')}}"></script>
<script src="{{asset('admin-assets/vendor/dropify/dropify.min.js')}}"></script>
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

{!! $dataTable->scripts() !!}

<script>
var certificateFilesDropzone, idFilesDropzone;
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

@can('master_edit')
    $(document).on("click", ".btnEditMaster", function() {
        pageLoader('show');
        let url = $(this).data('href');

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#editMaster').modal('show');

                    applyDropzone("#id_files", response.preloadedIdFiles);
                    applyDropzone("#certificate_files", response.preloadedCertificateFiles);
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

    $(document).on('submit','#editMasterForm', function(e) {
        e.preventDefault();
        pageLoader('show', true);

        $('.validation-error-block').remove();
        var formData = new FormData(this);
         if ($('#id_files').length){
            var fileVals =  idFilesDropzone.getAcceptedFiles();
            for (var i = 0; i < fileVals.length; i++) {
                formData.append('id_files[]', fileVals[i]);
            }
        }
        if ($('#certificate_files').length){
            var fileVals =  certificateFilesDropzone.getAcceptedFiles();
            for (var i = 0; i < fileVals.length; i++) {
                formData.append('certificate_files[]', fileVals[i]);
            }
        }
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
                    $('#editMaster').modal('hide');
                    $('#master-table').DataTable().ajax.reload(null, false);
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

@can('master_status')
    $(document).on('click','.master_status_cb', function(){
        let $this = $(this);
        let userId = $this.data('master_id');
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
                    url: "{{ route('masters.status') }}",
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
@endcan

@can('master_ban')
$(document).on('click','.master_isban', function(){
    let $this = $(this);
    let userId = $this.data('master_id');
    let isBanned = $this.data('is_ban');
    let action = isBanned ? "unban" : "ban";
    let confirmTemplate = "{{ __('global.ban_unban_confirm_text') }}";
    let confirmText = confirmTemplate
    .replace(':action', action)
    .replace(':user_type', 'Master');
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
                url: "{{ route('masters.isban') }}",
                dataType: 'json',
                data: { _token: csrf_token, id: userId },
                success: function (response) {
                    if(response.status == 'true') {
                        toasterAlert('success',response.message);
                        $('#master-table').DataTable().ajax.reload(null, false);
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

@can('master_is_approve')
$(document).on('click','.master_is_approved', function(){
    let $this = $(this);
    let userId = $this.data('master_id');
    let isApproved = $this.data('status');
    let isBanned = $this.data('is_ban');
    let action = isBanned ? "decline" : "approve";
    let confirmTemplate = "{{ __('global.ban_unban_confirm_text') }}";
    let confirmText = confirmTemplate
    .replace(':action', action)
    .replace(':user_type', 'Master');
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
                url: "{{ route('masters.isapproved') }}",
                dataType: 'json',
                data: { _token: csrf_token, id: userId , isApproved: isApproved},
                success: function (response) {
                    if(response.status == 'true') {
                        toasterAlert('success',response.message);
                        $('#master-table').DataTable().ajax.reload(null, false);
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


@can('master_show')
    $(document).on("click", ".btnViewMaster", function() {
        let url = $(this).data('href');
        pageLoader();

        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function (response) {
                if(response.success) {
                    $('.popup_render_div').html(response.htmlView);
                    $('#ViewMaster').modal('show');
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

@can('master_delete')
$(document).on("click",".deleteMasterBtn", function() {
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
                            $('#master-table').DataTable().ajax.reload(null, false);
                            toasterAlert('success',response.message);
                            let $countBadge = $(".sidebar_user_unverified_count");
                            if(response.data.unverified_count > 0){
                                if($countBadge.length){
                                    $countBadge.text(response.data.unverified_count);
                                } else {
                                    $(".side-nav-link").append(
                                        '<span class="pending_circle sidebar_user_unverified_count">'+response.data.unverified_count+'</span>'
                                    );
                                }
                            } else {
                                $countBadge.remove();
                            }
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

function applyDropzone(elmt_id, preloaded){
    /* dropzone start */ 
        Dropzone.autoDiscover = false;
        if(elmt_id == "#id_files"){
            idFilesDropzone = new Dropzone(elmt_id, {
                acceptedFiles: '.png,.jpg,.jpeg,.pdf',
                url: "{% url 'dropzone/images' %}",
                addRemoveLinks: true,
            });
            dropzoneFunc(idFilesDropzone, elmt_id, preloaded);
        } else {
            certificateFilesDropzone = new Dropzone(elmt_id, {
                acceptedFiles: '.png,.jpg,.jpeg,.pdf',
                url: "{% url 'dropzone/images' %}",
                addRemoveLinks: true,
            });
            dropzoneFunc(certificateFilesDropzone, elmt_id, preloaded);
        }
    /* dropzone end */
}

function dropzoneFunc(dropzone, elmt_id, preloaded){
    let excelFileExtensions = ['xls','xlsx', 'xml', 'csv', 'xlsm', 'xlw', 'xlr'];
    let wordFileExtensions = ['doc', 'docm', 'docx', 'dot'];
    let pdfFileExtensions = ['pdf'];
    let zipFileExtensions = ['zip','rar'];
    let filesExtensions = ['css', 'html', 'txt', 'php', 'sql', 'js'];
    
    dropzone.on('addedfile', function(file) {
       // $('.dz-message').css('display', 'none');
        $(elmt_id).find('.dz-message').css({'display': 'block','opacity': '0.6'});
        $(elmt_id).parent().addClass("preview_img_show");
        var countImage = $(elmt_id).find(".dz-complete").length; 
        if(countImage == -1){
            $('.dz-message').css('display', 'block');
        }
        var documentType = file.name.split('.').pop();
        if($.inArray(documentType, excelFileExtensions) != -1){
            $(file.previewElement).find(".dz-image img").attr("src", "{{ asset('default_images/excel.png') }}");
        }else if($.inArray(documentType, wordFileExtensions) != -1){
            $(file.previewElement).find(".dz-image img").attr("src", "{{ asset('default_images/word-icon.png') }}");
        }else if($.inArray(documentType, pdfFileExtensions) != -1){
            $(file.previewElement).find(".dz-image img").attr("src", "{{ asset('default_images/pdf-icon.png') }}");
        }else if($.inArray(documentType, zipFileExtensions) != -1){
            $(file.previewElement).find(".dz-image img").attr("src", "{{ asset('default_images/zip-icon.png') }}");
        }else if($.inArray(documentType, filesExtensions) != -1){
            $(file.previewElement).find(".dz-image img").attr("src", "{{ asset('default_images/file-icon.png') }}");
        } else {
            $(file.previewElement).find(".dz-image img").attr("src", "{{ asset('default_images/file-icon.png') }}");
        }
    });

    $.each(preloaded, function(key,value) {
        var mockFile = { name: value.fileName, size:value.size, path:value.src, id: value.id, type:value.documentType };
        dropzone.emit("addedfile", mockFile);
        if($.inArray(value.documentType, excelFileExtensions) != -1){
            dropzone.emit("thumbnail", mockFile, "{{ asset('default_images/excel.png') }}");
        }else if($.inArray(value.documentType, wordFileExtensions) != -1){
            dropzone.emit("thumbnail", mockFile, "{{ asset('default_images/word-icon.png') }}");
        }else if($.inArray(value.documentType, pdfFileExtensions) != -1){
            dropzone.emit("thumbnail", mockFile, "{{ asset('default_images/pdf-icon.png') }}");
        }else if($.inArray(value.documentType, zipFileExtensions) != -1){
            dropzone.emit("thumbnail", mockFile, "{{ asset('default_images/zip-icon.png') }}");
        }else if($.inArray(value.documentType, filesExtensions) != -1){
            dropzone.emit("thumbnail", mockFile, "{{ asset('default_images/file-icon.png') }}");
        }
        else{
            dropzone.emit("thumbnail", mockFile, value.src);
        }
        dropzone.emit("complete", mockFile);
    });

    dropzone.on("removedfile", function(file) {
        // $('.dz-message').css('display', 'block');
        $(elmt_id).find('.dz-message').css('display', 'block');
        var countImage = $(elmt_id).find(".dz-complete").length; 
        if(countImage > 0){
            // $('.dz-message').css('display', 'none');
            $(elmt_id).find('.dz-message').css({'display': 'block','opacity': '0.6'});
        }
        else{
            $(elmt_id).parent().removeClass("preview_img_show");
        }

        if(elmt_id == "#id_files"){
            var removeDocIds = $('#userIdFiles').val();
            if(removeDocIds && file.id) {
                var imageIds = removeDocIds+','+file.id;
                $("#userIdFiles").val(imageIds);
            }
            else if(file.id){
                $("#userIdFiles").val(file.id)
            }
        } else {
            var removeDocIds = $('#userCertificateFiles').val();
            if(removeDocIds && file.id) {
                var imageIds = removeDocIds+','+file.id;
                $("#userCertificateFiles").val(imageIds);
            }
            else if(file.id){
                $("#userCertificateFiles").val(file.id)
            }
        }
    });
}

$(document).ready(function () {
    let table = $('#master-table').DataTable();

    $('#added_required_fields_users').on('change', function () {
        let filterValue = $(this).val();
        table.ajax.url("{{ route('masters.index') }}?user_status=" + filterValue).load();
    });
});
</script>