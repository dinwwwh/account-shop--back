<?php

namespace App\Http\Resources\Pivot;

use App\Http\Resources\Resource;

class AccountHasGameInfosResource extends Resource
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
            //
        ]);
    }
}
