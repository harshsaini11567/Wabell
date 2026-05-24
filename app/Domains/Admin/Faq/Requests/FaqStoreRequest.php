<?php

namespace App\Domains\Admin\Faq\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;

class FaqStoreRequest extends FormRequest
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
        $faqType = $this->getFaqType();
        return [
            'question_en'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:faqs,question_en,NULL,id,deleted_at,NULL,faq_type,' . $faqType],
            'question_ar'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:faqs,question_ar,NULL,id,deleted_at,NULL,faq_type,' . $faqType],
            'answer_en'  => ['required', 'string'],
            'answer_ar'  => ['required', 'string'],
        ];
    }

    public function attributes()
    {
        return [
            /* 'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
            'description_en' => 'Description English',
            'Description_ar' => 'Description Arabic',
            'role_status'    =>  'Status' */
        ];
    }

    protected function getFaqType()
    {
        $faqType = 'customer';
        if($this->routeIs('master-faqs.*')){
            $faqType = 'master';
        } else if($this->routeIs('web-faqs.*')){
            $faqType = 'web';
        }
        return $faqType;
    }
}
