<?php

namespace App\Domains\Admin\Role\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;

class RoleUpdateRequest extends FormRequest
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
            'name_en'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:roles,name_en,'.$this->role->id.',id,deleted_at,NULL'],
            'name_ar'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:roles,name_ar,'.$this->role->id.',id,deleted_at,NULL'],
            'description_en'  => ['required', 'string'],
            'description_ar'  => ['required', 'string'],
            'role_status'  => ['required','in:'.implode(',',array_keys(config('constant.status')))],
        ];
    }

    public function attributes()
    {
        return [
            'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
            'description_en' => 'Description English',
            'Description_ar' => 'Description Arabic',
            'role_status'    => 'Status'
        ];
    }
}
