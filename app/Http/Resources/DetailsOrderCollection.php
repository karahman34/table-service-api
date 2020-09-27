<?php

namespace App\Http\Resources;

use App\DetailOrder;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DetailsOrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->map(function (DetailOrder $detailOrder) {
            return [
                'id' => $detailOrder->id,
                'food' => new FoodResource($detailOrder->food),
                'qty' => $detailOrder->qty,
                'served_at' => is_null($detailOrder->served_at) ? null : $detailOrder->served_at->toDateTimeString(),
                'created_at' => $detailOrder->created_at->toDateTimeString(),
                'updated_at' => $detailOrder->updated_at->toDateTimeString(),
            ];
        });
    }
}
