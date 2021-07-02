<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;

class GameInfoResource extends Resource
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

            // Relationships (exclude one-one & one-many-inverse relationships)
            'accounts' => AccountResource::collection($this->whenLoaded('accounts')),
        ]);
    }
}
