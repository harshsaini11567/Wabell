<?php

namespace App\Domains\Admin\City\DataTables;

use App\Domains\Core\City\Models\City;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class CityDataTable extends DataTable
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
            ->editColumn('name_ar', function($record){
                return $record->name_ar ? ucwords($record->name_ar) : '';
            })
            ->editColumn('neighborhoods_count', function($record){
                return $record->neighborhoods_count ?? 0;
            })

            ->addColumn('action', function($record){
                $actionHtml = '';
                if (Gate::check('city_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('cities.show',$record->uuid).'" class="btn btn-outline-info btn-sm btnViewCity" title="Show"> <i class="ri-eye-line"></i> </a>';
                }

                if (Gate::check('city_edit')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('cities.edit',$record->uuid).'" class="btn btn-outline-success btn-sm btnEditCity" title="Edit"> <i class="ri-edit-2-line"></i> </a>';
                }
                
                if (Gate::check('city_delete')) {
                    $actionHtml .= '<a href="javascript:void(0);" class="btn btn-outline-danger btn-sm deleteCityBtn" data-href="'.route('cities.destroy', $record->uuid).'" title="Delete"><i class="ri-delete-bin-line"></i></a>';
                }
                if (Gate::check('city_show')) {
                    $actionHtml .= '<a href="'.route('cities.neighborhoods.index',$record->uuid).'" class="btn btn-outline-info btn-sm" title="Neighborhood Detail"> <i class="ri-file-line"></i> </a>';
                }
                return $actionHtml;
            })
            ->setRowId('id')
            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
            // ->filter(function ($query) {
            //     $keyword = request()->input('search.value');

            //     if (is_numeric($keyword)) {
            //         $query->havingRaw('neighborhoods_count = ?', [(int) $keyword]);
            //     }
            // })

            // ->filterColumn('neighborhoods_count', function ($query, $keyword) {
            //     if (is_numeric($keyword)) {
            //         $query->havingRaw('neighborhoods_count = ?', [(int) $keyword]);
            //     }
            // })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(City $model): QueryBuilder
    {         
        return $model->newQuery()->withCount('neighborhoods');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $orderByColumn = 4;       
        return $this->builder()
                    ->setTableId('city-table')
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
                            var columnCount = $("#city-table").find("th").length;
                            // Store state globally
                            $("#city-table").data("has_data", hasData);
                            $("#city-table").attr("data-has_data", hasData);
        
                            // If there are no records, disable Responsive child rows
                            if (!hasData) {
                                setTimeout(function(){
                                    $("#city-table").find("th, td").css("display", "table-cell");
                                    $("#city-table").find(".dt-empty").attr("colspan", columnCount);
                                }, 500);
                            }
        
                            $(window).on("resize", function () {
                                var hasData = $("#city-table").data("has_data");
        
                                if (!hasData) {
                                    // Ensure all columns remain visible on resize if no data
                                    setTimeout(function(){
                                        $("#city-table").find("th, td").css("display", "table-cell");
                                        $("#city-table").find(".dt-empty").attr("colspan", columnCount);
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
      
        $columns[] = Column::make('name_en')->title(trans('cruds.city.fields.name_en'));
        $columns[] = Column::make('name_ar')->title(trans('cruds.city.fields.name_ar'));
        $columns[] = Column::make('neighborhoods_count')->title(trans('cruds.city.fields.neighborhoods_count'))->searchable(false)->orderable(true);
        // $columns[] = Column::make('created_by')->title(trans('cruds.city.fields.created_by'));
        // $columns[] = Column::make('status')->title(trans('cruds.city.fields.city_status'));
        $columns[] = Column::make('created_at')->title(trans('cruds.city.fields.created_at'))->addClass('dt-created_at');
        $columns[] = Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(200)->addClass('text-center action-col');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Cities' . date('YmdHis');
    }
}
