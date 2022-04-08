<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class OrderInvoices extends Model
{
    protected $table = 'order_invoice';
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function order()
    {
        return $this->hasOne(Orders::class, 'order_id', 'order_id');
    }
}
