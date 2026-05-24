<?php

namespace App\Domains\Admin\Faq\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;

class FaqUpdateRequest extends FormRequest
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
        $faqId = $this->getFaqId();
        $faqType = $this->getFaqType();
        return [
            'question_en'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:faqs,question_en,'. $faqId .',id,deleted_at,NULL,faq_type,' . $faqType],
            'question_ar'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, 'unique:faqs,question_ar,'. $faqId .',id,deleted_at,NULL,faq_type,' . $faqType],
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
            'role_status'    => 'Status' */
        ];
    }

    protected function getFaqId(): ?int
    {
        if($this->route('master_faq')){
            $faqId = $this->route('master_faq')->id;
        } else if($this->route('web_faq')){
            $faqId = $this->route('web_faq')->id;
        } else {
            $faqId = $this->route('faq')->id;
        }
        return $faqId;
    }

    protected function getFaqType()
    {
        $faqType = 'customer';
        if($this->route('master_faq')){
            $faqType = 'master';
        } else if($this->route('web_faq')){
            $faqType = 'web';
        } else {
        }
        return $faqType;
    }

}
