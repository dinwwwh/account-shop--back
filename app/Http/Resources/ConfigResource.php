<?php

namespace App\Http\Resources;

class ConfigResource extends Resource
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

            // Special attributes
            'data' => auth()->check() && auth()->user()->can('readData', $this->resource)
                ? $this->data
                : null,

            // Relationships

            // Merge when client request permissions
            $this->mergeWhen(
                auth()->check() && request('_isRequiredPermissions'),
                function () {
                    return [
                        'canReadData' => auth()->user()->can('readData', [$this->resource]),
                        'canUpdate' => auth()->user()->can('update', [$this->resource]),
                    ];
                }
            ),
        ]);
    }
}
