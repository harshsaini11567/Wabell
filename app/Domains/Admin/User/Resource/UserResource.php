<?php

namespace App\Domains\Admin\User\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $roleId = optional($this->roles->first())->id;
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'country_code'  => $this->country_code,
            'phone'         => $this->phone,
            'status'        => $this->user_status,
            'user_type'     => array_search($roleId, config('constant.roles')),
            'is_available'  => (bool) $this->is_available,
        ] + (!$this->is_available ? ['till_offline' => $this->till_offline] : []);
    }
}
