<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountFeeResource extends JsonResource
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
            'maximumCost' => $this->maximum_cost,
            'minimumCost' => $this->minimum_cost,
            'maximumFee' => $this->maximum_fee,
            'minimumFee' => $this->minimum_fee,
            'percentageCost' => $this->percentage_cost,
            'directFee' => $this->direct_fee,
            'accountTypeId' => $this->account_type_id,
        ];
    }
}
