<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;
use App\Http\Resources\Pivot\AccountHasGameInfosResource;
use App\Models\Pivot\AccountHasGameInfos;

class GameInfoResource extends Resource
{
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
            'rule' => new RuleResource($this->whenLoaded('rule')),

            'game' => new GameResource($this->whenLoaded('game')),

            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),

            'pivot' => $this->when($this->relationLoaded('pivot'), function () {
                switch (true) {
                    case $this->pivot instanceof AccountHasGameInfos:
                        return new AccountHasGameInfosResource($this->pivot);
                    default:
                        return new Resource($this->pivot);
                }
            })
        ]);
    }
}
