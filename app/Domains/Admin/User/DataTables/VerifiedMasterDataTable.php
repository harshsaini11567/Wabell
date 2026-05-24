<?php

namespace App\Domains\Admin\User\DataTables;

use App\Domains\Core\User\Models\User;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class VerifiedMasterDataTable extends DataTable
{
    public $customPageLength = 10;

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable(
            $query->select('users.*')->with(['masterDetail', 'city', 'activeSubscription.plan'])
        ))
            ->addIndexColumn()

            ->editColumn('created_at', function ($record) {
                return $record->created_at
                    ? $record->created_at->format(config('constant.date_format.date_time'))
                    : '-';
            })

            ->editColumn('name', fn($r) => $r->name ? ucwords($r->name) : '')
            ->editColumn('phone', fn($r) => $r->phone ?: '-')
            ->editColumn('email', fn($r) => $r->email ?: '-')
            // ->editColumn('city.name_en', fn($r) => $r->city ? ucwords($r->city->name_en) : '')
            ->editColumn('city.name_en', fn($r) =>
                $r->city_id == 0
                    ? trans('constant.other', [], app()->getLocale())
                    : ($r->city ? ucwords($r->city->name_en) : '')
            )

            ->editColumn('subscription_id', function ($r) {
                $subscription = $r->activeSubscription;
                if ($subscription && $subscription->plan) {
                    return $subscription->plan->{'name_' . app()->getLocale()};
                }
                return 'Basic';
            })

            /**
             *  APPROVAL STATUS COLUMN
             * (Handles approve/reject buttons and approved toggle)
             */
            ->editColumn('is_approved', function ($record) {
                $checked = $record->user_status === 'active' ? 'checked' : '';

                // if ($record->approval_status == 1) {
                //     // Approved → show toggle switch
                //     return '
                //     <div class="checkbox switch">
                //         <label>
                //             <input type="checkbox"
                //                 class="switch-control verified_master_management_status_cb"
                //                 ' . $checked . '
                //                 data-verified_master_management_id="' . $record->uuid . '" />
                //             <span class="switch-label"
                //                 data-active="' . trans('global.active') . '"
                //                 data-inactive="' . trans('global.inactive') . '"></span>
                //         </label>
                //     </div>';
                // }

                // Handle rejected or pending
                $statusText = '';
                if ($record->approval_status == 2) {
                    $statusText = '<span class="badge bg-danger">Rejected</span>';
                } elseif ($record->approval_status == 1) {
                    $statusText = '<span class="badge bg-success">Approved</span>';
                }

                $buttonsHtml = '';
                // Show approve/reject buttons only if pending
                if ($record->approval_status === 0) {
                    $buttonsHtml = '
                    <div class="d-flex gap-1">
                        <button type="button"
                                class="btn btn-sm btn-success btn-outline-success verified_master_approval_status"
                                data-status="1"
                                data-verified_master_id="' . $record->uuid . '"
                                title="Approve">
                            <i class="ri-check-line"></i>
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-danger btn-outline-danger verified_master_approval_status"
                                data-status="2"
                                data-verified_master_id="' . $record->uuid . '"
                                title="Reject">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>';
                }

                return $buttonsHtml . '<div>' . $statusText . '</div>';
            })

            ->addColumn('action', function ($record) {
                $actionHtml = '';

                // if ($record->approval_status == 1) {
                //     if (Gate::check('verified_master_edit')) {
                //         $actionHtml .= '<a href="javascript:void(0);" data-href="' . route('admins.change-password', $record->uuid) . '" class="btn btn-outline-dark btn-sm btnChangePassword" title="Change Password"><i class="fa fa-key"></i></a>';
                //     }
                // }

                if (Gate::check('verified_master_show')) {
                    $actionHtml .= '<a href="javascript:void(0);" data-href="' . route('verified-masters.show', $record->uuid) . '" class="btn btn-outline-info btn-sm btnViewVerifiedMaster" title="Show"><i class="ri-eye-line"></i></a>';
                }

                // if ($record->approval_status == 1) {
                //     if (Gate::check('verified_master_edit')) {
                //         $actionHtml .= '<a href="javascript:void(0);" data-href="' . route('verified-masters.edit', $record->uuid) . '" class="btn btn-outline-success btn-sm btnEditVerifiedMaster" title="Edit"><i class="ri-edit-2-line"></i></a>';
                //     }

                //     if (Gate::check('verified_master_ban')) {
                //         $isban = $record->is_ban === 1;
                //         $btnClass = $isban ? 'danger' : 'success';
                //         $title = $isban ? 'Unban' : 'Ban';
                //         $icon = $isban ? 'lock' : 'prohibited';
                //         $actionHtml .= '<a href="javascript:void(0);" data-is_ban="' . $isban . '" data-master_id="' . $record->uuid . '" class="btn btn-outline-' . $btnClass . ' btn-sm btnEditAdmin master_isban" title="' . $title . '"><i class="ri-' . $icon . '-line"></i></a>';
                //     }

                //     if (Gate::check('verified_master_delete')) {
                //         $actionHtml .= '<a href="javascript:void(0);" class="btn btn-outline-danger btn-sm deleteVerifiedMasterBtn" data-href="' . route('verified-masters.destroy', $record->uuid) . '" title="Delete"><i class="ri-delete-bin-line"></i></a>';
                //     }
                // }

                return $actionHtml;
            })
            ->setRowId('id')
            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]);
            })
            ->rawColumns(['action', 'is_approved']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->whereHas('roles', function ($q) {
                $q->where('role_type', 'app')->where('name_en', 'Master');
            })
            ->whereNotNull('date_of_birth')
            ->whereHas('uploads', fn($q) => $q->where('type', 'certificate_file'))
            ->whereHas('specialties')
            ->with(['activeSubscription.plan', 'uploads', 'specialties'])
            ->newQuery();
    }

    public function html(): HtmlBuilder
    {
        $orderByColumn = 7;
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
                'responsive' => true,
                'pagingType' => 'simple_numbers',
                'language' => ['emptyTable' => 'No records available'],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title(trans('global.sno'))->orderable(false)->searchable(false)->addClass('dt-sno'),
            Column::make('name')->title(trans('cruds.master.fields.name')),
            Column::make('phone')->title(trans('cruds.master.fields.phone')),
            Column::make('email')->title(trans('cruds.master.fields.email')),
            Column::make('city.name_en')->title(trans('cruds.master.fields.city_id')),
            Column::make('subscription_id')->title(trans('cruds.master.fields.subscription_id'))->orderable(false)->searchable(false),
            Column::make('is_approved')->title(trans('cruds.master.fields.is_approved')),
            Column::make('created_at')->title(trans('cruds.master.fields.created_at'))->addClass('dt-created_at'),
            Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(100)->addClass('text-center action-col'),
        ];
    }

    protected function filename(): string
    {
        return 'masters_' . date('YmdHis');
    }
}
