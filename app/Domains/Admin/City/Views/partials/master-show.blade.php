<div class="card-body">
            <div class="mb-2 normal_width_table">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th style="width:150px;"> @lang('cruds.master.fields.name') </th>
                            <td> {{ $user->name ?? 'N/A' }} </td>
                        </tr>
                        <tr>
                            <th> @lang('cruds.master.fields.email') </th>
                            <td> {{ $user->email ?? 'N/A' }} </td>
                        </tr>
                        <tr>
                            <th> @lang('cruds.master.fields.phone')  </th>
                            <td> {{ $user->phone ?? 'N/A' }} </td>
                        </tr>
                            <tr>
                            <th> @lang('cruds.master.fields.myGender') </th>
                            <td> {{ $user->gender && isset(config('constant.gender')[$user->gender])
                                    ? config('constant.gender')[$user->gender]
                                    : 'N/A' 
                                }} 
                            </td>
                        </tr>
                        <tr>
                            <th> @lang('cruds.master.fields.dob') </th>
                            <td> {!! $user->date_of_birth ?? '<span class="text-orange">N/A</span>' !!} </td>
                        </tr>
                        <tr>
                            <th> @lang('cruds.master.fields.city') </th>
                            <td> {!! $user->city_id == 0 ? trans('constant.other', [], app()->getLocale()) : ($user->city->name_en ?? '') !!} </td>
                            <!-- <td> {{ $user->city_id ?? 'N/A' }} </td> -->
                        </tr>
                        <tr>
                            <th> @lang('cruds.master.fields.neighborhood') </th>
                            <td> {!! $user->neighborhood_id == 0 ? trans('constant.other', [], app()->getLocale()) : ($user->neighborhood->name_en ?? '') !!} </td>
                            <!-- <td> {{ $user->neighborhood_id ?? 'N/A' }} </td> -->
                        </tr>
                        <tr>
                            <th> @lang('cruds.master.fields.specialty') </th>
                            <td> 
                                @php
                                    $specialties = $user->specialties->sortBy(function ($item) {
                                        if (!$item->parent_specialty_id) {
                                            return 0;
                                        } elseif ($item->parentSpecialty && !$item->parentSpecialty->parent_specialty_id) {
                                            return 1;
                                        } else {
                                            return 2;
                                        }
                                    });
                                    $grouped = [];

                                    foreach ($specialties as $specialty) {
                                        if (!$specialty->parent_specialty_id) {
                                            // Level 1
                                            $grouped[$specialty->id] = [
                                                'name' => $specialty->name_en,
                                                'children' => [],
                                            ];
                                        } elseif ($specialty->parentSpecialty && !$specialty->parentSpecialty->parent_specialty_id) {
                                            // Level 2
                                            $parentId = $specialty->parent_specialty_id;
                                            if (!isset($grouped[$parentId])) {
                                                $grouped[$parentId] = [
                                                    'name' => $specialty->parentSpecialty->name_en,
                                                    'children' => [],
                                                ];
                                            }
                                            $grouped[$parentId]['children'][$specialty->id] = [
                                                'name' => $specialty->name_en,
                                                'children' => [],
                                            ];
                                        } elseif ($specialty->parentSpecialty && $specialty->parentSpecialty->parent_specialty_id) {
                                            // Level 3
                                            $grandParentId = $specialty->parentSpecialty->parent_specialty_id;
                                            $parentId = $specialty->parent_specialty_id;

                                            if (!isset($grouped[$grandParentId])) {
                                                $grouped[$grandParentId] = [
                                                    'name' => $specialty->parentSpecialty->parentSpecialty->name_en ?? 'Unknown',
                                                    'children' => [],
                                                ];
                                            }

                                            if (!isset($grouped[$grandParentId]['children'][$parentId])) {
                                                $grouped[$grandParentId]['children'][$parentId] = [
                                                    'name' => $specialty->parentSpecialty->name_en ?? 'Unknown',
                                                    'children' => [],
                                                ];
                                            }
                                            
                                            $grouped[$grandParentId]['children'][$parentId]['children'][] = $specialty->name_en;
                                        }
                                    }
                                @endphp

                                @if (count($grouped))
                                    <ul style="padding-left: 16px; margin: 0;">
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
                        <tr>
                            <th> @lang('cruds.master.fields.certificates') </th>
                            <td>
                                @php
                                    $certImages = [];
                                    if (!empty($user->certificateFiles)) {
                                        foreach ($user->certificateFiles as $file) {
                                            $certImages[] = asset('storage/' . $file->file_path);
                                        }
                                    }
                                @endphp

                                @if (!empty($certImages))
                                    @foreach ($certImages as $url)
                                        <a href="{{ $url }}" target="_blank">
                                            <img src="{{ $url }}" alt="Certificate" width="100px" style="margin-right: 10px;">
                                        </a>
                                    @endforeach
                                @else
                                    <span class="text-orange">N/A</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th> @lang('cruds.master.fields.id_files') </th>
                            <td>
                                @if ($user->idFiles()->count() > 0)
                                    @foreach ($user->idFiles as $file)
                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $file->file_path) }}" alt="Certificate" width="100px" style="margin-right: 10px;">
                                        </a>
                                    @endforeach
                                @else
                                    <span class="text-orange">N/A</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th> @lang('cruds.master.fields.biography') </th>
                            <td> {!! $user->masterDetail->biography ?? '<span class="text-orange">N/A</span>' !!} </td>
                        </tr>
                            <tr>
                            <th> @lang('cruds.master.fields.experience') </th>
                            <td> {!! $user->masterDetail->experience 
                                    ? config('constant.experience')[$user->masterDetail->experience] ?? ucfirst(str_replace('_', ' ', $user->masterDetail->experience)) 
                                    : '<span class="text-orange">N/A</span>' !!} </td>
                        </tr>
                            <tr>
                            <th> @lang('cruds.master.fields.education') </th>
                            <td>  @php
                                    $education = $user->masterDetail->education ?? [];
                                    if (is_string($education)) {
                                        $education = json_decode($education, true);
                                    }
                                    $educationLabels = [];

                                    foreach ($education as $eduKey) {
                                        $educationLabels[] = config('constant.education')[$eduKey] ?? ucfirst(str_replace('_', ' ', $eduKey));
                                    }
                                @endphp

                                {!! !empty($educationLabels) ? implode(', ', $educationLabels) : '<span class="text-orange">N/A</span>' !!}
                            </td>
                        </tr>
                        <tr>
                            <th> @lang('cruds.master.fields.price_per_hour') </th>
                            <td> {!! $user->masterDetail->price_per_hour ?? '<span class="text-orange">N/A</span>' !!} </td>
                        </tr>

                        <tr>
                            <th> @lang('cruds.master.fields.available_day') </th>
                            <td>  @php
                                    $availableDays = $user->masterDetail->available_day ?? [];
                                    if (is_string($availableDays)) {
                                        $availableDays = json_decode($availableDays, true) ?: $availableDays;
                                    }
                                    if (is_array($availableDays) && !empty($availableDays)) {
                                        $display = implode(', ', array_map(function ($day) {
                                            return config('constant.available_day')[$day] ?? ucfirst($day);
                                        }, $availableDays));
                                    } elseif (is_string($availableDays)) {
                                        $display = config('constant.available_day')[$availableDays] ?? ucfirst($availableDays);
                                    } else {
                                        $display = '<span class="text-orange">N/A</span>';
                                    }
                                @endphp

                                {!! $display !!}
                            </td>
                        </tr>

                        <tr>
                            <th> @lang('cruds.master.fields.available_time') </th>
                            <td> @php
                                    $availableTimes = $user->masterDetail->available_time ?? [];
                                    if (is_string($availableTimes)) {
                                        $availableTimes = json_decode($availableTimes, true);
                                    }
                                    if (is_array($availableTimes) && !empty($availableTimes)) {
                                        $display = implode(', ', array_map(function ($time) {
                                            return config('constant.available_time')[$time] ?? ucfirst($time);
                                        }, $availableTimes));
                                    } elseif (is_string($availableTimes)) {
                                        $display = config('constant.available_time')[$availableTimes] ?? ucfirst($availableTimes);
                                    } else {
                                        $display = '<span class="text-orange">N/A</span>';
                                    }
                                @endphp

                                {!! $display !!}
                            </td>
                        </tr>

                        <!-- <tr>
                            <th> @lang('cruds.master.fields.roles') </th>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-success">{{ $role->name_en }}</span>
                                @endforeach
                            </td>
                        </tr> -->

                            <tr>
                            <th> @lang('cruds.master.fields.prefer_to_teach') </th>
                            <td> {!! $user->gender_preference && isset(config('constant.gender_preference')[$user->gender_preference])
                                ? config('constant.gender_preference')[$user->gender_preference]
                                : '<span class="text-orange">N/A</span>'  !!}
                            </td>
                        </tr>

                        <tr>
                            <th> @lang('cruds.master.fields.profile_image') </th>
                            <td>
                                @if(!empty($user->profile_image_url))
                                    <a href="{{ $user->profile_image_url }}" target="_blank" rel="noopener noreferrer">
                                        <img src="{{ $user->profile_image_url }}" alt="Profile Image" width="100px">
                                    </a>
                                @else
                                    <span class="text-orange">N/A</span>
                                @endif
                            </td>
                        </tr>
                        
                        <tr>
                            <th> @lang('cruds.master.fields.user_status') </th>
                            <td> {!! $user->user_status ? config('constant.user_status')[$user->user_status] : '<span class="text-orange">N/A</span>' !!} </td>
                        </tr>
                        <!-- <tr>
                            <th> @lang('cruds.master.fields.approval_status') </th>
                            <td>  {{ $user->is_approved === null  ? 'N/A' : (config('constant.approval_status')[(int) $user->is_approved] ?? 'N/A') }} </td>
                        </tr> -->
                        <tr>
                            <th> @lang('cruds.master.fields.created_at') </th>
                            <td> {{ $user->created_at->format(config('constant.date_format.date_time')) }} </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    