<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;

class RoleResource extends JsonResource
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
            'lastUpdatedEditor' => new UserResource($this->whenLoaded('lastUpdatedEditor')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'accountTypes' => AccountTypeResource::collection($this->whenLoaded('accountTypes')),
            'users' => UserResource::collection($this->whenLoaded('users')),
        ]);
    }
}
