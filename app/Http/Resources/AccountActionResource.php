<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountActionResource extends JsonResource
{
    /**
     * Indicates if the resource's collection keys should be preserved.
     *
     * @var bool
     */
    public $preserveKeys = true;

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
            'description' => $this->description,
            'videoPath' => $this->video_path,
            'required' => $this->required,
            'lastUpdatedEditor' => new UserResource($this->lastUpdatedEditor),
            'creator' => new UserResource($this->creator),
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
            'pivot' => $this->pivot,

            // Relationship
            'requiredRoles' => RoleResource::collection($this->requiredRoles),
        ];
    }
}
