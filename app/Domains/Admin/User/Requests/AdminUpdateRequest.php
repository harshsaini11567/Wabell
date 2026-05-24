<?php

namespace App\Domains\Admin\User\Requests;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateRequest extends FormRequest
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
            'email'     => ['required','email:dns','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','unique:users,email,'. $adminUuid.',uuid,deleted_at,NULL'],
            'phone'     => [ 'required', 'numeric', 'regex:/^[0-9]{7,15}$/', 'unique:users,phone,'. $adminUuid.',uuid,deleted_at,NULL'],

            'user_status'  => ['required', 'in:'.implode(',',array_keys(config('constant.user_status')))],
            'roles'   => ['required','array','exists:roles,uuid'],
        ];
    }
}
