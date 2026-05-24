<?php

namespace App\Domains\Admin\City\DataTables;

// use App\Models\User;

use App\Domains\Core\User\Models\User;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
class UsersWithoutLocationDataTable extends DataTable
{
    protected string $type = 'city'; // default value

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
    
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('created_at', fn($record) => $record->created_at ? $record->created_at->format('Y-m-d H:i') : '')
            ->addColumn('role', function ($record) {
                return $record->roles && $record->roles->count()
                    ? $record->roles->pluck('name_en')->implode(', ')
                    : 'N/A';
            })
                ->addColumn('action', function ($record) {
                $url = route('cities.user.show', $record->uuid);
                $roles = $record->roles && $record->roles->count()
                ? e($record->roles->pluck('name_en')->implode(', '))
                : 'N/A';
                return '<a href="javascript:void(0);" onclick="viewCustomer(\'' . $url . '\', \'' . $roles . '\')" class="btn btn-outline-info btn-sm"><i class="ri-eye-line"></i></a>';
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $searchDateFormat = config('constant.search_date_format.date_time');
                $query->whereRaw("DATE_FORMAT(created_at,'$searchDateFormat') like ?", ["%$keyword%"]); //date_format when searching using date
            })
            ->rawColumns(['action']);
    }

    public function query(User $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with('roles')
            ->where('deleted_at', NULL);

        if ($this->type === 'city') {
            $query->where('city_id', '0');
        } elseif ($this->type === 'neighbor') {
            $query->where('neighborhood_id', '0');
        }

        return $query->orderByDesc('created_at');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-without-city-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(5)
            ->parameters([
                'responsive' => true,
                'pagingType' => 'simple_numbers',
                'language' => ['emptyTable' => 'No records available'],
            ]);
    }

    protected function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title(trans('global.sno'))->orderable(false)->searchable(false),
            Column::make('name')->title('Name'),
            Column::make('email')->title('Email'),
            Column::make('phone')->title('Mobile Number'),
            Column::make('role')->title('Role'), // NEW column
            Column::make('created_at')->title('Created At')->addClass('dt-created_at'),
            Column::computed('action')->orderable(false)->exportable(false)->printable(false)->width(100),
        ];
    }

    protected function filename(): string
    {
        return 'UsersWithoutCity_' . date('YmdHis');
    }
}