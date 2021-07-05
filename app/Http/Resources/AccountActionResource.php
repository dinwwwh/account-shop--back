<?php

namespace App\Http\Resources;

use App\Http\Resources\Pivot\AccountAccountActionResource;
use App\PivotModels\AccountAccountAction;

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

            'pivot' => $this->when($this->relationLoaded('pivot'), function () {
                switch (true) {
                    case $this->pivot instanceof AccountAccountAction:
                        return new AccountAccountActionResource($this->pivot);
                    default:
                        return new Resource($this->pivot);
                }
            })
        ]);
    }
}
