<?php

namespace App\Http\Resources;

use Storage;
use URL;

class FileResource extends Resource
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

            // special attributes
            'path' => URL::asset(Storage::url($this->path)),

            // Relationships
            'fileable' => new Resource($this->whenLoaded('fileable')),
        ]);
    }
}
