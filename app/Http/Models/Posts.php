<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Posts extends Model
{
    use SoftDeletes;
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $fillable = [
        'post_type',
        'content',
        'last_modify',
    ];

    public function modified_by()
    {
        return $this->hasOne(Users::class, 'id', 'last_modify');
    }
}
