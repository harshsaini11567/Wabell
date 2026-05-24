<?php

namespace App\Domains\Admin\User\DataTables;

use App\Domains\Core\User\Models\User;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Support\Str;

class AdminDataTable extends DataTable
{
    public $customPageLength = 10;
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query->select('users.*')))
            ->addIndexColumn()

            ->editColumn('created_at', function($record) {
                return $record->created_at->format(config('constant.date_format.date_time'));
            })

            ->editColumn('name', function($record){
                return $record->name ? ucwords($record->name) : '';
            })


            ->editColumn('user_status', function($record){
                $checkedStatus = '';
                if($record->user_status == 'active'){
                    $checkedStatus = 'checked';
                }
                return '<div class="checkbox switch">
                    <label>
                        <input type="checkbox" class="switch-control admin_status_cb" '.$checkedStatus.' data-admin_id="'.($record->uuid).'" />
                        <span class="switch-label"></span>
                    </label>
                </div>';
            })
            
            ->addColumn('action', function($record){
                $actionHtml = '';

                // if (Gate::check('admin_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('admins.change-password',$record->uuid).'" class="btn btn-outline-dark btn-sm btnChangePassword" title="Change Password"> <i class="fa fa-key"></i> </a>';
                // }
                if (Gate::check('admin_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('admins.show',$record->uuid).'" class="btn btn-outline-info btn-sm btnViewAdmin" title="Show"> <i class="ri-eye-line"></i> </a>';
                }

                if (Gate::check('admin_edit')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('admins.edit',$record->uuid).'" class="btn btn-outline-success btn-sm btnEditAdmin" title="Edit"> <i class="ri-edit-2-line"></i> </a>';
                }
                
                if (Gate::check('admin_delete')) {
                    $actionHtml .= '<a href="javascript:void(0);" class="btn btn-outline-danger btn-sm deleteAdminBtn" data-href="'.route('admins.destroy', $record->uuid).'" title="Delete"><i class="ri-delete-bin-line"></i></a>';
                }
                return $actionHtml;
            })
            ->setRowId('id')

            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
            ->filterColumn('status', function ($query, $keyword) {
                $statusSearch  = null;
                if (Str::contains('active', strtolower($keyword))) {
                        $statusSearch = 'active';
                } else if (Str::contains('inactive', strtolower($keyword))) {
                        $statusSearch = 'inactive';
                }
                $query->where('user_status', $statusSearch); 
            })
            ->rawColumns(['action', 'user_status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {         
        return $model->whereHas('roles', function($q) {
            $q->whereNotIn('role_type',  ['app','super_admin']);
        })->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $orderByColumn = 4;        
        $pagination = PaginationSettings('user_pagination');
        return $this->builder()
                    ->setTableId('admin-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy($orderByColumn)                    
                    ->selectStyleSingle()
                    ->lengthMenu($pagination['lengthMenu'])
                    ->parameters([
                        'pageLength' => $pagination['pageLength'],
                        'responsive' => true, // keep responsive enabled
                        'pagingType' => 'simple_numbers',
                        'language' => [
                            'emptyTable' => 'No records available',
                        ],
                        'drawCallback' => 'function(settings) {
                            var api = this.api();
                            var data = api.rows({ page: "current" }).data();
        
                            var hasData = data.length > 0;
                            var columnCount = $("#admin-table").find("th").length;
                            // Store state globally
                            $("#admin-table").data("has_data", hasData);
                            $("#admin-table").attr("data-has_data", hasData);
        
                            // If there are no records, disable Responsive child rows
                            if (!hasData) {
                                setTimeout(function(){
                                    $("#admin-table").find("th, td").css("display", "table-cell");
                                    $("#admin-table").find(".dt-empty").attr("colspan", columnCount);
                                }, 500);
                            }
        
                            $(window).on("resize", function () {
                                var hasData = $("#admin-table").data("has_data");
        
                                if (!hasData) {
                                    // Ensure all columns remain visible on resize if no data
                                    setTimeout(function(){
                                        $("#admin-table").find("th, td").css("display", "table-cell");
                                        $("#admin-table").find(".dt-empty").attr("colspan", columnCount);
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
      
        $columns[] = Column::make('name')->title(trans('cruds.admin.fields.name'));
        $columns[] = Column::make('email')->title(trans('cruds.admin.fields.email'));
        $columns[] = Column::make('user_status')->title(trans('cruds.admin.fields.user_status'));
        $columns[] = Column::make('created_at')->title(trans('cruds.admin.fields.created_at'))->addClass('dt-created_at');
       
        $columns[] = Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(300)->addClass('text-center action-col');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'admins_' . date('YmdHis');
    }
}
