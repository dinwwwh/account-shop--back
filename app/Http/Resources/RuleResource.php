<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'placeholder' => $this->placeholder,
            'datatype' => $this->datatype,
            'required' => $this->required,
            'multiple' => $this->multiple,
            'min' => $this->min,
            'max' => $this->max,
            'values' => $this->values,
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
        ];
    }
}
