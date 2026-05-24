<?php

namespace App\Domains\Api\Common\Requests;

use App\Domains\Core\City\Models\City;
use App\Domains\Core\City\Models\Neighborhood;
use App\Http\Requests\ApiRequest;
use App\Rules\NoMultipleSpacesRule;

class ProfileRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $cityIds = City::pluck('id');
        $cityIds[] =0;
        $neighborhoodIds = Neighborhood::pluck('id');
        $neighborhoodIds[] =0;
        $authUser = auth('api')->user();
        $rules = [];
        $rules['name'] = ['required', 'regex:/^[a-zA-Z\s]+$/', 'string', 'max:255', new NoMultipleSpacesRule];
        $rules['email'] = ['required','email:dns','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i'];
        // $rules['country_code'] = ['required','string','regex:/^\+966$/'];
        // $rules['phone'] = ['required','numeric','regex:/^5\d{8}$/','unique:users,phone,'.$authUser->id.',id,deleted_at,NULL'];
        $rules['gender'] = ['nullable',"in:".implode(',', array_keys(config('constant.gender')))];
        // $rules['city_id'] = ['nullable','string'];
        // $rules['neighborhood_id'] = ['nullable','string'];
        $rules['city_id'] = ['required',
                'in:'.implode(',', $cityIds->toArray())];
        $rules['neighborhood_id'] = ['required',
                'in:'.implode(',', $neighborhoodIds->toArray())];
        $rules['date_of_birth'] = ['required','date_format:Y-m-d'];
        $rules['profile_image'] = ['nullable','image','mimes:jpeg,png,jpg','max:5120'];
        if ($authUser->hasRole('Learner')) {
            $rules['about_user'] = ['nullable','string'];
            $rules['user_interest'] = ['nullable','array'];
            $rules['user_interest.*'] = ['string'];
            $rules['learning_mode'] = ['nullable','string'];
            $rules['gender_preference'] = ['nullable','in:'.implode(',', array_keys(config('constant.gender_preference')))];
        }
        if ($authUser->hasRole('Master')) {
            $rules['biography'] = ['nullable','string'];

            $rules['specialties'] = ['sometimes','array'];
            $rules['specialties.*.specialty_id'] = ['required_with:specialties','integer','exists:specialties,id'];
            $rules['specialties.*.level_id'] = ['nullable','in:'.implode(',', array_keys(config('constant.specialty_level')))];

            $rules['certificate_files'] = ['sometimes','array'];
            $rules['certificate_files.*'] = ['file','mimes:pdf,jpg,jpeg,png','max:5120'];

            // $rules['deleted_user_certificate_files'] = ['nullable','string'];
            // $rules['deleted_user_certificate_files.*'] = ['integer', 'exists:uploads,id'];

            $rules['price_per_hour'] = ['sometimes','numeric'];
            $rules['available_day'] = ['sometimes','array','min:1'];
            $rules['available_day.*'] = ['in:'.implode(',', array_keys(config('constant.available_day')))];
            $rules['available_time'] = ['sometimes','array','min:1'];
            $rules['available_time.*'] = ['in:'.implode(',', array_keys(config('constant.available_time')))];

            $rules['experience'] = ['sometimes','string'];
            $rules['education'] = ['sometimes','array'];
            $rules['education.*'] = ['in:'.implode(',', array_keys(config('constant.education')))];
        }
        // $profileType = $this->profile_type;
        // switch ($profileType) {
        //     case 'personal_information':
        //         $rules['profile_image'] = ['nullable', 'image', 'max:'.config('constant.profile_max_size'), 'mimes:jpeg,png,jpg', 'unique:users,email,'.$authUser->id.',id,deleted_at,NULL', 'max:5120'];
        //         $rules['name'] = ['required', 'regex:/^[a-zA-Z\s]+$/', 'string', 'max:255', new NoMultipleSpacesRule];
        //         $rules['country_code'] = ['required', 'string', 'regex:/^\+966$/'];
        //         $rules['phone'] = [ 'required', 'numeric', 'regex:/^5\d{8}$/', 'unique:users,phone,'.$authUser->id.',id,deleted_at,NULL'];
        //         $rules['email'] = ['required','email:dns','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i'];
        //         $rules['gender'] = ['required', "in:".implode(',', array_keys(config('constant.gender')))];
        //         // $rules['city_id'] = ['required','exists:cities,id,deleted_at,NULL'];
        //         // $rules['neighborhood_id'] = ['required','exists:neighborhoods,id,deleted_at,NULL'];
        //         $rules['city_id'] = ['nullable','string'];
        //         $rules['neighborhood_id'] = ['nullable','string'];
        //         $rules['date_of_birth'] = ['required', 'date_format:Y-m-d'];
        //         break;
            
        //     case 'biography':
        //         $rules['biography'] = ['required'];
        //         break;
            
        //     case 'specialties':
        //         $rules['specialties'] = ['required'];
        //         break;

        //     case 'certificates':
        //         $rules['certificate_files'] = ['nullable', 'array', 'min:1'];
        //         $rules['certificate_files.*'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png','max:5120'];
        //         break; 
                
        //     case 'work_preferences':
        //             $rules['price_per_hour'] = ['required', 'numeric'];
        //             $rules['available_day'] = ['required', 'array', 'min:1'];
        //             $rules['available_day.*'] = ['required', 'in:'.implode(',', array_keys(config('constant.available_day')))];

        //             $rules['available_time'] = ['required', 'array', 'min:1'];
        //             $rules['available_time.*'] = ['required', 'in:'.implode(',', array_keys(config('constant.available_time')))];
        //         break;
            
        //     case 'experience':
        //         $rules['experience'] = ['required','string'];
            
        //         break;
                
        //     case 'education':
        //         $rules['education'] = ['required', 'array', 'min:1'];
        //         $rules['education.*'] = ['required', 'in:'.implode(',', array_keys(config('constant.education')))];
        //         break;

        //     case 'user_interest':
        //         $rules['user_interest'] = ['required','array'];
        //         $rules['user_interest.*'] = ['string']; 
        //         break;
                
        //     case 'about_user':
        //         $rules['about_user'] = ['required'];
        //         break;
                
        //     case 'learning_mode':
        //         $rules['learning_mode'] = ['required'];
        //         break;
                
        //     case 'gender_preference':
        //         $rules['gender_preference'] = ['required', 'in:'.implode(',', array_keys(config('constant.gender_preference')))];
        //         break;    

        //     default:
        //         # code...
        //         break;
        // }
        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => trans('cruds.api.name'),
            'email' => trans('cruds.api.email'),
            'gender' => trans('cruds.api.gender'),
            'city_id' => trans('cruds.api.city_id'),
            'neighborhood_id' => trans('cruds.api.neighborhood_id'),
            // 'phone' => trans('cruds.api.phone'),
            // 'country_code' => trans('cruds.api.country_code'),
            'date_of_birth' => trans('cruds.api.date_of_birth'),
            'profile_image' => trans('cruds.api.profile_image'),
            'about_user' =>trans('cruds.api.about_user'),
            'user_interest' =>trans('cruds.api.user_interest'),
            'learning_mode' =>trans('cruds.api.learning_mode'),
            'gender_preference' =>trans('cruds.api.gender_preference'),
            'biography' =>trans('cruds.api.biography'),
            'specialties' =>trans('cruds.api.specialties'),
            'specialties.*.specialty_id' => trans('cruds.api.specialties'),
            'specialties.*.level_id' => trans('cruds.api.levels'),
            'certificate_files' =>trans('cruds.api.certificate_files'),
            'price_per_hour' =>trans('cruds.api.price_per_hour'),
            'available_day' =>trans('cruds.api.available_day'),
            'available_time' =>trans('cruds.api.available_time'),
            'experience' =>trans('cruds.api.experience'),
            'education' =>trans('cruds.api.education'),
        ];
    }
}
