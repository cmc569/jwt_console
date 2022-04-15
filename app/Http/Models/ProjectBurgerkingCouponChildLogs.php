<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class ProjectBurgerkingCouponChildLogs extends Model
{
    use SoftDeletes;

    public $timestamps = false;
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $fillable = [
        'id',
        'user_token',
        'action',
        'campaign_guid',
        'coupon_guid',
        'coupon_code',
        'data',
        'created_at',
        'deleted_at',
    ];

}
