<?php

namespace App\Domains\Admin\Announcement\DataTables;

use App\Domains\Core\Announcement\Models\Announcement;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class AnnouncementDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('created_at', function($record) {
                return $record->created_at->format(config('constant.date_format.date_time'));
            })
            ->editColumn('title_en', function($record){
                return $record->title_en ? ucwords($record->title_en) : '-';
            })
            ->editColumn('title_ar', function($record){
                return $record->title_ar ? ucwords($record->title_ar) : '-';
            })
            ->addColumn('action', function ($record) {
                $actionHtml = '';

                if (Gate::check('announcement_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="' . route('announcements.show', $record->uuid) . '" class="btn btn-outline-info btn-sm btnViewAnnouncement" title="Show"><i class="ri-eye-line"></i></a> ';
                }

                if ($record) {
                    if (Gate::check('announcement_delete')) {
                        $actionHtml .= '<a href="javascript:void(0);" data-href="' . route('announcements.destroy', $record->uuid) . '" class="btn btn-outline-danger btn-sm deleteAnnouncementBtn" title="Delete"><i class="ri-delete-bin-line"></i></a>';
                    }
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

    public function query(Announcement $model): QueryBuilder
    {
        // Show only announcements with announcement_type super_admin or admin
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        $orderByColumn = 3;   
        return $this->builder()
            ->setTableId('announcement-table')
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
                    var columnCount = $("#announcement-table").find("th").length;
                    // Store state globally
                    $("#announcement-table").data("has_data", hasData);
                    $("#announcement-table").attr("data-has_data", hasData);
 
                    // If there are no records, disable Responsive child rows
                    if (!hasData) {
                       setTimeout(function(){
                                $("#announcement-table").find("th, td").css("display", "table-cell");
                                $("#announcement-table").find(".dt-empty").attr("colspan", columnCount);
                            }, 500);
                    }
 
                    $(window).on("resize", function () {
                        var hasData = $("#announcement-table").data("has_data");
 
                        if (!hasData) {
                            // Ensure all columns remain visible on resize if no data
                           setTimeout(function(){
                                $("#announcement-table").find("th, td").css("display", "table-cell");
                                $("#announcement-table").find(".dt-empty").attr("colspan", columnCount);
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
            Column::make('title_en')
                ->title(trans('cruds.announcement.fields.title_en')),
            Column::make('title_ar')
                ->title(trans('cruds.announcement.fields.title_ar')),
            Column::make('created_at')
                ->title(trans('cruds.announcement.fields.created_at')),
            Column::computed('action')
                ->orderable(false)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center action-col'),
        ];
    }

    protected function filename(): string
    {
        return 'Announcements' . date('YmdHis');
    }
}
