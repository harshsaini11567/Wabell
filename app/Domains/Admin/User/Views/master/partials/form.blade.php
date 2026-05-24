<div class="card-body">
    <div class="row">
        <input type="hidden" name="userIdFiles" id="userIdFiles">
        <input type="hidden" name="userCertificateFiles" id="userCertificateFiles">

        {{-- Name --}}
        <div class="form-group col-md-6">
            <label for="name">@lang('cruds.master.fields.name') <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control {{ empty($master->name) ? 'border-orange ' : '' }}" 
                   id="name" 
                   name="name" 
                   value="{{ $master->name ?? '' }}" 
                   required>
        </div>

        {{-- Email --}}
        <div class="form-group col-md-6">
            <label for="email">@lang('cruds.master.fields.email')</label>
            <input type="text" 
                   class="form-control {{ empty($master->email) ? 'border-orange ' : '' }}" 
                   id="email" 
                   name="email" 
                   value="{{ $master->email ?? '' }}" 
                   required autocomplete="username" readonly disabled>
        </div>

        {{-- Phone --}}
        <div class="form-group col-md-6">
            <label for="phone">@lang('cruds.master.fields.phone')</label>
            <input type="text" 
                   class="form-control {{ empty($master->phone) ? 'border-orange ' : '' }}" 
                   id="phone" 
                   name="phone" 
                   value="{{ isset($master) ? $master->country_code . ' ' . $master->phone : '' }}" 
                   required readonly disabled>
        </div>

        {{-- Education --}}
        <div class="form-group col-md-6">
            <label for="education">@lang('cruds.master.fields.education') <span class="text-danger">*</span></label>
            <select name="education[]" 
                    id="education" 
                    class="form-control select2 {{ empty($master->masterDetail->education) ? 'border-orange ' : '' }}" 
                    multiple required>
                <option value="">Select @lang('cruds.master.fields.education')</option>
                @foreach (config('constant.education') as $key => $education)
                    <option value="{{ $key }}" 
                        {{ isset($master->masterDetail->education) && in_array($key, $master->masterDetail->education) ? 'selected' : '' }}>
                        {{ $education }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Experience --}}
        <div class="form-group col-md-6">
            <label for="experience">@lang('cruds.master.fields.experience') <span class="text-danger">*</span></label>
            <select name="experience" 
                    id="experience" 
                    class="form-control select2 {{ empty($master->masterDetail->experience) ? 'border-orange ' : '' }}" 
                    required>
                <option value="">Select @lang('cruds.master.fields.experience')</option>
                @foreach (config('constant.experience') as $key => $experience)
                    <option value="{{ $key }}" 
                        {{ isset($master->masterDetail->experience) && $master->masterDetail->experience == $key ? 'selected' : '' }}>
                        {{ $experience }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tagline --}}
        <div class="form-group col-md-6">
            <label for="tagline">@lang('cruds.master.fields.tagline') <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control {{ empty($master->masterDetail->tagline) ? 'border-orange ' : '' }}" 
                   id="tagline" 
                   name="tagline" 
                   value="{{ $master->masterDetail->tagline ?? '' }}" 
                   required>
        </div>

        {{-- Biography --}}
        <div class="form-group col-md-6">
            <label for="biography">@lang('cruds.master.fields.biography') <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control {{ empty($master->masterDetail->biography) ? 'border-orange ' : '' }}" 
                   id="biography" 
                   name="biography" 
                   value="{{ $master->masterDetail->biography ?? '' }}" 
                   required>
        </div>

        {{-- Price per hour --}}
        <div class="form-group col-md-6 price_per_column position-relative">
            <label for="price_per_hour">@lang('cruds.master.fields.price_per_hour') <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control {{ empty($master->masterDetail->price_per_hour) ? 'border-orange ' : '' }}" 
                   id="price_per_hour" 
                   name="price_per_hour" 
                   value="{{ $master->masterDetail->price_per_hour ?? '' }}" 
                   required>
            <span class="input-group-text">{{ config('constant.currency') }}</span>
        </div>

        {{-- User Status --}}
        <div class="form-group col-md-6">
            <label for="user_status">@lang('cruds.master.fields.user_status') <span class="text-danger">*</span></label>
            <select name="user_status" 
                    id="user_status" 
                    class="form-control select2 {{ empty($master->user_status) ? 'border-orange ' : '' }}">
                <option value="">Select @lang('cruds.master.fields.user_status')</option>
                @foreach (config('constant.user_status') as $key => $status)
                    <option value="{{ $key }}" 
                        {{ isset($master) && $master->user_status == $key ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Available Time --}}
        <div class="form-group col-md-6">
            <label for="available_time">@lang('cruds.master.fields.available_time') <span class="text-danger">*</span></label>
            <select name="available_time[]" 
                    id="available_time" 
                    class="form-control select2 {{ empty($master->masterDetail->available_time) ? 'border-orange ' : '' }}" 
                    multiple required>
                <option value="">Select @lang('cruds.master.fields.available_time')</option>
                @foreach (config('constant.available_time') as $key => $time)
                    <option value="{{ $key }}" 
                        {{ isset($master->masterDetail->available_time) && in_array($key, $master->masterDetail->available_time) ? 'selected' : '' }}>
                        {{ $time }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Available Day --}}
        <div class="form-group col-md-6">
            <label for="available_day">@lang('cruds.master.fields.available_day') <span class="text-danger">*</span></label>
            <select name="available_day[]" 
                    id="available_day" 
                    class="form-control select2 {{ empty($master->masterDetail->available_day) ? 'border-orange ' : '' }}" 
                    multiple required>
                <option value="">Select @lang('cruds.master.fields.available_day')</option>
                @foreach (config('constant.available_day') as $key => $day)
                    <option value="{{ $key }}" 
                        {{ isset($master->masterDetail->available_day) && in_array($key, $master->masterDetail->available_day) ? 'selected' : '' }}>
                        {{ $day }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Profile Image --}}
        <div class="form-row">
            <div class="form-group col-md-12">
                <label class="form-label">@lang('cruds.master.fields.profile_image')</label>
                <input name="profile_image" 
                       type="file" 
                       class="dropify {{ empty($master->profile_image_url) ? 'border-orange ' : '' }}" 
                       id="profile_image" 
                       data-default-file="{{ $master->profile_image_url ?? '' }}"  
                       data-show-loader="true" 
                       data-errors-position="outside" 
                       data-allowed-file-extensions="jpeg png jpg PNG JPG" 
                       accept="image/jpeg, image/png, image/jpg, image/PNG, image/JPG" />
            </div>
        </div>

        {{-- ID Files --}}

        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="id_files">@lang('cruds.master.fields.id_files')</label>
                <div id="id_files" class="dropzone {{ ($master->idFiles && $master->idFiles->isEmpty()) ? 'border-orange' : '' }}">
                    <div class="dz-default dz-message">Drag & Drop files</div>
                </div>
            </div>
        </div>

        {{-- Certificate Files --}}
        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="certificate_files">@lang('cruds.master.fields.certificate_files')</label>
                <div id="certificate_files" class="dropzone {{ ($master->certificateFiles && $master->certificateFiles->isEmpty()) ? 'border-orange' : '' }}">
                    <div class="dz-default dz-message">Drag & Drop files</div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="card-footer">
    <button type="submit" class="btn btn-primary submitBtn">@lang('global.save')</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
</div>
