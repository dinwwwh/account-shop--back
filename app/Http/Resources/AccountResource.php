<?php

namespace App\Http\Resources;

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
            'order' => $this->order,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'rule' => new RuleResource($this->rule),
            'lastUpdatedEditor' => new UserResource($this->order),
            'creator' => new UserResource($this->creator),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
