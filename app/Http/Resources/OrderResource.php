<?php

namespace App\Http\Resources;

use App\DetailOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'customer' => [
                'id' => $this->customer->id,
                'username' => $this->customer->username,
            ],
            'table' => [
                'id' => $this->table->id,
                'number' => $this->table->number,
            ],
            'details' => $this->details->map(function (DetailOrder $detailOrder) {
                return [
                    'id' => $detailOrder->id,
                    'food' => new FoodResource($detailOrder->food),
                    'qty' => $detailOrder->qty,
                    'served_at' => is_null($detailOrder->served_at) ? null : $detailOrder->served_at->toDateTimeString(),
                    'created_at' => $detailOrder->created_at->toDateTimeString(),
                    'updated_at' => $detailOrder->updated_at->toDateTimeString(),
                ];
            }),
        ];
    }
}
