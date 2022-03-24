<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class RoleHasPermission extends Model
{
    protected $table = 'role_has_permission';
    protected $fillable = [
        'role_id',
        'permission_id',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function permissions()
    {
        return $this->hasOne(Permissions::class, 'id', 'permission_id');
    }
}
