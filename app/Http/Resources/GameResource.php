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
        return array_merge(parent::toArray($request), [
            // Relationships (exclude one-one & one-many-inverse relationships)
            // 'accountTypes' => AccountTypeResource::collection($this->whenLoaded('accountTypes')),
            // 'gameInfos' => GameInfoResource::collection($this->whenLoaded('gameInfos')),
            // 'usableDiscountCodes' => DiscountCodeResource::collection($this->whenLoaded('usableDiscountCodes')),

            // Special relationship need override
            'representativeImage' => new FileResource($this->whenLoaded('representativeImage')),
        ]);
    }
}
