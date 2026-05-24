<?php

namespace App\Domains\Admin\Setting\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function rules()
    {
        $rules = [];
        $rules['setting_type'] = ['required', 'in:site,content,support,social_link'];
        if($this->setting_type == 'site'){
            $rules['site_title'] = ['required'];
            $rules['site_logo'] = ['image', 'mimes:jpeg,png,jpg,PNG,JPG', 'max:5120'];
            $rules['favicon'] = ['image', 'mimes:jpeg,png,jpg,PNG,JPG', 'max:5120'];
        } else if($this->setting_type == 'content'){
            $rules['about_us_en'] = ['required'];
            $rules['about_us_ar'] = ['required'];
            $rules['term_condition_en'] = ['required'];
            $rules['term_condition_ar'] = ['required'];
            $rules['privacy_policy_en'] = ['required'];
            $rules['privacy_policy_ar'] = ['required'];
            $rules['learner_welcome_video'] = ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm', 'max:51200'];
            $rules['master_welcome_video'] = ['nullable', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm', 'max:51200'];
        }
        else if($this->setting_type == 'support'){
            $rules['support_email'] = ['required','email:dns'];
            $rules['support_contact'] = ['required'];
            $rules['support_location_en'] = ['required'];
            $rules['support_location_ar'] = ['required'];
        }
        else if($this->setting_type == 'social_link'){
            $rules['social_link_youtube'] = ['nullable'];
            $rules['social_link_tiktok'] = ['nullable'];
            $rules['social_link_instagram'] = ['nullable'];
            $rules['social_link_linkedin'] = ['nullable'];
            $rules['social_link_snapchat'] = ['nullable'];
            $rules['social_link_twitter'] = ['nullable'];
            $rules['social_link_facebook'] = ['nullable'];
            $rules['social_link_whatsapp'] = ['nullable'];
        }

        return $rules;
    }
    
    public function messages()
    {
        return [
            'site_logo.image' => 'The site logo must be an image.',
            'site_logo.mimes' => 'The site logo must be jpeg,png,jpg,PNG,JPG.',
            'favicon.image' => 'The favicon must be an image.',
            'favicon.mimes' => 'The favicon must be jpeg,png,jpg,PNG,JPG.',
        ];
    }

}
?>