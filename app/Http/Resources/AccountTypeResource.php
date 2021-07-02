<?php

namespace App\Http\Resources;

class AccountTypeResource extends Resource
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
            'accountInfos' => AccountInfoResource::collection($this->whenLoaded('accountInfos')),
            'accountActions' => AccountActionResource::collection($this->whenLoaded('accountActions')),
            'accountFees' => AccountFeeResource::collection($this->whenLoaded('accountFees')),
            'rolesCanUsedAccountType' => RoleResource::collection($this->whenLoaded('rolesCanUsedAccountType')),
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
        ]);
    }
}
