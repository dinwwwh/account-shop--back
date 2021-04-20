<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountCodeResource extends JsonResource
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
            'discountCode' => $this->discount_code,
            'price' => $this->price,
            'buyable' => $this->buyable,
            'name' => $this->name,
            'description' => $this->description,

            'maximumPrice' => $this->maximum_price,
            'minimumPrice' => $this->minimum_price,
            'maximumDiscount' => $this->maximum_discount,
            'minimumDiscount' => $this->minimum_discount,
            'percentageDiscount' => $this->percentage_discount,
            'directDiscount' => $this->direct_discount,
            'usableAt' => $this->usable_at,
            'usableClosedAt' => $this->usable_closed_at,
            'offeredAt' => $this->offered_at,
            'offerClosedAt' => $this->offer_closed_at,
        ];
    }
}
