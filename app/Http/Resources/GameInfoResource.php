<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;

class GameInfoResource extends JsonResource
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
            'rule' => new RuleResource($this->whenLoaded('rule')),
            'game' => new GameResource($this->whenLoaded('game')),
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
        ]);
    }
}
