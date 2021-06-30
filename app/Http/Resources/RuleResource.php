<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;

class RuleResource extends JsonResource
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

            // relationship
            'requiredRoles' => RoleResource::collection($this->whenLoaded('requiredRoles')),
        ]);
    }
}
