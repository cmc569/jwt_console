<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class StoreValue extends Model
{
    protected $table = 'stored_value_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mobile',
        'source_system',
        'order_id',
        'systex_order_id',
        'tender_name',
        'price',
        'stored_time',
        'edited_account',
        'expired_at',
        'remark'
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
