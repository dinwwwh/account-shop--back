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
            'status' => $this->status,
            'description' => $this->description,
            'representativeImagePath' => Storage::url($this->representative_image_path),

            // Relationship
            'images' => AccountImageResource::collection($this->images),
            'game' => new GameResource($this->game),
            'game' => new AccountTypeResource($this->accountType),
            'lastUpdatedEditor' => new UserResource($this->lastUpdatedEditor),
            'creator' => new UserResource($this->creator),
            'censor' => new UserResource($this->censor),

            // Time
            'approvedAt' => $this->approved_at,
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
        ];
    }
}
