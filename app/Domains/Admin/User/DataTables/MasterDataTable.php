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
use Illuminate\Support\Facades\App;
class MasterDataTable extends DataTable
{
    public $customPageLength = 10;
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query->select('users.*')->with(['masterDetail',
         'city'
         ])))
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
            ->editColumn('city.name_en', function($record){
                if ($record->city_id == 0) {
                    return trans('constant.other', [], app()->getLocale());
                }
                return $record->city ? ucwords($record->city->name_en) : '';
            })
            ->editColumn('subscription_id', function($record){
                // return $record->plan_name ?? 'Basic';
                 $subscription = $record->activeSubscription;
                if ($subscription && $subscription->plan) {
                    return $subscription->plan->{"name_" . app()->getLocale()};
                }
                return 'Basic';
            })
            ->editColumn('user_status', function($record){
                if (!Gate::check('master_status')) {
                     return $record->user_status ? ucwords($record->user_status) : '-';
                }
                if ($record->is_approved === 0) {
                    return '<span class="badge bg-danger">'. ucwords($record->user_status). '</span>';
                }
                else if ($record->is_approved === 1 ) {
                    $checkedStatus = $record->user_status == 'active' ? 'checked' : '';
                    return '<div class="checkbox switch">
                        <label>
                            <input type="checkbox" class="switch-control master_status_cb" '.$checkedStatus.' data-master_id="'.($record->uuid).'" />
                            <span class="switch-label"></span>
                        </label>
                    </div>';
                }
                else{
                    return $record->user_status ? '<span class="badge bg-info">' .ucwords($record->user_status). '</span>' : '-';
                }
            })

            // ->editColumn('is_approved', function($record){
            //     $is_approved = $record->is_approved;

            //     $approvedClass = $is_approved === 1 ? 'btn-success' : 'btn-outline-success';
            //     $declinedClass = $is_approved === 0 ? 'btn-danger' : 'btn-outline-danger';

            //     $statusText = $is_approved === 1 
            //         ? '<span class="badge bg-success">Approved</span>' 
            //         : ($is_approved === 0 
            //             ? '<span class="badge bg-danger">Declined</span>' 
            //             : '');

            //     $buttonsHtml = '';

            //     // Show buttons only if status is null (pending)
            //     if (is_null($is_approved)) {
            //         $buttonsHtml = '
            //             <div class="d-flex gap-1">
            //                 <button type="button" class="btn btn-sm ' . $approvedClass . ' master_is_approved" data-status="1" data-master_id="' . $record->uuid . '" title="Approve">
            //                     <i class="ri-check-line"></i>
            //                 </button>
            //                 <button type="button" class="btn btn-sm ' . $declinedClass . ' master_is_approved" data-status="0" data-master_id="' . $record->uuid . '" title="Decline">
            //                     <i class="ri-close-line"></i>
            //                 </button>
            //             </div>';
            //     }

            //     return $buttonsHtml . '<div>' . $statusText . '</div>';
            // })
            
            ->editColumn('last_access_date_time', function($record){
                return $record->last_access_date_time->format(config('constant.date_format.date_time'));
            })

            ->addColumn('action', function($record){
                $actionHtml = '';
                if($record->is_approved == 1){
                    if (Gate::check('master_edit')) {
                        $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('admins.change-password',$record->uuid).'" class="btn btn-outline-dark btn-sm btnChangePassword" title="Change Password"> <i class="fa fa-key"></i> </a>';
                    }
                }
                if (Gate::check('master_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('masters.show',$record->uuid).'" class="btn btn-outline-info btn-sm btnViewMaster" title="Show"> <i class="ri-eye-line"></i> </a>';
                }
                if($record->is_approved == 1){
                    if (Gate::check('master_edit')) {
                        $actionHtml .= '<a href="javascript:void(0);" data-href="'.route('masters.edit',$record->uuid).'" class="btn btn-outline-success btn-sm btnEditMaster" title="Edit"> <i class="ri-edit-2-line"></i> </a>';
                    }

                    if (Gate::check('master_ban')) {
                        $isban = $record->is_ban === 1;
                        $btnClass = $isban ? 'danger' : 'success';
                        $title = $isban ? 'Unban' : 'Ban';
                        $icon = $isban ? 'lock' : 'prohibited';
                        $actionHtml .= '<a href="javascript:void(0);" data-is_ban="'.$isban.'" data-master_id="'.$record->uuid.'" class="btn btn-outline-'. $btnClass .' btn-sm btnEditAdmin master_isban" title="'.$title.'"> <i class="ri-'.$icon.'-line"></i> </a>';
                    }

                    if (Gate::check('master_delete')) {
                        $actionHtml .= '<a href="javascript:void(0);" class="btn btn-outline-danger btn-sm deleteMasterBtn" data-href="'.route('masters.destroy', $record->uuid).'" title="Delete"><i class="ri-delete-bin-line"></i></a>';
                    }
                }
                return $actionHtml;
            })
            ->setRowId('id')

            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
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
            ->filterColumn('is_approved', function ($query, $keyword) {
                $approvedSearch  = '2';
                if (Str::contains('approved', strtolower($keyword))) {
                        $approvedSearch = 1;
                } else if (Str::contains('declined', strtolower($keyword))) {
                        $approvedSearch = 0;
                }
                $query->where('is_approved', $approvedSearch);
            })
            ->rawColumns(['action', 'user_status','is_approved']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {      
        $query = $model->whereHas('roles', function($q) {
            $q->where('role_type', 'app')->where('name_en','Master');
        })
        ->with(['activeSubscription.plan', 'uploads', 'specialties']);
        if (request()->has('user_status')) {
            $status = request('user_status');

            $query->where(function($q) use ($status) {
                if ($status === 'verified') {
                    $q->whereNotNull('date_of_birth')
                    ->whereHas('uploads', function($q2) {
                        $q2->where('type', 'certificate_file');
                    })
                    ->whereHas('specialties');
                } elseif ($status === 'unverified') {
                    $q->where(function($q2) {
                        $q2->whereNull('date_of_birth')
                        ->orWhereDoesntHave('uploads', function($q3) {
                            $q3->where('type', 'certificate_file');
                        })
                        ->orWhereDoesntHave('specialties');
                    });
                }
            });
        }
       return $query->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $orderByColumn = 8;        
        $pagination = PaginationSettings('user_pagination');
        return $this->builder()
                    ->setTableId('master-table')
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
                            var columnCount = $("#master-table").find("th").length;
                            // Store state globally
                            $("#master-table").data("has_data", hasData);
                            $("#master-table").attr("data-has_data", hasData);
        
                            // If there are no records, disable Responsive child rows
                            if (!hasData) {
                               setTimeout(function(){
                                    $("#master-table").find("th, td").css("display", "table-cell");
                                    $("#master-table").find(".dt-empty").attr("colspan", columnCount);
                                }, 500);
                            }
        
                            $(window).on("resize", function () {
                                var hasData = $("#master-table").data("has_data");
        
                                if (!hasData) {
                                    // Ensure all columns remain visible on resize if no data
                                    setTimeout(function(){
                                        $("#master-table").find("th, td").css("display", "table-cell");
                                        $("#master-table").find(".dt-empty").attr("colspan", columnCount);
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
      
        $columns[] = Column::make('name')->title(trans('cruds.master.fields.name'));
        $columns[] = Column::make('phone')->title(trans('cruds.master.fields.phone'));
        $columns[] = Column::make('email')->title(trans('cruds.master.fields.email'));
        $columns[] = Column::make('city.name_en')->title(trans('cruds.master.fields.city_id'));
        $columns[] = Column::make('subscription_id')->title(trans('cruds.master.fields.subscription_id'))->orderable(false)->searchable(false);
        $columns[] = Column::make('user_status')->title(trans('cruds.master.fields.user_status'));
        $columns[] = Column::make('last_access_date_time')->title(trans('cruds.master.fields.last_access_date_time'));
        // $columns[] = Column::make('is_approved')->title(trans('cruds.master.fields.is_approved'));
        $columns[] = Column::make('created_at')->title(trans('cruds.master.fields.created_at'))->addClass('dt-created_at');
       
        $columns[] = Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(300)->addClass('text-center action-col');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'masters_' . date('YmdHis');
    }
}
