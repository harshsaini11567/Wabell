<div id="sortable_screens">
@foreach ($splashScreens as $key => $splashScreen)    
    <div class="accordion-item faq_inner" data-id="{{ $splashScreen->id }}" data-position="{{ $splashScreen->position }}">
        <div class="row align-items-center mx-0 faq_question_header position-relative">
            <div class="col-md-9 col-lg-10">
                <div class="header_question">
                    <h6>@lang('cruds.splash_screen.fields.title_en'):</h6>
                    <p>{{ $splashScreen->title_en }}</p>
                </div>
                <div class="header_question">
                    <h6>@lang('cruds.splash_screen.fields.title_ar'):</h6>
                    <p dir="rtl">{{ $splashScreen->title_ar }}</p>
                </div>
            </div>
            <div class="col-md-3 col-lg-2 text-end">
                @if ($splashScreen->status)
                    @php
                        $colorClass = match ($splashScreen->status) {
                            'active' => 'badge bg-success',
                            'inactive' => 'badge bg-danger',
                            default => 'badge bg-secondary',
                        };
                    @endphp

                    <span class="{{ $colorClass }}">
                        {{ ucwords($splashScreen->status) }}
                    </span>
                @else
                    <span>-</span>
                @endif
                <div class="faq_btns">
                    <span class="btn btn-outline-info btn-sm drag-handle" title="Drag to sort" style="cursor: move;">
                        <i class="ri-drag-move-2-line"></i>
                    </span>
                    <a href="javascript:void(0)" class="btn btn-outline-info btn-sm btnViewSplashScreen"  data-href="{{ request()->routeIs('splash-screens.*') ? route('splash-screens.show', $splashScreen->id) : route('splash-screens.show', $splashScreen->id) }}" data-step="0"><i class="ri-eye-line"></i></a>
                    <a href="javascript:void(0)" class="btn btn-outline-dark btn-sm btnEditSplashScreen"  data-href="{{ request()->routeIs('splash-screens.*') ? route('splash-screens.edit', $splashScreen->id) : route('splash-screens.edit', $splashScreen->id) }}" data-step="0"><i class="ri-pencil-line"></i></a>
                    <!-- <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm deleteSplashScreenBtn"  data-href="{{ request()->routeIs('splash-screens.*') ? route('splash-screens.destroy', $splashScreen->id) : route('splash-screens.destroy', $splashScreen->id) }}" data-step="0"><i class="ri-delete-bin-line"></i></a> -->
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq_collapse_{{$splashScreen->id}}" aria-expanded="true" aria-controls="faq_collapse_{{$splashScreen->id}}"></button>
                </div>
            </div>
        </div>
        <div id="faq_collapse_{{$splashScreen->id}}" class="accordion-collapse collapse {{-- {{ $key == 0 ? 'show' : '' }} --}}" aria-labelledby="faq_header_{{$splashScreen->id}}"
            data-bs-parent="#accordion_splash_screen">
            <div class="accordion-body">
                <div class="body_answer">
                    <h6>@lang('cruds.splash_screen.fields.description_en'):</h6>
                    <p>{{ $splashScreen->description_en }}</p>
                </div>
                <div class="body_answer">
                    <h6>@lang('cruds.splash_screen.fields.description_ar'):</h6>
                    <p dir="rtl">{{ $splashScreen->description_ar }}</p>
                </div>
            </div>
        </div>
    </div>
@endforeach
</div>
