<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>
<script src="{{asset('admin-assets/vendor/tinymce/js/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('admin-assets/vendor/dropify/dropify.min.js')}}"></script>
<script src="{{asset('admin-assets/vendor/select2/js/select2.min.js')}}"></script>

{!! $dataTable->scripts() !!}

<script>
    $(document).on('shown.bs.modal', '.modal', function () {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('.modal'),
            dropdownPosition: 'below',
            selectOnClose: false
        });

        $('.tinymce-editor').each(function () {
            let id = $(this).attr('id');
            let dir = $(this).data('dir');
            if (tinymce.get(id)) {
                tinymce.get(id).remove();
            }
            let tinySettings = {
                selector: `#${id}`,
                height: 300,
                plugins: [
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
            };
            if ($(this).data('dir') === 'rtl') {
                tinySettings['directionality'] = 'rtl';
                tinySettings['content_style'] += ' body { direction: rtl; text-align: right; }';
            }
            tinymce.init(tinySettings);
        });

        $('.dropify').dropify();
        $('.dropify-errors-container').remove();
        $('.dropify-wrapper').find('.dropify-clear').hide();
    });

    @can('plan_show')
        $(document).on("click", ".btnViewSubscriptionPlan", function() {
            pageLoader('show');
            var url = $(this).data('href');

            $.ajax({
                type: 'get',
                url: url,
                dataType: 'json',
                success: function (response) {
                    if(response.success) {
                        $('.popup_render_div').html(response.htmlView);
                        $('#ViewSubscriptionPlan').modal('show');
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

    @can('plan_edit')
        $(document).on("click", ".btnEditSubscriptionPlan", function() {
            pageLoader('show');
            var url = $(this).data('href');

            $.ajax({
                type: 'get',
                url: url,
                dataType: 'json',
                success: function (response) {
                    if(response.success) {
                        $('.popup_render_div').html(response.htmlView);
                        $('#editSubscriptionPlan').modal('show');
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

        $(document).on('submit','#editSubscriptionPlanForm', function(e) {
            e.preventDefault();
            pageLoader('show', true);
            $('.validation-error-block').remove();
            var formData = new FormData(this);
            $('.tinymce-editor').each(function () {
                let id = $(this).attr('id');
                
                let content = tinymce.get(id).getContent({ format: 'html' }).trim();
                formData.append(id, content);
            });
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
                        $('#editSubscriptionPlan').modal('hide');
                        $('#subscription-plan-table').DataTable().ajax.reload(null, false);
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
                        var errorLabelTitle = '';
                        $.each(response.responseJSON.errors, function (key, item) {
                            console.log(key);
                            errorLabelTitle = '<span class="validation-error-block">'+item[0]+'</span>';
                            $("#"+key).siblings('.select2').after(errorLabelTitle);
                            $("input[name='" + key + "']").after(errorLabelTitle);
                            // $("textarea[name='" + key + "']").after(errorLabelTitle);
                            if(key == 'features_en' || key == 'features_ar' ){
                                $(`textarea[name='${key}']`).closest('.form-group').find('.tox-tinymce').after(errorLabelTitle);
                            }
                        });
                    }
                },
                complete: function(xhr){
                    pageLoader('hide', true);
                }
            });
        }); 

        $(document).on('click','.subscription_plan_status_cb', function(){
            let $this = $(this);
            let subscriptionPlanId = $this.data('subscription_plan_id');
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
                        url: "{{ route('subscriptions.status') }}",
                        dataType: 'json',
                        data: { _token: csrf_token, id: subscriptionPlanId },
                        success: function (response) {
                            if(response.status == 'true') {
                                toasterAlert('success',response.message);
                                $('#subscription-plan-table').DataTable().ajax.reload(null, false);
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
</script>
