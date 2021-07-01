<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;
use Request;

class AccountTypeResource extends JsonResource
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

            // Relationship
            'lastUpdatedEditor' => new UserResource($this->whenLoaded('lastUpdatedEditor')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'accountInfos' => AccountInfoResource::collection($this->whenLoaded('accountInfos')),
            'accountActions' => AccountActionResource::collection($this->whenLoaded('accountActions')),
            'accountFees' => AccountFeeResource::collection($this->whenLoaded('accountFees')),
            'rolesCanUsedAccountType' => RoleResource::collection($this->whenLoaded('rolesCanUsedAccountType')),
            'game' => new GameResource($this->whenLoaded('game')),
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
        ]);
    }
}
