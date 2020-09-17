<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Food extends Model
{
    protected $table = 'foods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'discount',
        'image'
    ];

    /**
     * Image folder name.
     *
     * @var string
     */
    public static $image_folder = 'foods';

    /**
     * Validation rules.
     *
     * @return  array
     */
    public static function validationRules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric',
            'discount' => 'required|numeric|digits_between:1,3',
            'image' => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'category_id' => 'required|regex:/^\d+$/'
        ];
    }

    /**
     * Relation many to one to "Category" Model.
     *
     * @return  BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Category');
    }
}
