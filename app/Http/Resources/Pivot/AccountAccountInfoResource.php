<?php

namespace App\Http\Resources\Pivot;

use App\Http\Resources\Resource;

class AccountAccountInfoResource extends Resource
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
                auth()->check() && auth()->user()->can('readAccountInfos', $this->resource->account),
                function () {
                    return [
                        'values' => $this->values,
                    ];
                }
            ),
        ]);
    }
}
