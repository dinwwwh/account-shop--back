<?php

namespace App\Http\Resources;

class RoleResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [

            // Relationships
            'lastUpdatedEditor' => new UserResource($this->whenLoaded('lastUpdatedEditor')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'accountTypes' => AccountTypeResource::collection($this->whenLoaded('accountTypes')),
            'users' => UserResource::collection($this->whenLoaded('users')),
        ]);
    }
}
