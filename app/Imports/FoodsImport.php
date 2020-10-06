<?php

namespace App\Imports;

use App\Food;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FoodsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Food([
            'category_id' => $row['category_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'discount' => $row['discount'],
            'description' => $row['description'],
            'image' => $row['image'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ]);
    }
}
