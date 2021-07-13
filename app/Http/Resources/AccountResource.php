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
            'approver' => new UserResource($this->whenLoaded('approver')),
            'buyer' => new UserResource($this->whenLoaded('buyer')),

            'latestAccountStatus' => new AccountStatusResource($this->whenLoaded('latestAccountStatus')),
            'accountStatuses' => new AccountStatusResource($this->whenLoaded('accountStatuses')),

            'images' => FileResource::collection($this->whenLoaded('images')),
            'representativeImage' => new FileResource($this->whenLoaded('representativeImage')),
            'otherImages' => FileResource::collection($this->whenLoaded('otherImages')),

            'gameInfos' => GameInfoResource::collection($this->whenLoaded('gameInfos')),

            'accountType' => new AccountTypeResource($this->whenLoaded('accountType')),

            'accountActions' => AccountActionResource::collection($this->whenLoaded('accountActions')),

            'accountInfos' => AccountInfoResource::collection($this->whenLoaded('accountInfos')),

            // Just merge when auth can READ LOGIN INFOS
            $this->mergeWhen(
                auth()->check() && auth()->user()->can('readLoginInfos', $this->resource),
                function () {
                    return [
                        'password' => $this->password,
                    ];
                }
            ),
        ]);
    }
}
