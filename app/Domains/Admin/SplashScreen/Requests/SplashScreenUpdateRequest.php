<?php

namespace App\Domains\Admin\SplashScreen\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SplashScreenUpdateRequest extends FormRequest
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
        $splashScreenId = $this->route('splash_screen')->id;
        return [
            'title_en'  => ['required', 'string', 'max:30', new NoMultipleSpacesRule, Rule::unique('splash_screens')->ignore($splashScreenId)->whereNull('deleted_at')],
            'title_ar'  => ['required', 'string', 'max:150', new NoMultipleSpacesRule, Rule::unique('splash_screens')->ignore($splashScreenId)->whereNull('deleted_at')],
            'description_en'  => ['required', 'string'],
            'description_ar'  => ['required', 'string'],
            'status'        => ['nullable'],
            'splash_image' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function attributes()
    {
        return [
            'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
            'description_en' => 'English Description',
            'description_ar' => 'Arabic Description',
            'role_status'    => 'Status',
            'splash_image'  => 'Splash Image'
        ];
    }

    protected function getSplashScreenId(): ?int
    {
        return optional($this->route('splash_screens'))->id;
    }

}
