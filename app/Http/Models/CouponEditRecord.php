<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class CouponEditRecord extends Model
{
    protected $table = 'coupon_edit_record';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mobile',
        'user_token',
        'type',
        'coupon_guid',
        'edited_account',
        'remark'
    ];


    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
