<div class="card-body">
    <div class="mb-2 normal_width_table">
        <table class="table table-striped">
            <tbody>
                <tr>
                    <th style="width:150px;">@lang('cruds.customer.fields.name')</th>
                    <td>{!! $user->name ?? '<span class="text-orange">N/A</span>' !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.email')</th>
                    <td>{!! $user->email ?? '<span class="text-orange">N/A</span>' !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.phone')</th>
                    <td>{!! $user->country_code && $user->phone ? $user->country_code . ' ' . $user->phone : '<span class="text-orange">N/A</span>' !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.myGender')</th>
                    <td>{!! $user->gender && isset(config('constant.gender')[$user->gender])
                            ? config('constant.gender')[$user->gender]
                            : '<span class="text-orange">N/A</span>'
                        !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.dob')</th>
                    <td>{!! $user->date_of_birth ?? '<span class="text-orange">N/A</span>' !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.city')</th>
                    <td> {!! $user->city_id == 0 ? trans('constant.other', [], app()->getLocale()) : ($user->city->name_en ?? '') !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.neighborhood')</th>
                    <td> {!! $user->neighborhood_id == 0 ? trans('constant.other', [], app()->getLocale()) : ($user->neighborhood->name_en ?? '') !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.specialty')</th>
                    <td>
                        @php
                            $specialties = $user->specialties->sortBy(function ($item) {
                                if (!$item->parent_specialty_id) return 0;
                                elseif ($item->parentSpecialty && !$item->parentSpecialty->parent_specialty_id) return 1;
                                return 2;
                            });
                            $grouped = [];

                            foreach ($specialties as $specialty) {
                                if (!$specialty->parent_specialty_id) {
                                    $grouped[$specialty->id] = [
                                        'name' => $specialty->name_en,
                                        'children' => [],
                                    ];
                                } elseif ($specialty->parentSpecialty && !$specialty->parentSpecialty->parent_specialty_id) {
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
                    <th>@lang('cruds.customer.fields.about')</th>
                    <td>{!! $user->about_user ?? '<span class="text-orange">N/A</span>' !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.interest')</th>
                    <td>{!! $user->user_interest ?? '<span class="text-orange">N/A</span>' !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.learning_mode')</th>
                    <td>{!! $user->learning_mode && isset(config('constant.learning_mode')[$user->learning_mode])
                            ? config('constant.learning_mode')[$user->learning_mode]
                            : '<span class="text-orange">N/A</span>'
                        !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.prefer_to_learn')</th>
                    <td>{!! $user->gender_preference && isset(config('constant.gender_preference')[$user->gender_preference])
                            ? config('constant.gender_preference')[$user->gender_preference]
                            : '<span class="text-orange">N/A</span>'
                        !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.profile_image')</th>
                    <td>
                        @if(!empty($user->profile_image_url))
                            <a href="{{ $user->profile_image_url }}" target="_blank">
                                <img src="{{ $user->profile_image_url }}" alt="Profile Image" width="100px">
                            </a>
                        @else
                            <span class="text-orange">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.user_status')</th>
                    <td>{!! $user->user_status ? config('constant.user_status')[$user->user_status] : '<span class="text-orange">N/A</span>' !!}</td>
                </tr>
                <tr>
                    <th>@lang('cruds.customer.fields.created_at')</th>
                    <td>{{ $user->created_at->format(config('constant.date_format.date_time')) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
