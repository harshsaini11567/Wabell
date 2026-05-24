<?php

namespace App\Domains\Api\Common\Requests;

use App\Http\Requests\ApiRequest;
use App\Rules\NoMultipleSpacesRule;

class PhoneNumberRequest extends ApiRequest
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
        $authUser = auth('api')->user();
        $rules = [];
        $rules['country_code'] = ['required','string','regex:/^\+966$/'];
        $rules['phone'] = ['required','numeric','regex:/^5\d{8}$/','unique:users,phone,'.$authUser->id.',id,deleted_at,NULL'];
        return $rules;
    }

    public function attributes()
    {
        return [
            'phone' => trans('cruds.api.phone'),
            'country_code' => trans('cruds.api.country_code'),
        ];
    }
}
