<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailOrder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'food_id',
        'qty',
        'served_at',
    ];

    /**
    * The attributes that should be cast.
    *
    * @var array
    */
    protected $casts = [
        'served_at' => 'date'
    ];

    /**
     * Relation many to one to "Order" Model.
     *
     * @return  BelongsTo
     */
    public function order()
    {
        return $this->belongsTo('App\Order', 'order_id');
    }

    /**
     * Relation many to one to "Food" Model.
     *
     * @return  BelongsTo
     */
    public function food()
    {
        return $this->belongsTo('App\Food', 'food_id');
    }
}
