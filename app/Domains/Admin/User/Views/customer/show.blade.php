<div class="modal fade show" id="ViewCustomer" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true" >
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">@lang('global.show') @lang('cruds.customer.title_singular')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="mb-2 normal_width_table">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width:150px;"> @lang('cruds.customer.fields.name') </th>
                                    <td> {!! $customer->name ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.email') </th>
                                    <td> {!! $customer->email ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.phone')  </th>
                                    <td> {!! $customer->country_code && $customer->phone ? $customer->country_code . ' ' . $customer->phone : '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.myGender') </th>
                                    <td> {!! $customer->gender && isset(config('constant.gender')[$customer->gender])
                                            ? config('constant.gender')[$customer->gender]
                                            : '<span class="text-orange">N/A</span>' 
                                        !!} 
                                    </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.dob') </th>
                                    <td> {!! $customer->date_of_birth ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.city') </th>
                                    <td> {!! $customer->city_id == 0 ? trans('constant.other', [], app()->getLocale()) : ($customer->city->name_en ?? '') !!} </td>
                                    <!-- <td> {{ $customer->city_id ?? 'N/A' }} </td> -->
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.neighborhood') </th>
                                    <td> {!! $customer->neighborhood_id == 0 ? trans('constant.other', [], app()->getLocale()) : ($customer->neighborhood->name_en ?? '') !!} </td>
                                    <!-- <td> {{ $customer->neighborhood_id ?? 'N/A' }} </td> -->
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.specialty') </th>
                                    <td> 
                                        @php
                                            $specialties = $customer->specialties->sortBy(function ($item) {
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
                                    <th> @lang('cruds.customer.fields.about') </th>
                                    <td> {!! $customer->about_user ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.interest') </th>
                                    <td> {!! $customer->user_interest ?? '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                 <tr>
                                    <th> @lang('cruds.customer.fields.learning_mode') </th>
                                    <td> {!!
                                            $customer->learning_mode && isset(config('constant.learning_mode')[$customer->learning_mode])
                                                ? config('constant.learning_mode')[$customer->learning_mode]
                                                : '<span class="text-orange">N/A</span>'
                                        !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.prefer_to_learn') </th>
                                    <td> {!! $customer->gender_preference && isset(config('constant.gender_preference')[$customer->gender_preference])
                                        ? config('constant.gender_preference')[$customer->gender_preference]
                                        : '<span class="text-orange">N/A</span>'  !!}
                                    </td>
                                </tr>
                                <!-- <tr>
                                    <th> @lang('cruds.customer.fields.roles') </th>
                                    <td>
                                        @foreach($customer->roles as $role)
                                            <span class="badge bg-success">{{ $role->name_en }}</span>
                                        @endforeach
                                    </td>
                                </tr> -->
                                <tr>
                                    <th> @lang('cruds.customer.fields.profile_image') </th>
                                    <td>
                                        @if(!empty($customer->profile_image_url))
                                            <a href="{{ $customer->profile_image_url }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ $customer->profile_image_url }}" alt="Profile Image" width="100px">
                                            </a>
                                        @else
                                            <span class="text-orange">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th> @lang('cruds.customer.fields.user_status') </th>
                                    <td> {!! $customer->user_status ? config('constant.user_status')[$customer->user_status] : '<span class="text-orange">N/A</span>' !!} </td>
                                </tr>
                                <tr>
                                    <th> @lang('cruds.customer.fields.created_at') </th>
                                    <td> {{ $customer->created_at->format(config('constant.date_format.date_time')) }} </td>
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
