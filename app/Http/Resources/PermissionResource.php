<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $baseProperties = ArrayHelper::convertToCamelKey(parent::toArray($request), 2);

        return array_merge($baseProperties, [

            // Relationships
            'users' => UserResource::collection($this->whenLoaded('users')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ]);
    }
}
