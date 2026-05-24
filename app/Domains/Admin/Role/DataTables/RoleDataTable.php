<?php

namespace App\Domains\Admin\Role\DataTables;

use App\Domains\Core\Role\Models\Role;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class RoleDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('created_at', function($record) {
                return $record->created_at->format(config('constant.date_format.date_time'));
            })
            ->editColumn('name_en', function($record){
                return $record->name_en ? ucwords($record->name_en) : '-';
            })
            ->editColumn('name_ar', function($record){
                return $record->name_ar ? ucwords($record->name_ar) : '-';
            })
            ->editColumn('description_en', function($record){
                return $record->description_en ? ucwords($record->description_en) : '-';
            })
            ->editColumn('description_ar', function($record){
                return $record->description_ar ? ucwords($record->description_ar) : '-';
            })
            ->editColumn('role_status', function ($record) {
                $status = $record->role_status;
                $statusText = $status ? config('constant.status.' . $status, '') : '';
                if ($statusText) {
                    $colorClass = match ($status) {
                        'active' => 'badge bg-success',
                        'inactive' => 'badge bg-danger',
                        default => 'badge bg-secondary',
                    };
                    return "<span class=\"{$colorClass}\">{$statusText}</span>";
                }
                return '-';
            })
            ->addColumn('action', function ($record) {
                $actionHtml = '';
                // $user = auth()->user();

                // View button for all users who have permission
                if (Gate::check('role_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="' . route('roles.show', $record->uuid) . '" class="btn btn-outline-info btn-sm btnViewRole" title="Show"><i class="ri-eye-line"></i></a> ';
                }

                if ($record) {
                    if ($record->role_type === 'admin') {
                        // admin user: edit + delete buttons (if permitted)
                        if (Gate::check('role_edit')) {
                            $actionHtml .= '<a href="javascript:void(0);" data-href="' . route('roles.edit', $record->uuid) . '" class="btn btn-outline-success btn-sm btnEditRole" title="Edit"><i class="ri-edit-2-line"></i></a> ';
                        }
                        if (Gate::check('role_delete')) {
                            $actionHtml .= '<a href="javascript:void(0);" data-href="' . route('roles.destroy', $record->uuid) . '" class="btn btn-outline-danger btn-sm deleteRoleBtn" title="Delete"><i class="ri-delete-bin-line"></i></a>';
                        }
                    }
                    // super_admin user: NO edit/delete buttons, only view (already added)
                }

                return $actionHtml;
            })
            ->setRowId('id')
            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
            ->rawColumns(['action', 'role_status']);
    }

    public function query(Role $model): QueryBuilder
    {
        // Show only roles with role_type super_admin or admin
        return $model->newQuery()
            ->whereIn('role_type', ['super_admin', 'admin']);
    }

    public function html(): HtmlBuilder
    {
        $orderByColumn = 6;   
        return $this->builder()
            ->setTableId('role-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy($orderByColumn,'desc') // Order by name_en by default
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
                    var columnCount = $("#role-table").find("th").length;
                    // Store state globally
                    $("#role-table").data("has_data", hasData);
                    $("#role-table").attr("data-has_data", hasData);
 
                    // If there are no records, disable Responsive child rows
                    if (!hasData) {
                       setTimeout(function(){
                                $("#role-table").find("th, td").css("display", "table-cell");
                                $("#role-table").find(".dt-empty").attr("colspan", columnCount);
                            }, 500);
                    }
 
                    $(window).on("resize", function () {
                        var hasData = $("#role-table").data("has_data");
 
                        if (!hasData) {
                            // Ensure all columns remain visible on resize if no data
                           setTimeout(function(){
                                $("#role-table").find("th, td").css("display", "table-cell");
                                $("#role-table").find(".dt-empty").attr("colspan", columnCount);
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

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')
                ->title(trans('global.sno'))
                ->orderable(false)
                ->searchable(false)
                ->width(30)
                ->addClass('dt-sno'),
            Column::make('name_en')
                ->title(trans('cruds.role.fields.name_en')),
            Column::make('name_ar')
                ->title(trans('cruds.role.fields.name_ar')),
            Column::make('description_en')
                ->title(trans('cruds.role.fields.description_en')),
            Column::make('description_ar')
                ->title(trans('cruds.role.fields.description_ar')),
            Column::make('role_status')
                ->title(trans('cruds.role.fields.status')),
            Column::make('created_at')
                ->title(trans('cruds.role.fields.created_at')),
            Column::computed('action')
                ->orderable(false)
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center action-col'),
        ];
    }

    protected function filename(): string
    {
        return 'Roles' . date('YmdHis');
    }
}
