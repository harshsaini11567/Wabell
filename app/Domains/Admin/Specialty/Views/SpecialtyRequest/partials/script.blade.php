<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>

<script src="{{asset('admin-assets/vendor/select2/js/select2.min.js')}}"></script>
<script src="{{asset('admin-assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>

{!! $dataTable->scripts() !!}

<script>
    @can('specialties_request_status')
        $(document).on('change','.special_request_dropdown', function(){
            let $this = $(this);
            let userId = $this.data('special_request_id');
            let newStatus = $this.val();
            let oldStatus = $this.data('old_status');
            let csrf_token = $('meta[name="csrf-token"]').attr('content');

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
                        url: "{{ route('specialty.request.status') }}",
                        dataType: 'json',
                        data: { _token: csrf_token, id: userId , status: newStatus},
                        success: function (response) {
                            if(response.status == 'true') {
                                $('#specialty-request-table').DataTable().ajax.reload(null, false);
                                toasterAlert('success',response.message);
                                let $countBadge = $(".sidebar_pending_specialty_request_count");
                                if(response.data.pending_specialty_request_count > 0){
                                    if($countBadge.length){
                                        $countBadge.text(response.data.pending_specialty_request_count);
                                    } else {
                                        $(".side-nav-link").append(
                                            '<span class="pending_circle sidebar_pending_specialty_request_count">'+response.data.pending_specialty_request_count+'</span>'
                                        );
                                    }
                                } else {
                                    $countBadge.remove();
                                }
                                // $this.data('old_status', newStatus);
                            }
                        },
                        error:function (response){
                            toasterAlert('error',response.error);
                            $this.val(oldStatus);
                        },
                        complete: function(xhr){
                            pageLoader('hide');
                        }
                    });
                }
                else {
                    $this.val(oldStatus);
                }
            });
        });
    @endcan


</script>