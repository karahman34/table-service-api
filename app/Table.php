<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'available',
    ];

    /**
     * Validation rules.
     *
     * @param   bool   $edit
     * @param   int  $id
     *
     * @return  array
     */
    public static function validationRules(bool $edit = false, $id = null)
    {
        $rules = [
            'number' => 'required|regex:/^[1-9]+([0-9]+)?$/|unique:tables,number',
            'available' => 'nullable|in:y,n'
        ];

        if ($edit) {
            $rules['number'] .= ',' . $id;
        }

        return $rules;
    }

    /**
     * Relation one to many tp "Order" Model.
     *
     * @return  HasMany
     */
    public function orders()
    {
        return $this->hasMany('App\Order');
    }
}
