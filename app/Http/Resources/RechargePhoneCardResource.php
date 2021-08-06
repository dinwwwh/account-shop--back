<?php

namespace App\Http\Resources;

class RechargePhoneCardResource extends Resource
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

            // Relationships
            'approver' => UserResource::collection($this->whenLoaded('approver')),

            // Merge base ability
            $this->mergeWhen(
                auth()->check() && auth()->user()->can('read-sensitive-infos', $this->resource),
                function () {
                    return [
                        'code' => $this->code,
                    ];
                }
            ),

            // Abilities of auth
            $this->mergeWhen(
                auth()->check(),
                fn () => [
                    'canReadSensitiveInfos' => auth()->user()->can('readSensitiveInfos', $this->resource),
                    'canStartApproving' => auth()->user()->can('startApproving', $this->resource),
                    'canEndApproving' => auth()->user()->can('endApproving', $this->resource),
                ]
            )
        ]);
    }
}
