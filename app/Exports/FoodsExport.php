<?php

namespace App\Exports;

use App\Food;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FoodsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Id',
            'Category Id',
            'Name',
            'Price',
            'Discount',
            'Description',
            'Image',
            'Created At',
            'Updated At',
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $foods = Food::select(
            'id',
            'category_id',
            'name',
            'price',
            'discount',
            'description',
            'image',
            'created_at',
            'updated_at',
        )->get();

        return $foods;
    }
}
