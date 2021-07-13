<?php

namespace App\Http\Resources;

class RuleResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(parent::getAttributes($request), [

            // Relationships
            'requiredUsers' => UserResource::collection($this->whenLoaded('requiredUsers')),
            'unrequiredUsers' => UserResource::collection($this->whenLoaded('unrequiredUsers')),
        ]);
    }
}
