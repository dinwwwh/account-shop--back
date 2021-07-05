<?php

namespace App\Http\Resources;

class GameResource extends Resource
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
            'representativeImage' => new FileResource($this->whenLoaded('representativeImage')),

            'accountTypes' => AccountTypeResource::collection($this->whenLoaded('accountTypes')),

            'gameInfos' => GameInfoResource::collection($this->whenLoaded('gameInfos')),

            'usableDiscountCodes' => DiscountCodeResource::collection($this->whenLoaded('usableDiscountCodes')),
        ]);
    }
}
