<?php

namespace App\Http\Resources;

use App\Food;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FoodsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->map(function (Food $food) {
            return new FoodResource($food);
        });
    }
}
