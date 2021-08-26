<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'status',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    const IS_CONNECTED = 'connecté';
    const IS_DISCONNECTED = 'déconnecté';

    const LOGIN_RULES = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];
    const REGISTER_RULES = [
        'name' => 'required|unique:users,name',
        'email' => 'required|unique:users,email',
        'password' => 'required|min:6|confirmed',
        'password_confirmation' => 'required|min:6',
        'roles' => 'required',
    ];

    public static function editRules(int $id)
    {
        return [
            'name' => 'required|unique:users,name,' . $id,
            'email' => 'required|unique:users,email,' . $id,
            'roles' => 'required',
        ];
    }

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

    public function connecter()
    {
        $this->attributes['status'] = self::IS_CONNECTED;
    }

    public function deconnecter()
    {
        $this->attributes['status'] = self::IS_DISCONNECTED;
    }
}
