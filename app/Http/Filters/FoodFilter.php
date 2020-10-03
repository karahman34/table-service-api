<?php

namespace App\Http\Filters;

use Illuminate\Http\Request;

class FoodFilter extends Filter
{
    /**
     * Apply request query filter.
     *
     * @param   Request  $request
     * @param   mixed  $query
     *
     * @return  mixed   $query
     */
    public static function collection(Request $request, $query)
    {
        if ($request->has('filter')) {
            // new, random, popular, name
            $filterQuery = strtolower($request->get('filter'));

            switch ($filterQuery) {
                case 'new':
                    $query->orderBy('id', 'desc');
                break;
                
                case 'random':
                    $query->inRandomOrder();
                    break;

                case 'name':
                    $query->orderBy('name', 'asc');
                    break;

                case 'popular':
                    $query->select('foods.*')
                            ->join('detail_orders', 'foods.id', 'detail_orders.food_id')
                            ->orderByRaw('COUNT(detail_orders.food_id) DESC')
                            ->groupBy('detail_orders.food_id');
                    break;
            }
        }

        $search_able_fields = [
            'id' => 'id',
            'name' => 'name',
            'description' => 'description',
            'price' => 'price',
            'discount' => 'discount',
        ];

        $query = self::search($request, $search_able_fields, $query);

        $sort_able_fields = [
            'id' => 'id',
            'name' => 'name',
            'description' => 'description',
            'price' => 'price',
            'discount' => 'discount',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];

        $query = self::sort($request, $sort_able_fields, $query);

        return $query;
    }
}
