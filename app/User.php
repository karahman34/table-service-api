<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Lumen\Auth\Authorizable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get validation rules.
     *
     * @param   bool   $edit
     * @param   int  $id
     *
     * @return  array $rules
     */
    public static function validationRules(bool $edit = false, $id = null)
    {
        $rules = [
            'username' => 'required|string|min:8|max:255|regex:/^[a-z]+([_a-z]+)?([0-9]+)?$/|unique:users,username',
            'password' => 'string|min:8|max:255',
        ];

        if ($edit) {
            $rules['username'] .= ',' . $id;
        }

        if (!$edit) {
            $rules['password'] = 'required|' . $rules['password'];
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

    /**
     * Relation one to many tp "Transaction" Model.
     *
     * @return  HasMany
     */
    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
}
