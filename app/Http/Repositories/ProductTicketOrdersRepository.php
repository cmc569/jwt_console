<?php

namespace App\Http\Repositories;

use App\Http\Models\ProductTicketOrders;

class ProductTicketOrdersRepository extends BaseRepository
{
    public function orderList($mobile)
    {
        $list = ProductTicketOrders::select('order_id')
            ->where('mobile', $mobile)
            ->where('status', 'Y')
            ->orderBy('id', 'desc')
            ->get()->toArray();

        return $list;
    }

}
