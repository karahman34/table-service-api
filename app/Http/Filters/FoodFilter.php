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
            // new, random, popular, name, price
            $filterQuery = strtolower($request->get('filter'));

            switch ($filterQuery) {
                case 'new':
                    $query->orderBy('id', 'desc');
                break;

                case 'price':
                    $query->orderBy('price', 'asc');
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

        if ($request->has('categories')) {
            // id1,id2,id3
            $categories = $request->get('categories');
            $query->whereIn('foods.category_id', explode(',', $categories));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $sort_able_fields = [
            'id' => 'foods.id',
            'name' => 'foods.name',
            'description' => 'foods.description',
            'price' => 'foods.price',
            'discount' => 'foods.discount',
            'created_at' => 'foods.created_at',
            'updated_at' => 'foods.updated_at',
        ];

        $query = self::sort($request, $sort_able_fields, $query);

        return $query;
    }
}
