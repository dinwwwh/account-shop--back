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
            'maximumPrice' => $this->maximum_price,
            'minimumPrice' => $this->minimum_price,
            'maximumFee' => $this->maximum_fee,
            'minimumFee' => $this->minimum_fee,
            'percentagePrice' => $this->percentage_price,
            'accountTypeId' => $this->account_type_id,
        ];
    }
}
