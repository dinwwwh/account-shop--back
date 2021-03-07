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
            'currentRoleNeedFillingAccountInfos'
            => AccountInfoResource::collection($this->currentRoleNeedFillingAccountInfos()),
            'currentRoleNeedPerformingAccountActions'
            => AccountActionResource::collection($this->currentRoleNeedPerformingAccountActions()),
            'lastUpdatedEditor' => new UserResource($this->lastUpdatedEditor),
            'creator' => new UserResource($this->creator),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
