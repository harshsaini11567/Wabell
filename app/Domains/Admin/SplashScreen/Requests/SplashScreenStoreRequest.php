<?php

namespace App\Domains\Admin\SplashScreen\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;

class SplashScreenStoreRequest extends FormRequest
{
    /**
     * 
     * 
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
            'title_en'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:splash_screens,title_en,NULL,id,deleted_at,NULL'],
            'title_ar'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:splash_screens,title_ar,NULL,id,deleted_at,NULL'],
            'description_en'  => ['required', 'string'],
            'description_ar'  => ['required', 'string'],
            'status'  => ['required', 'in:'.implode(',',array_keys(config('constant.splash_screen_status')))],
            'splash_image' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function attributes()
    {
        return [
            'title_en' => 'English Title',
            'title_ar' => 'Arabic Title',
            'description_en' => 'English Description',
            'description_ar' => 'Arabic Description',
            'status'    =>  'Status',
            'splash_image' => 'Splash Image',
        ];
    }
}
