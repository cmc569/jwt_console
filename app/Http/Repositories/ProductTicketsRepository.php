<?php

namespace App\Http\Repositories;

use App\Http\Models\ProductTickets;

class ProductTicketsRepository extends BaseRepository
{
    public function couponList($mobile, $orderId)
    {
        $list = ProductTickets::select('coupon_no','amount')
            ->leftJoin('product_ticket_items', 'product_tickets.product_id', '=', 'product_ticket_items.product_id')
            ->where('mobile', $mobile)
            ->where('product_ticket_order_id', $orderId)
            ->where('product_tickets.status', 'Y')
            ->where(function ($query){
                $query->where('ticket_status', '0');
                $query->orwhere('ticket_status', '1');
            })
            ->orderBy('product_tickets.id', 'desc')
            ->get();

        return $list;
    }

    public function updateTicketsStatus($mobile, $coupons)
    {
        try{
            foreach ($coupons as $coupon) {
                $ticket = ProductTickets::where('mobile', $mobile)
                    ->where('coupon_no', $coupon)
                    ->first();
                $ticket->ticket_status = '3';
                $ticket->status = 'N';
                $ticket->save();
            }
            return true;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

    }

}
