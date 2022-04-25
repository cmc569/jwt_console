<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class OrderPoint extends Model
{
    protected $table = 'order_point';
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
