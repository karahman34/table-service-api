<?php

namespace App\Http\Filters;

use Illuminate\Http\Request;

class TransactionFilter extends Filter
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
            'total_price' => 'total_price',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];

        $query = self::sort($request, $sort_able_fields, $query);

        return $query;
    }
}
