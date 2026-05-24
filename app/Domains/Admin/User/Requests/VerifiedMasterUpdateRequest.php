<?php

namespace App\Domains\Admin\User\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifiedMasterUpdateRequest extends FormRequest
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
        $adminUuid = $this->route('admin');
        return [
            'name'  => ['required', 'regex:/^[a-zA-Z\s]+$/', 'string', 'max:255', new NoMultipleSpacesRule],
            // 'email'     => ['required','email:dns','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i'],
           
            'price_per_hour' => ['required','numeric'],
            'experience' => ['required','string'],
            'tagline' => ['required', 'string'],
            'biography' => ['required', 'string'],
            'education' =>['required', 'array', 'min:1'],
            'education.*' => ['required', 'in:'.implode(',', array_keys(config('constant.education')))],

            'user_status'  => ['required', 'in:'.implode(',',array_keys(config('constant.user_status')))],
            
            'available_day' => ['required', 'array', 'min:1'],
            'available_day.*' => ['required', 'in:'.implode(',', array_keys(config('constant.available_day')))],

            'available_time' => ['required', 'array', 'min:1'],
            'available_time.*' => ['required', 'in:'.implode(',', array_keys(config('constant.available_time')))],

            'id_files' => ['nullable', 'array', 'min:1'],
            'id_files.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'certificate_files' => ['nullable', 'array', 'min:1'],
            'certificate_files.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
