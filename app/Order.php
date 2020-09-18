<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'table_id',
        'status'
    ];

    /**
     * Relation many to one to "User" Model.
     *
     * @return  BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Relation many to one to "Table" Model.
     *
     * @return  BelongsTo
     */
    public function table()
    {
        return $this->belongsTo('App\Table');
    }

    /**
     * Relation one to many to "DetailOrder" Model.
     *
     * @return  HasMany
     */
    public function details()
    {
        return $this->hasMany('App\DetailOrder', 'order_id');
    }
}
