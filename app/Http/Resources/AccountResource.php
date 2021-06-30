<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Helpers\ArrayHelper;

class AccountResource extends JsonResource
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
            // Special attributes
            'price' => $this->calculateTemporaryPrice(),

            // Relationship
            'lastUpdatedEditor' => new UserResource($this->whenLoaded('lastUpdatedEditor')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'censor' => new UserResource($this->whenLoaded('censor')),
            'accountType' => new AccountTypeResource($this->whenLoaded('accountType')),
            'images' => AccountImageResource::collection($this->whenLoaded('images')),
            'game' => new GameResource($this->whenLoaded('game')),

            // Relationships contain pivot property
            'gameInfos' => GameInfoResource::collection($this->whenLoaded('gameInfos')),

            // Merge when auth an view sensitive infos
            $this->mergeWhen(
                auth()->check() && auth()->user()->can('viewSensitiveInfo', $this->resource),
                fn () => [
                    'accountActions' => AccountActionResource::collection($this->whenLoaded('accountActions')),
                    'accountInfos' => AccountInfoResource::collection($this->whenLoaded('accountInfos')),
                    'password' => $this->password,
                ]
            ),
        ]);
    }
}
