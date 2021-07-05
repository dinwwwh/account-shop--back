<?php

namespace App\Http\Resources;

class AuditResource extends Resource
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
            'auditable' => new Resource($this->whenLoaded('auditable')),

            'user' => new UserResource($this->whenLoaded('user')),
        ]);
    }
}
