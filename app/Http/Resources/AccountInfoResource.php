<?php

namespace App\Http\Resources;

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
        return array_merge(parent::toArray($request), [

            // Relationships
            'creator' => new UserResource($this->whenLoaded('creator')),
            'lastUpdatedEditor' => new UserResource($this->whenLoaded('lastUpdatedEditor')),
            'rule' => new RuleResource($this->whenLoaded('rule')),
            'accountType' => new RuleResource($this->whenLoaded('accountType')),
            'accounts' => new RuleResource($this->whenLoaded('accounts')),
        ]);
    }
}
