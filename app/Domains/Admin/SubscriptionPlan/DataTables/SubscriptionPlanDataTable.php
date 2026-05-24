<?php

namespace App\Domains\Admin\SubscriptionPlan\DataTables;

use App\Domains\Core\Subscription\Models\Plan;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class SubscriptionPlanDataTable extends DataTable
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

            ->editColumn('name_en', function($record){
                return $record->name_en ? ucwords($record->name_en) : '';
            })
            ->editColumn('monthly_price', function($record){
                return $record->monthly_price ? $record->monthly_price : '';
            })
            ->editColumn('yearly_price', function($record){
                return $record->yearly_price ? $record->yearly_price : '';
            })

            ->editColumn('is_active', function($record){
                $checkedStatus = '';
                if($record->is_active == '1'){
                    $checkedStatus = 'checked';
                }
                return '<div class="checkbox switch">
                    <label>
                        <input type="checkbox" class="switch-control subscription_plan_status_cb" '.$checkedStatus.' data-subscription_plan_id="'.($record->id).'" />
                        <span class="switch-label"></span>
                    </label>
                </div>';
            })

            ->addColumn('action', function($record){
                $actionHtml = '';

                if (Gate::check('plan_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('subscription-plans.show',$record->id).'" class="btn btn-outline-info btn-sm btnViewSubscriptionPlan" title="Show"> <i class="ri-eye-line"></i> </a>';
                }

                if (Gate::check('plan_edit')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('subscription-plans.edit',$record->id).'" class="btn btn-outline-success btn-sm btnEditSubscriptionPlan" title="Edit"> <i class="ri-edit-2-line"></i> </a>';
                }
                
              
                return $actionHtml;
            })
            ->setRowId('id')
            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
            ->rawColumns(['action','is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Plan $model): QueryBuilder
    {
        return $model->newQuery()
        ->select('plans.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $orderByColumn = 5;       
        return $this->builder()
                    ->setTableId('subscription-plan-table')
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
                            var columnCount = $("#subscription-plan-table").find("th").length;
                            // Store state globally
                            $("#subscription-plan-table").data("has_data", hasData);
                            $("#subscription-plan-table").attr("data-has_data", hasData);
        
                            // If there are no records, disable Responsive child rows
                            if (!hasData) {
                                setTimeout(function(){
                                    $("#subscription-plan-table").find("th, td").css("display", "table-cell");
                                    $("#subscription-plan-table").find(".dt-empty").attr("colspan", columnCount);
                                }, 500);
                            }
        
                            $(window).on("resize", function () {
                                var hasData = $("#subscription-plan-table").data("has_data");
        
                                if (!hasData) {
                                    // Ensure all columns remain visible on resize if no data
                                    setTimeout(function(){
                                        $("#subscription-plan-table").find("th, td").css("display", "table-cell");
                                        $("#subscription-plan-table").find(".dt-empty").attr("colspan", columnCount);
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
      
        $columns[] = Column::make('name_en')->title(trans('cruds.subscription_plan.fields.name_en'));
        $columns[] = Column::make('monthly_price')->title(trans('cruds.subscription_plan.fields.monthly_price'));
        $columns[] = Column::make('yearly_price')->title(trans('cruds.subscription_plan.fields.yearly_price'));
        $columns[] = Column::make('is_active')->title(trans('cruds.subscription_plan.fields.status'));
        $columns[] = Column::make('created_at')->title(trans('cruds.subscription_plan.fields.created_at'))->addClass('dt-created_at');
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
