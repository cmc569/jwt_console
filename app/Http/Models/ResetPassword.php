<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    protected $table = 'reset_password';
    protected $fillable = [
        'code',
        'email',
    ];
}
