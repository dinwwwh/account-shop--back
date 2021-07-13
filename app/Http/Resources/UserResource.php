<?php

namespace App\Http\Resources;

class UserResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(parent::getAttributes($request), [

            // Relationships
            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            'usableAccountTypes' => AccountTypeResource::collection($this->whenLoaded('usableAccountTypes')),
            'approvableAccountTypes' => AccountTypeResource::collection($this->whenLoaded('approvableAccountTypes')),

            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),

            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
        ]);
    }
}
