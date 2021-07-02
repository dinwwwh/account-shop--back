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
        // Especially parent::toArray() method used toArray method in model
        // This method generate unhidden attributes and loaded relationship one-one, one-many-inverse
        // Therefore all-resources don't need declare relationship one-one and one-many-inverse
        // Unless you want to show hidden attributes in that relationships
        return  ArrayHelper::convertArrayKeyToCamelCase(parent::toArray($request), -1);
    }

    static function withLoadRelationships($resource)
    {
        // Get required model relationships form config
        $requiredRelationships = config('request.requiredModelRelationships', []);
        // Load all required relationships
        $resource->load($requiredRelationships);

        return new static($resource);
    }

    static function withLoadMissingRelationships($resource)
    {
        // Get required model relationships form config
        $requiredRelationships = config('request.requiredModelRelationships', []);
        // Just load missing required relationships
        $resource->load($requiredRelationships);

        return new static($resource);
    }
}
