<?php

namespace App\Domains\Admin\Specialty\DataTables;

use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use App\Domains\Core\Specialty\Models\SpecialtyRequest;
use App\Domains\Core\Role\Models\Role;

class SpecialtyRequestDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query->select('specialty_requests.*')))
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
            ->editColumn('user_role', function($record){
                $rolesConstant = config('constant.roles');
                if (! $record->user_role) {
                    return '';
                }
                $roleKey = $record->user_role;
                $roleId = $rolesConstant[$roleKey] ?? null;
                if (! $roleId) {
                    return ucwords(str_replace('_', ' ', $roleKey));
                }
                $role = Role::find($roleId);
                return $role ? $role->name_en : ucwords(str_replace('_', ' ', $roleKey));
                // return $record->user_role ? ucwords($record->user_role) : '';
            })
            ->editColumn('created_by', function($record){
                return $record->created_by ? ucwords($record->created_by) : '';
            })
            ->addColumn('action', function($record) {
                $actionHtml = '';

                $statusOptions = config('constant.specialties_request_status');
                $currentStatus = $record->status ?? '';
                if (in_array($currentStatus, ['accept', 'archive'])) {
                    // Show plain text if already accepted or declined
                    $actionHtml .= '<span class="badge ' . ($currentStatus === 'accept' ? 'bg-success' : 'bg-danger') . '">';
                    $actionHtml .= $statusOptions[$currentStatus] ?? ucfirst($currentStatus);
                    $actionHtml .= '</span>';
                }
                else{
                    $hasPermission = Gate::allows('specialties_request_status');

                    $disabled = $hasPermission ? '' : 'disabled';

                    $actionHtml .= '<div class="dropdown specilityRequestStatus">';
                    $actionHtml .= '<select class="form-control special_request_dropdown appearance_auto select2" name="special_request_status" data-special_request_id="' . $record->uuid . '" data-old_status="' . $record->status . '" ' . $disabled . '>';

                    foreach ($statusOptions as $key => $label) {
                        $selected = ($currentStatus === $key) ? 'selected' : '';
                        $actionHtml .= '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
                    }

                    $actionHtml .= '</select>';
                    $actionHtml .= '</div>';
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
    public function query(SpecialtyRequest $model): QueryBuilder
    {         
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $orderByColumn = 4;       
        return $this->builder()
                    ->setTableId('specialty-request-table')
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
                        'responsive' => true, // keep responsive enabled
                        'pagingType' => 'simple_numbers',
                        'language' => [
                            'emptyTable' => 'No records available',
                        ],
                        'drawCallback' => 'function(settings) {
                            var api = this.api();
                            var data = api.rows({ page: "current" }).data();
        
                            var hasData = data.length > 0;
                            var columnCount = $("#specialty-request-table").find("th").length;
                            // Store state globally
                            $("#specialty-request-table").data("has_data", hasData);
                            $("#specialty-request-table").attr("data-has_data", hasData);
        
                            // If there are no records, disable Responsive child rows
                            if (!hasData) {
                                setTimeout(function(){
                                    $("#specialty-request-table").find("th, td").css("display", "table-cell");
                                    $("#specialty-request-table").find(".dt-empty").attr("colspan", columnCount);
                                }, 500);
                            }
        
                            $(window).on("resize", function () {
                                var hasData = $("#specialty-request-table").data("has_data");
        
                                if (!hasData) {
                                    // Ensure all columns remain visible on resize if no data
                                    setTimeout(function(){
                                        $("#specialty-request-table").find("th, td").css("display", "table-cell");
                                        $("#specialty-request-table").find(".dt-empty").attr("colspan", columnCount);
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
      
        $columns[] = Column::make('name_en')->title(trans('cruds.specialty_request.fields.name_en'));
        $columns[] = Column::make('name_ar')->title(trans('cruds.specialty_request.fields.name_ar'));
        $columns[] = Column::make('user_role')->title(trans('cruds.specialty_request.fields.user_role'));
        // $columns[] = Column::make('created_by')->title(trans('cruds.specialty_request.fields.created_by'));
        $columns[] = Column::make('created_at')->title(trans('cruds.specialty_request.fields.created_at'))->addClass('created_at');
        $columns[] = Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(200)->addClass('text-center action-col');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'SpecialtyRequests_' . date('YmdHis');
    }
}
