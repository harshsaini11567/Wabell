<div class="modal fade show" id="ViewSplashScreen" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true" >
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.show') @lang('cruds.splash_screen.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="mb-2 normal_width_table">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.splash_screen.fields.title_en')</th>
                                    <td> {{ $splashScreen->title_en ?? 'N/A' }} </td>
                                </tr>
                                 <tr>
                                    <th style="width:150px;"> @lang('cruds.splash_screen.fields.title_ar')</th>
                                    <td dir="rtl"> {{ $splashScreen->title_ar ?? 'N/A' }} </td>
                                </tr>
                                 <tr>
                                    <th style="width:150px;"> @lang('cruds.splash_screen.fields.description_en')</th>
                                    <td> {{ $splashScreen->description_en ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.splash_screen.fields.description_ar')</th>
                                    <td dir="rtl"> {{ $splashScreen->description_ar ?? 'N/A' }} </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.splash_screen.fields.splash_screen_status')</th>
                                    <td> {{ config('constant.splash_screen_status.' . $splashScreen->status, 'N/A') }} </td>
                                </tr> 
                                <tr>
                                    <th> @lang('cruds.splash_screen.fields.splash_image') </th>
                                    <td>
                                        @if(!empty($splashScreen->splash_image_url))
                                            <a href="{{ $splashScreen->splash_image_url }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ $splashScreen->splash_image_url }}" alt="Splash Image" width="100px">
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.splash_screen.fields.created_at')</th>
                                    <td> {{ $splashScreen->created_at->format(config('constant.date_format.date_time')) }} </td>
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
