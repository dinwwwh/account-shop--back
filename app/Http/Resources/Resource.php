<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\ArrayHelper;
use Illuminate\Database\Eloquent\Collection;

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
        return  ArrayHelper::convertArrayKeysToCamelCase(parent::toArray($request), -1);
    }

    /**
     * Return all unhidden-attributes of resource
     * (exclude relationships)
     * (include pivot relationship)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function getAttributes($request)
    {
        if (is_null($this->resource)) {
            return [];
        }

        $attributes = is_array($this->resource)
            ? $this->resource
            : $this->resource->attributesToArray();

        // Convert keys of all-layers to camel case
        return  array_merge(
            ArrayHelper::convertArrayKeysToCamelCase(array_merge($attributes), -1),
            [
                'creator' => new UserResource($this->whenLoaded('creator')),
                'latestUpdater' => new UserResource($this->whenLoaded('latestUpdater')),
                'pivot' => new Resource($this->whenLoaded('pivot')),
            ]
        );
    }


    /**
     * Instantiate a resource with load required model relationships
     *
     * @param any $resource
     * @return any
     */
    static function withLoadRelationships($resource)
    {
        // Get required model relationships form config
        $requiredRelationships = config('request.requiredModelRelationships', []);
        // Load all required relationships
        $resource->load($requiredRelationships);

        return $resource instanceof Collection
            ? static::collection($resource)
            : new static($resource);
    }

    /**
     * Instantiate a resource with load missing required model relationships
     *
     * @param any $resource
     * @return any
     */
    static function withLoadMissingRelationships($resource)
    {
        // Get required model relationships form config
        $requiredRelationships = config('request.requiredModelRelationships', []);
        // Just load missing required relationships
        $resource->loadMissing($requiredRelationships);

        return $resource instanceof Collection
            ? static::collection($resource)
            : new static($resource);
    }
}
