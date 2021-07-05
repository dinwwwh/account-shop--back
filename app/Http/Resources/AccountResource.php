<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Storage;

class AccountResource extends Resource
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

            // Special attributes
            'price' => $this->calculateTemporaryPrice(),

            // Relationships
            'creator' => new UserResource($this->whenLoaded('creator')),
            'lastUpdatedEditor' => new UserResource($this->whenLoaded('lastUpdatedEditor')),
            'censor' => new UserResource($this->whenLoaded('censor')),
            'buyer' => new UserResource($this->whenLoaded('buyer')),

            'images' => FileResource::collection($this->whenLoaded('images')),
            'representativeImage' => new FileResource($this->whenLoaded('representativeImage')),
            'otherImages' => FileResource::collection($this->whenLoaded('otherImages')),

            'gameInfos' => GameInfoResource::collection($this->whenLoaded('gameInfos')),

            'accountType' => new AccountTypeResource($this->whenLoaded('accountType')),


            // Just merge when auth can view sensitive infos
            $this->mergeWhen(
                auth()->check() && auth()->user()->can('viewSensitiveInfo', $this->resource),
                function () {
                    return [
                        'accountActions' => AccountActionResource::collection($this->whenLoaded('accountActions')),
                        'accountInfos' => AccountInfoResource::collection($this->whenLoaded('accountInfos')),
                        'password' => $this->password,
                    ];
                }
            ),
        ]);
    }
}
