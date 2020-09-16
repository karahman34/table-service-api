<?php

namespace App\Http\Filters;

use Illuminate\Http\Request;

class CategoryFilter extends Filter
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
        $search_able_fields = [
            'name' => 'name'
        ];

        $query = self::search($request, $search_able_fields, $query);

        $sort_able_fields = [
            'id' => 'id',
            'name' => 'name',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ];

        $query = self::sort($request, $sort_able_fields, $query);

        return $query;
    }
}
