<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class AccunixCoupons extends Model
{
    protected $connection = 'accunix';
    protected $table = 'project_burgerking_coupons';
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
