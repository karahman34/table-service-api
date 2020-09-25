<?php

namespace App\Http\Filters;

use Illuminate\Http\Request;

class TableFilter extends Filter
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
        $sort_able_fields = [
            'id' => 'id',
            'number' => 'number',
            'available' => 'available',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ];

        $query = self::sort($request, $sort_able_fields, $query);

        $special_queries = [
            'number' => 'number',
            'available' => 'available',
        ];

        foreach ($special_queries as $special_query => $column) {
            $q = $request->get($special_query, null);
            
            if (!is_null($q)) {
                $query->where($column, $q);
            }
        }

        return $query;
    }
}
