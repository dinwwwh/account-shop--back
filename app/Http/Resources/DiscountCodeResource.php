<?php

namespace App\Http\Resources;

class DiscountCodeResource extends Resource
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

            // Relationship
            'lastUpdatedEditor' => new UserResource($this->whenLoaded('lastUpdatedEditor')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'buyers' => UserResource::collection($this->whenLoaded('buyers')),
            'supportedGames' => GameResource::collection($this->whenLoaded('supportedGames')),
        ]);
    }
}
