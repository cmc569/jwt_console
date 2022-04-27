<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTicketVoid extends Model
{
    protected $table = 'product_ticket_void';
    protected $fillable = [
        'mobile',
        'source_system',
        'order_id',
        'old_order_id',
        'product_id',
        'tender_name',
        'voidnum',
        'voidamount',
        'remark'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
