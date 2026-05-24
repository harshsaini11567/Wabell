<?php

namespace App\Domains\Admin\SubscriptionPlan\DataTables;

use App\Domains\Core\Subscription\Models\Transactions;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Support\Facades\App;

class TransactionDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('created_at', function($record) {
                return $record->created_at->format(config('constant.date_format.date_time'));
            })

            // ->editColumn('user.name', function($record){
            //     optional($record->user)->name ? ucwords($record->user->name) : '';
            // })
            ->editColumn('user.name', fn($record) => ucwords(optional($record->user)->name ?? ''))
            ->editColumn('subscription.plan.name_en', function($record){
                $plan = optional($record->subscription->plan);
                return $plan ? $plan->{"name_" . app()->getLocale()} : 'Basic';
            })
            ->editColumn('amount', function($record){
                return $record->amount ? $record->amount : '';
            })

            ->addColumn('action', function($record){
                $actionHtml = '';

                if (Gate::check('transaction_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('transactions.show',$record->id).'" class="btn btn-outline-info btn-sm btnViewTransaction" title="Show"> <i class="ri-eye-line"></i> </a>';
                }             
              
                return $actionHtml;
            })
            ->setRowId('id')
            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
            ->filterColumn('user.name', function($query, $keyword) {
                $query->whereHas('user', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('subscription.plan.name_en', function($query, $keyword) {
                $query->whereHas('subscription.plan', function($q) use ($keyword) {
                    $q->where('name_en', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Transactions $model): QueryBuilder
    {
        return $model->newQuery()
        ->with(['user', 'subscription.plan']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $orderByColumn = 4;       
        return $this->builder()
                    ->setTableId('transaction-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    // ->dom('Bfrtip')
                    ->orderBy($orderByColumn)                    
                    ->selectStyleSingle()
                    ->lengthMenu([
                            [10, 25, 50, 100, /*-1*/],
                            [10, 25, 50, 100, /*'All'*/]
                        ])
                    ->parameters([
                        // 'pageLength' => $pagination['pageLength'],
                        'responsive' => true, // keep responsive enabled
                        'pagingType' => 'simple_numbers',
                        'language' => [
                            'emptyTable' => 'No records available',
                        ],
                        'drawCallback' => 'function(settings) {
                            var api = this.api();
                            var data = api.rows({ page: "current" }).data();
        
                            var hasData = data.length > 0;
                            var columnCount = $("#transaction-table").find("th").length;
                            // Store state globally
                            $("#transaction-table").data("has_data", hasData);
                            $("#transaction-table").attr("data-has_data", hasData);
        
                            // If there are no records, disable Responsive child rows
                            if (!hasData) {
                                setTimeout(function(){
                                    $("#transaction-table").find("th, td").css("display", "table-cell");
                                    $("#transaction-table").find(".dt-empty").attr("colspan", columnCount);
                                }, 500);
                            }
        
                            $(window).on("resize", function () {
                                var hasData = $("#transaction-table").data("has_data");
        
                                if (!hasData) {
                                    // Ensure all columns remain visible on resize if no data
                                    setTimeout(function(){
                                        $("#transaction-table").find("th, td").css("display", "table-cell");
                                        $("#transaction-table").find(".dt-empty").attr("colspan", columnCount);
                                    }, 500);
                                }
                            });
        
                            var rows = data.length;
                            var pageLength = api.page.len();
                            var recordsTotal = api.page.info().recordsTotal;
                            if (recordsTotal > pageLength) {
                                $(this).closest(".dt-container").find(".dt-paging.paging_simple_numbers").show();
                            } else {
                                $(this).closest(".dt-container").find(".dt-paging.paging_simple_numbers").hide();
                            }
                        }'
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [];

        $columns[] = Column::make('DT_RowIndex')->title(trans('global.sno'))->orderable(false)->searchable(false)->addClass('dt-sno');
      
        $columns[] = Column::make('user.name')->title(trans('cruds.transaction.fields.user_id'))->searchable(true);
        $columns[] = Column::make('subscription.plan.name_en')->title(trans('cruds.transaction.fields.plan_id'))->searchable(true);
        $columns[] = Column::make('amount')->title(trans('cruds.transaction.fields.amount'))->searchable(true);
        $columns[] = Column::make('created_at')->title(trans('cruds.transaction.fields.created_at'))->addClass('dt-created_at');
        $columns[] = Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(200)->addClass('text-center action-col');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'SubscriptionPlans' . date('YmdHis');
    }
}
