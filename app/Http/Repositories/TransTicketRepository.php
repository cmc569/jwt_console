<?php

namespace App\Http\Repositories;

use App\Crypto\Crypto;
// use App\Http\Models\AccunixCoupons;
use App\Http\Models\AccunixCouponChildLogs;
use App\Http\Models\Roles;
use App\Util\UtilTime;
use Exception;
use DB;

class TransTicketRepository extends BaseRepository
{
    
    public function getTickets(Int $offset, Int $limit, String $filter=null, String $start_date=null, String $end_date=null)
    {
        $tickets = AccunixCouponChildLogs::select('project_burgerking_coupon_child_logs.*', 
                    'project_burgerking_coupon_child_logs.data->transfer->origin_user as origin_user',
                    'project_burgerking_coupon_child_logs.data->transfer->target_user as target_user',
                    'project_burgerking_coupons.title',
                    'project_burgerking_coupons.end_at')
                ->join('project_burgerking_coupons', 'project_burgerking_coupons.guid', '=', 'project_burgerking_coupon_child_logs.coupon_guid');

        if (!empty($start_date) && !empty($end_date)) {
            $tickets = $tickets->whereBetween('project_burgerking_coupon_child_logs.created_at', [$start_date, $end_date]);
        }

        if (!empty($filter)) {
            $tickets = $tickets->where('project_burgerking_coupon_child_logs.data->transfer->origin_user', 'LIKE', "{$filter}%")
                ->orWhere('project_burgerking_coupon_child_logs.data->transfer->target_user', 'LIKE', "{$filter}%");
        } else {
            $tickets = $tickets->whereNotNull('project_burgerking_coupon_child_logs.data->transfer');
        }

        $total   = $tickets->count();
        $records = $tickets->offset($offset)->limit($limit)->get();

        return collect(compact('total', 'records'));
    }
}
