<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class GameResource extends JsonResource
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
            'order' => $this->order,
            'name' => $this->name,
            'slug' => $this->slug,
            'publisherName' => $this->publisher_name,
            'imagePath' => URL::asset(Storage::url($this->image_path)),
            'accountTypesThatCurrentUserCanUse' => AccountTypeResource::collection($this->getAccountTypesThatCurrentUserCanUse()),
            'accountTypes' => AccountTypeResource::collection($this->accountTypes),
            'lastUpdatedEditor' => new UserResource($this->lastUpdatedEditor),
            'creator' => new UserResource($this->creator),
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,

            // Relationship
            'rolesCanCreatedGame' => RoleResource::collection($this->rolesCanCreatedGame),
        ];
    }
}
