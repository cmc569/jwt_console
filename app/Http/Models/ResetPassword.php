<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResetPassword extends Model
{
    use SoftDeletes;
    
    protected $table = 'reset_password';
    protected $fillable = [
        'user_id',
        'code',
        'email',
    ];
}
