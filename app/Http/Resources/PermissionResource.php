<?php

namespace App\Http\Resources;

class PermissionResource extends Resource
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

            // Relationships (exclude one-one & one-many-inverse relationships)
            'users' => UserResource::collection($this->whenLoaded('users')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ]);
    }
}
