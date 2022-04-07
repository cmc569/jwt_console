<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Members extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    
}
