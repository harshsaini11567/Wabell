<div class="modal fade show" id="ViewAdmin" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true" >
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.show') @lang('cruds.admin.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="mb-2 normal_width_table">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.admin.fields.name') </th>
                                    <td> {{ $admin->name ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.admin.fields.email') </th>
                                    <td> {{ $admin->email ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.admin.fields.phone')  </th>
                                    <td> {{ $admin->phone ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.admin.fields.roles') </th>
                                    <td>
                                        @foreach($admin->roles as $role)
                                            <span class="badge bg-success">{{ $role->name_en }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.admin.fields.profile_image') </th>
                                    <td>
                                        @if(!empty($admin->profile_image_url))
                                            <a href="{{ $admin->profile_image_url }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ $admin->profile_image_url }}" alt="Profile Image" width="100px">
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th> @lang('cruds.admin.fields.user_status') </th>
                                    <td> {{ $admin->user_status ? config('constant.user_status')[$admin->user_status] : 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.admin.fields.created_at') </th>
                                    <td> {{ $admin->created_at->format(config('constant.date_format.date_time')) }} </td>
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
