<?php

namespace App\Http\Resources;

class AccountActionResource extends Resource
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
        return array_merge(parent::toArray($request), [

            // Relationships
            'lastUpdatedEditor' => new UserResource($this->whenLoaded('lastUpdatedEditor')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'accountType' => new UserResource($this->whenLoaded('accountType')),
            'requiredRoles' => RoleResource::collection($this->whenLoaded('requiredRoles')),
            'accounts' => RoleResource::collection($this->whenLoaded('accounts')),
        ]);
    }
}
