<div class="modal fade show" id="ViewVerifiedMaster" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.show') @lang('cruds.verified_master.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="mb-2 normal_width_table">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.verified_master.fields.name') </th>
                                    <td> {{ $master->name ?? '<span class="text-orange">N/A</span>' }} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.email') </th>
                                    <td> {!! $master->email ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.phone')  </th>
                                    <td> {!! $master->phone ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.gender') </th>
                                    <td> {!! $master->gender && isset(config('constant.gender')[$master->gender])
                                            ? config('constant.gender')[$master->gender]
                                            : '<span class="text-orange">N/A</span>'
                                        !!} 
                                    </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.dob') </th>
                                    <td> {!! $master->date_of_birth ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.city') </th>
                                    <td> {!! $master->city_id == 0 ? trans('constant.other', [], app()->getLocale()) : ($master->city->name_en ?? '') !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.neighborhood') </th>
                                    <td> {!! $master->neighborhood_id == 0 ? trans('constant.other', [], app()->getLocale()) : ($master->neighborhood->name_en ?? '') !!} </td>
                                </tr>

                                {{-- Specialty --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.specialty') </th>
                                    <td>
                                        @php
                                            $specialties = $master->specialties->sortBy(function ($item) {
                                                if (!$item->parent_specialty_id) return 0;
                                                elseif ($item->parentSpecialty && !$item->parentSpecialty->parent_specialty_id) return 1;
                                                else return 2;
                                            });
                                            $grouped = [];
                                            foreach ($specialties as $specialty) {
                                                if (!$specialty->parent_specialty_id) {
                                                    $grouped[$specialty->id] = ['name' => $specialty->name_en, 'children' => []];
                                                } elseif ($specialty->parentSpecialty && !$specialty->parentSpecialty->parent_specialty_id) {
                                                    $parentId = $specialty->parent_specialty_id;
                                                    if (!isset($grouped[$parentId])) {
                                                        $grouped[$parentId] = ['name' => $specialty->parentSpecialty->name_en, 'children' => []];
                                                    }
                                                    $grouped[$parentId]['children'][$specialty->id] = ['name' => $specialty->name_en, 'children' => []];
                                                } elseif ($specialty->parentSpecialty && $specialty->parentSpecialty->parent_specialty_id) {
                                                    $grandParentId = $specialty->parentSpecialty->parent_specialty_id;
                                                    $parentId = $specialty->parent_specialty_id;
                                                    if (!isset($grouped[$grandParentId])) {
                                                        $grouped[$grandParentId] = ['name' => $specialty->parentSpecialty->parentSpecialty->name_en ?? 'Unknown', 'children' => []];
                                                    }
                                                    if (!isset($grouped[$grandParentId]['children'][$parentId])) {
                                                        $grouped[$grandParentId]['children'][$parentId] = ['name' => $specialty->parentSpecialty->name_en ?? 'Unknown', 'children' => []];
                                                    }
                                                    $grouped[$grandParentId]['children'][$parentId]['children'][] = $specialty->name_en;
                                                }
                                            }
                                        @endphp
                                        @if (count($grouped))
                                            <ul style="padding-left: 16px; margin:0;">
                                                @foreach ($grouped as $level1)
                                                    <li>
                                                        <strong>{{ $level1['name'] }}</strong>
                                                        @if (!empty($level1['children']))
                                                            <ul>
                                                                @foreach ($level1['children'] as $level2)
                                                                    <li>
                                                                        {{ $level2['name'] }}
                                                                        @if (!empty($level2['children']))
                                                                            <ul>
                                                                                @foreach ($level2['children'] as $level3)
                                                                                    <li>{{ $level3 }}</li>
                                                                                @endforeach
                                                                            </ul>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-orange">N/A</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Certificates --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.certificates') </th>
                                    <td>
                                        @php
                                            $certImages = [];
                                            if (!empty($master->certificateFiles)) {
                                                foreach ($master->certificateFiles as $file) {
                                                    $certImages[] = asset('storage/' . $file->file_path);
                                                }
                                            }
                                        @endphp
                                        @if (!empty($certImages))
                                            @foreach ($certImages as $url)
                                                <a href="{{ $url }}" target="_blank">
                                                    <img src="{{ $url }}" width="100px" style="margin-right:10px;">
                                                </a>
                                            @endforeach
                                        @else
                                            <span class="text-orange">N/A</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- ID Files --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.id_files') </th>
                                    <td>
                                        @if ($master->idFiles()->count() > 0)
                                            @foreach ($master->idFiles as $file)
                                                <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $file->file_path) }}" width="100px" style="margin-right:10px;">
                                                </a>
                                            @endforeach
                                        @else
                                            <span class="text-orange">N/A</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Biography --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.biography') </th>
                                    <td> {!! $master->masterDetail->biography ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>

                                {{-- Experience --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.experience') </th>
                                    <td> {!! $master->masterDetail->experience 
                                            ? config('constant.experience')[$master->masterDetail->experience] ?? ucfirst(str_replace('_', ' ', $master->masterDetail->experience))
                                            : '<span class="text-orange">N/A</span>' !!} 
                                    </td>
                                </tr>

                                {{-- Education --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.education') </th>
                                    <td>
                                        @php
                                            $education = $master->masterDetail->education ?? [];
                                            if (is_string($education)) $education = json_decode($education, true);
                                            $educationLabels = [];
                                            foreach ($education as $eduKey) {
                                                $educationLabels[] = config('constant.education')[$eduKey] ?? ucfirst(str_replace('_', ' ', $eduKey));
                                            }
                                        @endphp
                                        {!! !empty($educationLabels) ? implode(', ', $educationLabels) : '<span class="text-orange">N/A</span>' !!}
                                    </td>
                                </tr>

                                {{-- Price per hour --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.price_per_hour') </th>
                                    <td> {!! $master->masterDetail->price_per_hour ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>

                                {{-- Available day --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.available_day') </th>
                                    <td>
                                        @php
                                            $availableDays = $master->masterDetail->available_day ?? [];
                                            if (is_string($availableDays)) $availableDays = json_decode($availableDays, true) ?: $availableDays;
                                            if (is_array($availableDays) && !empty($availableDays)) {
                                                $display = implode(', ', array_map(fn($day) => config('constant.available_day')[$day] ?? ucfirst($day), $availableDays));
                                            } elseif (is_string($availableDays)) {
                                                $display = config('constant.available_day')[$availableDays] ?? ucfirst($availableDays);
                                            } else {
                                                $display = '<span class="text-orange">N/A</span>';
                                            }
                                        @endphp
                                        {!! $display !!}
                                    </td>
                                </tr>

                                {{-- Available time --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.available_time') </th>
                                    <td>
                                        @php
                                            $availableTimes = $master->masterDetail->available_time ?? [];
                                            if (is_string($availableTimes)) $availableTimes = json_decode($availableTimes, true);
                                            if (is_array($availableTimes) && !empty($availableTimes)) {
                                                $display = implode(', ', array_map(fn($time) => config('constant.available_time')[$time] ?? ucfirst($time), $availableTimes));
                                            } elseif (is_string($availableTimes)) {
                                                $display = config('constant.available_time')[$availableTimes] ?? ucfirst($availableTimes);
                                            } else {
                                                $display = '<span class="text-orange">N/A</span>';
                                            }
                                        @endphp
                                        {!! $display !!}
                                    </td>
                                </tr>

                                {{-- Profile image --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.profile_image') </th>
                                    <td>
                                        @if(!empty($master->profile_image_url))
                                            <a href="{{ $master->profile_image_url }}" target="_blank">
                                                <img src="{{ $master->profile_image_url }}" width="100px">
                                            </a>
                                        @else
                                            <span class="text-orange">N/A</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- User status --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.user_status') </th>
                                    <td> {!! $master->user_status ? config('constant.user_status')[$master->user_status] : '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>

                                {{-- Created at --}}
                                <tr>
                                    <th> @lang('cruds.verified_master.fields.created_at') </th>
                                    <td> {!! $master->created_at ? $master->created_at->format(config('constant.date_format.date_time')) : '<span class="text-orange">N/A</span>' !!} </td>
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
