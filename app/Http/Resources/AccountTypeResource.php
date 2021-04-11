<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        // Need to middleware ware in here
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            // Relationship
            'accountInfosThatRoleNeedFilling'
            => AccountInfoResource::collection($this->accountInfosThatRoleNeedFilling()),
            'accountInfos' => AccountInfoResource::collection($this->accountInfos),
            'accountInfosThatRoleNeedPerforming'
            => AccountActionResource::collection($this->accountInfosThatRoleNeedPerforming()),
            'accountActions' => AccountActionResource::collection($this->accountActions),
            'lastUpdatedEditor' => new UserResource($this->lastUpdatedEditor),
            'creator' => new UserResource($this->creator),
            'rolesCanUsedAccountType' => RoleResource::collection($this->rolesCanUsedAccountType),

            // Time
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
        ];
    }
}
