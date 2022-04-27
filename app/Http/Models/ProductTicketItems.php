<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTicketItems extends Model
{
    protected $table = 'product_ticket_items';

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
