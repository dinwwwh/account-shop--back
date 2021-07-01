<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;

class Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Convert keys of all-layers to camel case
        return  ArrayHelper::convertArrayKeyToCamelCase(parent::toArray($request), -1);
    }

    static function withLoadRelationships($resource)
    {
        // Get required model relationships form config
        $requiredRelationships = config('request.requiredModelRelationships', []);
        // Load missing required relationships
        $resource->loadMissing($requiredRelationships);

        return new static($resource);
    }
}
