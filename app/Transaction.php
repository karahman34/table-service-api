<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'total_price',
    ];

    /**
     * Relation many to one to "Order" Model.
     *
     * @return  BelongsTo
     */
    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    /**
     * Relation many to one to "User" Model.
     *
     * @return  BelongsTo
     */
    public function cashier()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
