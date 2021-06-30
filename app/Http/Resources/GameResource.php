<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Helpers\ArrayHelper;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $baseProperties = ArrayHelper::convertToCamelKey(parent::toArray($request));

        return array_merge($baseProperties, [

            // Relationship
            'lastUpdatedEditor' => new UserResource($this->whenLoaded('lastUpdatedEditor')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'accountTypes' => AccountTypeResource::collection($this->whenLoaded('accountTypes')),
            'gameInfos' => GameInfoResource::collection($this->whenLoaded('gameInfos')),
            'usableDiscountCodes' => DiscountCodeResource::collection($this->whenLoaded('usableDiscountCodes')),
        ]);
    }
}
