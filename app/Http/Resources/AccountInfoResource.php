<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountInfoResource extends JsonResource
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
            'rule' => new RuleResource($this->rule),
            'lastUpdatedEditor' => new UserResource($this->lastUpdatedEditor),
            'creator' => new UserResource($this->Creator),
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
            'pivot' => $this->pivot,

            // Relationship
            'rolesNeedFilling' => RoleResource::collection($this->rolesNeedFilling),
        ];
    }
}
