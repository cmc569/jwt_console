<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Orders extends Model
{
    protected $table = 'orders';
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function invoice()
    {
        return $this->hasOne(OrderInvoices::class, 'order_id', 'order_id');
    }
}
