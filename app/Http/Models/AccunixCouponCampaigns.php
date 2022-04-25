<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class AccunixCouponCampaigns extends Model
{
    protected $connection = 'accunix_db';
    protected $table = 'project_burgerking_coupon_campaigns';

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
