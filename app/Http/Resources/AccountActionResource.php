<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;

class AccountActionResource extends JsonResource
{
    /**
     * Indicates if the resource's collection keys should be preserved.
     *
     * @var bool
     */
    public $preserveKeys = true;

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
            'accountType' => new UserResource($this->whenLoaded('accountType')),
            'requiredRoles' => RoleResource::collection($this->whenLoaded('requiredRoles')),
            'accounts' => RoleResource::collection($this->whenLoaded('accounts')),
        ]);
    }
}
