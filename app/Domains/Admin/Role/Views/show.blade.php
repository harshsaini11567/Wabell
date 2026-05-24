<div class="modal fade show" id="ViewRole" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true" >
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.show') @lang('cruds.role.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="mb-2 normal_width_table">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.role.fields.name_en')</th>
                                    <td> {{ $role->name_en ?? 'N/A' }} </td>
                                </tr>
                                 <tr>
                                    <th style="width:150px;"> @lang('cruds.role.fields.name_ar')</th>
                                    <td dir="rtl"> {{ $role->name_ar ?? 'N/A' }} </td>
                                </tr>
                                 <tr>
                                    <th style="width:150px;"> @lang('cruds.role.fields.description_en')</th>
                                    <td> {{ $role->description_en ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.role.fields.description_ar')</th>
                                    <td dir="rtl"> {{ $role->description_ar ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.role.fields.permission')</th>
                                    <td>
                                        <div class="column_3blog">
                                            @if($groupedPermissions->count())
                                                @foreach($groupedPermissions as $module => $permissions)
                                                    <div>
                                                        <strong>{{ ucfirst(str_replace('_', ' ', $module)) }}</strong>
                                                        <ul style="margin: 0; padding-left: 18px;">
                                                            @foreach($permissions as $permission)
                                                                <li>{{ $permission->title }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endforeach
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.role.fields.status')</th>
                                    <td> {{ config('constant.status.' . $role->role_status, 'N/A') }} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.role.fields.created_at')</th>
                                    <td> {{ $role->created_at->format(config('constant.date_format.date_time')) }} </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
