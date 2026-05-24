<?php

namespace App\Domains\Admin\City\DataTables;

use App\Domains\Core\City\Models\Neighborhood;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class NeighborhoodDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public $city;

    public function setCity($city)
    {
        $this->city = $city;
        // dd($this->city);
        return $this;
    }
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $city = $this->city;
        return (new EloquentDataTable($query->select('neighborhoods.*')->where('city_id', $this->city->id)))
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
            // ->editColumn('created_by', function($record){
            //     return $record->created_by ? ucwords($record->created_by) : '';
            // })
            // ->editColumn('status', function($record) {
            //     $status = $record->status;
            //     $statusText = $status ? config('constant.neighborhood_status.' . $status, '') : '';
            //     if($statusText){
            //         $colorClass = match($status) {
            //             'inactive'    => 'badge bg-danger',
            //             'active'  => 'badge bg-success',
            //         };
            //         return '<span class="' . $colorClass . '">' . $statusText . '</span>';
            //     } else {
            //         return '';
            //     }
            // })

            ->addColumn('action', function($record) use ($city){
                $actionHtml = '';
                if (Gate::check('city_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('cities.neighborhoods.show',['city' => $city->uuid, 'neighborhood' => $record->uuid]).'" class="btn btn-outline-info btn-sm btnViewNeighborhood" title="Show"> <i class="ri-eye-line"></i> </a>';
                }

                if (Gate::check('city_edit')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('cities.neighborhoods.edit',['city' => $city->uuid, $record->uuid]).'" class="btn btn-outline-success btn-sm btnEditNeighborhood" title="Edit"> <i class="ri-edit-2-line"></i> </a>';
                }
                
                if (Gate::check('city_delete')) {
                    $actionHtml .= '<a href="javascript:void(0);" class="btn btn-outline-danger btn-sm deleteNeighborhoodBtn" data-href="'.route('cities.neighborhoods.destroy', ['city' => $city->uuid,'neighborhood' => $record->uuid]).'" title="Delete"><i class="ri-delete-bin-line"></i></a>';
                }
                return $actionHtml;
            })
            ->setRowId('id')
            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Neighborhood $model): QueryBuilder
    {         
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $orderByColumn = 3;       
        return $this->builder()
                    ->setTableId('neighborhood-table')
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
                            var columnCount = $("#neighborhood-table").find("th").length;
                            // Store state globally
                            $("#neighborhood-table").data("has_data", hasData);
                            $("#neighborhood-table").attr("data-has_data", hasData);
        
                            // If there are no records, disable Responsive child rows
                            if (!hasData) {
                                setTimeout(function(){
                                    $("#neighborhood-table").find("th, td").css("display", "table-cell");
                                    $("#neighborhood-table").find(".dt-empty").attr("colspan", columnCount);
                                }, 500);
                            }
        
                            $(window).on("resize", function () {
                                var hasData = $("#neighborhood-table").data("has_data");
        
                                if (!hasData) {
                                    // Ensure all columns remain visible on resize if no data
                                    setTimeout(function(){
                                        $("#neighborhood-table").find("th, td").css("display", "table-cell");
                                        $("#neighborhood-table").find(".dt-empty").attr("colspan", columnCount);
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
      
        $columns[] = Column::make('name_en')->title(trans('cruds.neighborhood.fields.name_en'));
        $columns[] = Column::make('name_ar')->title(trans('cruds.neighborhood.fields.name_ar'));
        // $columns[] = Column::make('created_by')->title(trans('cruds.neighborhood.fields.created_by'));
        // $columns[] = Column::make('status')->title(trans('cruds.neighborhood.fields.neighborhood_status'));
        $columns[] = Column::make('created_at')->title(trans('cruds.neighborhood.fields.created_at'))->addClass('dt-created_at');
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
