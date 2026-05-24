<?php

namespace App\Domains\Admin\City\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NeighborhoodStoreRequest extends FormRequest
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
        return [
            'name_en'     => [
                'required',
                'string',
                'max:191',
                'unique:neighborhoods,name_en,NULL,id,deleted_at,NULL',
            ],
            'name_ar'     => [
                'required',
                'string',
                'max:191',
                'unique:neighborhoods,name_ar,NULL,id,deleted_at,NULL',
            ],
            'lat' => [
                'required'
            ],
            'lng' => [
                'required'
            ]
        ];
    }

    public function attributes()
    {
        return [
            'en_name' => 'English name',
            'ar_name' => 'Arabic name',
            'lat'           => 'Latitude',
            'lng'           => 'Longitude',
        ];
    }
}
