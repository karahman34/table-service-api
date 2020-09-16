<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get validation rules.
     *
     * @param   bool   $edit
     * @param   int    $id
     *
     * @return  array   $rules
     */
    public static function validationRules(bool $edit = false, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:categories,name'
        ];

        if ($edit) {
            $rules['name'] .= ',' . $id;
        }

        return $rules;
    }
}
