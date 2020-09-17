<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function user()
    {
        return $this->belongsTo('App\User');
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
}
