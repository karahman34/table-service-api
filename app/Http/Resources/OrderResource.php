<?php

namespace App\Http\Resources;

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
            'details_complete' => $this->details_complete,
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
            'details' => new DetailsOrderCollection($this->details),
        ];
    }
}
