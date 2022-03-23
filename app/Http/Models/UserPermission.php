<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class UserPermission extends Model
{
    protected $table = 'user_permission';
    protected $fillable = [
        'user_id',
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
