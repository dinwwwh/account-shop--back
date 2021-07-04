<?php

namespace App\Http\Resources;

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
        return array_merge(parent::toArray($request), [

            // special attributes
            'path' => URL::asset($this->path),
        ]);
    }
}
