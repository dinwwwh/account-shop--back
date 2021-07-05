<?php

namespace App\Http\Resources\Pivot;

use App\Http\Resources\Resource;

class AccountAccountActionResource extends Resource
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

            // Just merge when auth can view sensitive infos
            $this->mergeWhen(
                auth()->check() && auth()->user()->can('viewSensitiveInfo', $this->resource->account),
                function () {
                    return [
                        'isDone' => $this->is_done,
                    ];
                }
            ),
        ]);
    }
}
