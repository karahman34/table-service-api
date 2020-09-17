<?php

namespace App\Http\Resources;

use App\Table;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TablesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->map(function (Table $table) {
            return new TableResource($table);
        });
    }
}
