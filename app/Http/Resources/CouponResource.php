<?php

namespace App\Http\Resources;

class CouponResource extends Resource
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

            // Sensitive properties
            $this->mergeWhen(
                auth()->check() && auth()->user()->can('readSensitiveInfos', $this->resource),
                fn () => [
                    'code' => $this->code,
                ]
            ),

            // Relationships
            'supportedGames' => GameResource::collection($this->whenLoaded('supportedGames')),
        ]);
    }
}
