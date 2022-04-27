<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTicketOrders extends Model
{
    protected $table = 'product_ticket_orders';

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
