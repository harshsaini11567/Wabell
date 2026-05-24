<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>

{!! $dataTable->scripts() !!}

<script>
    @can('transaction_show')
        $(document).on("click", ".btnViewTransaction", function() {
            pageLoader('show');
            var url = $(this).data('href');

            $.ajax({
                type: 'get',
                url: url,
                dataType: 'json',
                success: function (response) {
                    if(response.success) {
                        $('.popup_render_div').html(response.htmlView);
                        $('#ViewTransactionPlan').modal('show');
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
</script>
