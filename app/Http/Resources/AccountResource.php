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

            // Merge when client request permissions
            $this->mergeWhen(
                auth()->check() && request('_isRequiredPermissions'),
                function () {
                    return [
                        'canReadLoginInfos' => auth()->user()->can('readLoginInfos', [$this->resource]),
                        'canReadAccountInfos' => auth()->user()->can('readAccountInfos', [$this->resource]),
                        'canStartApproving' => auth()->user()->can('startApproving', [$this->resource]),
                        'canEndApproving' => auth()->user()->can('endApproving', [$this->resource]),
                        'canBuy' => auth()->user()->can('buy', [$this->resource]),
                        'canUpdateGameInfos' => auth()->user()->can('updateGameInfos', [$this->resource]),
                        'canUpdateAccountInfos' => auth()->user()->can('updateAccountInfos', [$this->resource]),
                        'canUpdateLoginInfos' => auth()->user()->can('updateLoginInfos', [$this->resource]),
                        'canUpdateImages' => auth()->user()->can('updateImages', [$this->resource]),
                        'canUpdateCost' => auth()->user()->can('updateCost', [$this->resource]),
                    ];
                }
            ),
        ]);
    }
}
