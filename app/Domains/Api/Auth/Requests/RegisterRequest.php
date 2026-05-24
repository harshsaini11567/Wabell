<?php

namespace App\Domains\Api\Auth\Requests;

use App\Domains\Core\City\Models\City;
use App\Domains\Core\City\Models\Neighborhood;
use App\Http\Requests\ApiRequest;
use App\Rules\NoMultipleSpacesRule;
use Illuminate\Validation\Rule;
class RegisterRequest extends ApiRequest
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
        $rules = [
            'register_type'     => ['required', 'in:normal,facebook,google,apple'],
            'user_type'         => ['required','in:customer,master'],

            'name'              => ['required', 'regex:/^[a-zA-Z\s]+$/', 'string', 'max:255', new NoMultipleSpacesRule],
            'email'             => ['required','email:dns','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','unique:users,email,NULL,id,deleted_at,NULL'],

            'gender'            => ['nullable', 'in:'.implode(',', array_keys(config('constant.gender')))],

            // 'city_id'           => ['required','string'],
            
            // 'neighborhood_id'   => ['required','string'],
            
            
            'master_gender_preference' => ['nullable', 'in:'.implode(',', array_keys(config('constant.gender_preference')))],
            // 'neighborhood_id'    => ['required','exists:neighborhoods,id,deleted_at,NULL'],
            // 'neighborhood_id'    => ['required',Rule::exists('neighborhoods', 'id')->whereNull('deleted_at')->whereNot('id', 0)],
            // 'country_code' => ['required', 'string', 'regex:/^\+966$/'],
            // 'phone'     => [ 'required', 'numeric', 'regex:/^5\d{8}$/', 'unique:users,phone,NULL,id,deleted_at,NULL'],            

            // 'city_id'    => ['required','exists:cities,id,deleted_at,NULL'],
            'city_id' => [
                'required',
                'in:'.implode(',', $cityIds->toArray()),
            ],
            'neighborhood_id' => [
                'required',
                'in:'.implode(',', $neighborhoodIds->toArray()),
            ],
            // 'city_id'    => ['required',Rule::exists('cities', 'id')->whereNull('deleted_at')->whereNot('id', 0)],

        ];
        if($this->register_type == 'normal'){
            $rules['password']  = ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/'];
        }

        if(in_array($this->register_type, ['facebook', 'google', 'apple'])){
            $rules['social_user_id']  = ['required'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'password.regex' => trans('validation.password.regex',['attribute'=> trans('cruds.api.password')]),
        ];
    }

    public function attributes()
    {
        return [
            'register_type' => trans('cruds.api.register_type'),
            'name' => trans('cruds.api.name'),
            'email' => trans('cruds.api.email'),
            'user_type' => trans('cruds.api.user_type'),
            'master_gender_preference' => trans('cruds.api.master_gender_preference'),
            'password' => trans('cruds.api.password'),
            'social_user_id' => trans('cruds.api.social_user_id'),
            'city_id' => trans('cruds.api.city_id'),
            'neighborhood_id' => trans('cruds.api.neighborhood_id'),
            'gender' => trans('cruds.api.gender'),
        ];
    }
}
