<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PermissionResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'goldCoin' => $this->gold_coin,
            'silverCoin' => $this->silver_coin,
            // 'permissions' => PermissionResource::collection($this->getAllPermissions()),
        ];
    }
}
