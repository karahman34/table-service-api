<?php

namespace App\Filters;

use Error;
use Illuminate\Http\Request;

class Filter
{
    /**
     * Apply search query.
     *
     * @param   Request  $request
     * @param   array    $search_able_fields
     * @param   mixed    $query
     *
     * @return  mixed    $query
     */
    public static function search(Request $request, array $search_able_fields, $query)
    {
        // search=vall
        $search = $request->get('search', null);

        if (is_null($search)) {
            return $query;
        }

        foreach ($search_able_fields as $search_able_field) {
            $query->orWhere($search_able_field, 'like', "%$search%");
        }

        return $query;
    }

    /**
     * Apply sort query.
     *
     * @param   Request  $request
     * @param   array    $sort_able_fields
     * @param   mixed    $query
     *
     * @return  mixed    $query
     */
    public static function sort(Request $request, array $sort_able_fields, $query)
    {
        // sort = col1,-col2
        // - = desc
        $sort = $request->get('sort', null);

        if (is_null($sort)) {
            return $query;
        }

        $sort_columns = explode(',', $sort);
        foreach ($sort_columns as $sort_column) {
            $direction = 'asc';

            if (substr($sort_column, 0, 1) == '-') {
                $direction = 'desc';
                $sort_column = substr($sort_column, 1);
            }

            if (!isset($sort_able_fields[$sort_column])) {
                continue;
            }

            $query->orderBy($sort_able_fields[$sort_column], $direction);
        }

        return $query;
    }
}
