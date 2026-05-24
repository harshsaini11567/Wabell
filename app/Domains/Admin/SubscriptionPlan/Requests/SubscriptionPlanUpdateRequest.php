<?php

namespace App\Domains\Admin\SubscriptionPlan\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionPlanUpdateRequest extends FormRequest
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
        // dd(route('subscription-plans'));
        $subscriptionPlanId = $this->route('subscription_plan');
        return [
            'name_en'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, Rule::unique('plans')->ignore($subscriptionPlanId)->whereNull('deleted_at')],
            'name_ar'  => ['required', 'string', 'max:255', new NoMultipleSpacesRule, Rule::unique('plans')->ignore($subscriptionPlanId)->whereNull('deleted_at')],
            'monthly_price'  => ['required', 'numeric'],
            'yearly_price'  => ['required', 'numeric'],
            'features_en'  => ['required'],
            'features_ar'  => ['required'],
            'plan_image' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function attributes()
    {
        return [
            'name_en' => 'English Name',
            'name_ar'   => 'Arabic Name',
            'monthly_price' => 'Monthly Price',
            'yearly_price' => 'Yearly Price',
            'features_en' => 'English Features',
            'features_ar' => 'Arabic Features',
        ];
    }

}
