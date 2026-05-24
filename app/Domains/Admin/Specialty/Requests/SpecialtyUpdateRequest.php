<?php

namespace App\Domains\Admin\Specialty\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;

class SpecialtyUpdateRequest extends FormRequest
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
        $specialtyUuid = $this->route('specialty');
        return [
            'name_en'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:specialties,name_en,'. $specialtyUuid.',uuid,deleted_at,NULL'],
            'name_ar'  => ['required', 'unique:specialties,name_ar,'. $specialtyUuid.',uuid,deleted_at,NULL'],
            'specialty_icon' => 'nullable|image|mimes:jpeg,png,jpg|max:5120|dimensions:width=150,height=150',
        ];
    }

    public function attributes()
    {
        return [];
    }
}
