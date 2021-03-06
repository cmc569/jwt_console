<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Model {
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'account',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'name' => 'string',
        'account' => 'string',
        'email' => 'string',
        'role_id' => 'integer',
        'created_at' => 'string',
        'updated_at' => 'string',
        'password' => 'string',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function role()
    {
        return $this->hasOne(Roles::class, 'id', 'role_id');
    }

    public function permissions()
    {
        return $this->hasManyThrough(Permissions::class, RoleHasPermission::class, 'role_id', 'id', 'role_id', 'permission_id');
    }

}
