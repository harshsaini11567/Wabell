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

class CustomerDataTable extends DataTable
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
            ->editColumn('phone', function($record){
                return $record->phone ? ucwords($record->phone) : '';
            })
            ->editColumn('email', function($record){
                return $record->email ? ucwords($record->email) : '';
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
            ->editColumn('user_status', function($record){
                if (!Gate::check('customer_status')) {
                     return $record->user_status ? ucwords($record->user_status) : '-';
                }
                $checkedStatus = '';
                if($record->user_status == 'active'){
                    $checkedStatus = 'checked';
                }
                return '<div class="checkbox switch">
                    <label>
                        <input type="checkbox" class="switch-control customer_status_cb" '.$checkedStatus.' data-customer_id="'.($record->uuid).'" />
                        <span class="switch-label"></span>
                    </label>
                </div>';
            })
            ->editColumn('last_access_date_time', function($record){
                return $record->last_access_date_time->format(config('constant.date_format.date_time'));
            })
            
            ->addColumn('action', function($record){
                $actionHtml = '';
                    if (Gate::check('customer_edit')) {
                        $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('admins.change-password',$record->uuid).'" class="btn btn-outline-dark btn-sm btnChangePassword" title="Change Password"> <i class="fa fa-key"></i> </a>';
                    }
                    if (Gate::check('customer_show')) {
                        $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('customers.show',$record->uuid).'" class="btn btn-outline-info btn-sm btnViewCustomer" title="Show"> <i class="ri-eye-line"></i> </a>';
                    }
                    if (Gate::check('customer_edit')) {
                        $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('customers.edit',$record->uuid).'" class="btn btn-outline-success btn-sm btnEditCustomer" title="Edit"> <i class="ri-edit-2-line"></i> </a>';
                    }
                    if (Gate::check('customer_ban')) {
                        $isBan = $record->is_ban === 1;
                        $btnClass = $isBan ? 'danger' : 'success';
                        $icon = $isBan ? 'lock' : 'prohibited';
                        $title = $isBan ? 'Unban' : 'Ban';
                        $actionHtml .= '<a href="javascript:void(0);" data-is_ban="'.$isBan.'" data-customer_id="'.$record->uuid.'" class="btn btn-outline-'. $btnClass .' btn-sm btnEditAdmin customer_isban" title="'.$title.'"> <i class="ri-'.$icon.'-line"></i> </a>';
                    }
                    if (Gate::check('customer_delete')) {
                        $actionHtml .= '<a href="javascript:void(0);" class="btn btn-outline-danger btn-sm deleteCustomerBtn" data-href="'.route('customers.destroy', $record->uuid).'" title="Delete"><i class="ri-delete-bin-line"></i></a>';
                    }
                    return $actionHtml;
            })
            ->setRowId('id')
            ->filterColumn('last_access_date_time', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(last_access_date_time,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
            ->filterColumn('user_status', function ($query, $keyword) {
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
        $rolesConstant = config('constant.roles'); 
        $customerRoleId = $rolesConstant['customer'] ?? null;

        return $model->whereHas('roles', function($q) use ($customerRoleId) {
            $q->where('role_type', 'app')->where('id',$customerRoleId);
        })->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $orderByColumn = 6;        
        $pagination = PaginationSettings('user_pagination');
        return $this->builder()
                    ->setTableId('customer-table')
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
                            var columnCount = $("#customer-table").find("th").length;

                            // Store state globally
                            $("#customer-table").data("has_data", hasData);
                            $("#customer-table").attr("data-has_data", hasData);
        
                            // If there are no records, disable Responsive child rows
                            if (!hasData) {
                                setTimeout(function(){
                                    $("#customer-table").find("th, td").css("display", "table-cell");
                                    $("#customer-table").find(".dt-empty").attr("colspan", columnCount);
                                }, 500);
                            }
        
                            $(window).on("resize", function () {
                                var hasData = $("#customer-table").data("has_data");
        
                                if (!hasData) {
                                    setTimeout(function(){
                                        $("#customer-table").find("th, td").css("display", "table-cell");
                                        $("#customer-table").find(".dt-empty").attr("colspan", columnCount);
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
        $columns[] = Column::make('phone')->title(trans('cruds.admin.fields.phone'));
        $columns[] = Column::make('email')->title(trans('cruds.admin.fields.email'));
        $columns[] = Column::make('user_status')->title(trans('cruds.admin.fields.user_status'));
        $columns[] = Column::make('last_access_date_time')->title(trans('cruds.master.fields.last_access_date_time'));
        $columns[] = Column::make('created_at')->title(trans('cruds.admin.fields.created_at'))->addClass('dt-created_at');
       
        $columns[] = Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(250)->addClass('text-center action-col');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'customers_' . date('YmdHis');
    }
}
