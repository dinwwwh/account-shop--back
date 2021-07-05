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
        return array_merge(parent::getAttributes($request), [

            // Relationships
            'requiredRoles' => RoleResource::collection($this->whenLoaded('requiredRoles')),

            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),

            'accountType' => new AccountTypeResource($this->whenLoaded('accountType')),
        ]);
    }
}
