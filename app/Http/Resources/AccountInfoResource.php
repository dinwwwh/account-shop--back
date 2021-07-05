<?php

namespace App\Http\Resources;

use App\PivotModels\AccountAccountInfo;
use App\Http\Resources\Pivot\AccountAccountInfoResource;

class AccountInfoResource extends Resource
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
            'accountType' => new AccountTypeResource($this->whenLoaded('accountType')),

            'rule' => new RuleResource($this->whenLoaded('rule')),

            'accounts' => new AccountResource($this->whenLoaded('accounts')),

            'pivot' => $this->when($this->relationLoaded('pivot'), function () {
                switch (true) {
                    case $this->pivot instanceof AccountAccountInfo:
                        return new AccountAccountInfoResource($this->pivot);
                    default:
                        return new Resource($this->pivot);
                }
            })
        ]);
    }
}
