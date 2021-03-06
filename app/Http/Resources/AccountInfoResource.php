<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountInfoResource extends JsonResource
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
            'description' => $this->description,
            'rule' => new RuleResource($this->rule),
            'lastUpdatedEditor' => new UserResource($this->lastUpdatedEditor),
            'Creator' => new UserResource($this->Creator),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
