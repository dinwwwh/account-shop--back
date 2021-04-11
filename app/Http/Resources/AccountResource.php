<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'price' => $this->price,
            'statusCode' => $this->status_code,
            'description' => $this->description,
            'representativeImagePath' => Storage::url($this->representative_image_path),
            'lastRoleKeyCreatorUsed' => $this->last_role_key_creator_used,

            // Relationship
            'images' => AccountImageResource::collection($this->images),
            'game' => new GameResource($this->game),
            'game' => new AccountTypeResource($this->accountType),
            'lastUpdatedEditor' => new UserResource($this->lastUpdatedEditor),
            'creator' => new UserResource($this->creator),
            'censor' => new UserResource($this->censor),
            'type' => new AccountTypeResource($this->type),

            // Relationship contain pivot
            'infos' => AccountInfoResource::collection($this->infos->keyBy->id),
            'actions' => AccountActionResource::collection($this->actions->keyBy->id),

            // Time
            'approvedAt' => $this->approved_at,
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
        ];
    }
}
