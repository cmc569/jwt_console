<?php

namespace App\Http\Repositories;

use App\Crypto\Crypto;
use App\Http\Models\AccunixCoupons;
use App\Http\Models\AccunixCouponChildLogs;
use App\Http\Models\ProjectBurgerkingCoupons;
use App\Http\Models\ProjectBurgerkingCouponChildLogs;
use App\Http\Models\Roles;
use App\Util\UtilTime;
use Exception;
use DB;
use Log;

class TransTicketRepository extends BaseRepository
{
    public function getTickets(Int $offset, Int $limit, String $filter=null, String $start_date=null, String $end_date=null)
    {
        $tickets = AccunixCouponChildLogs::select('project_burgerking_coupon_child_logs.created_at',
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

    public function syncPBCC()
    {
        $coupons = AccunixCouponChildLogs::whereNotNull('data->transfer');
        $last    = $this->getPBCCLast();

        if (!is_null($last)) {
            $coupons = $coupons->where('id', '>', $last->id);
        }
        $coupons = $coupons->get();
        
        if (empty($coupons)) {
            return ;
        }

        $coupons->map(function($item) {
            $data = $item->toArray();
            
            if (ProjectBurgerkingCouponChildLogs::create($data)) {
                Log::info('project_burgerking_coupon_child_logs sync ok! ('.$data['id'].')');
            } else {
                Log::error('project_burgerking_coupon_child_logs sync failed! ('.$data['id'].')');
            }
        });

        return $coupons;
    }

    public function syncPBC($coupons=null)
    {
        if (empty($coupons)) {
            return ;
        }

        $coupons->map(function($item) {
            $data = AccunixCoupons::where('guid', $item->coupon_guid)
                    ->where('campaign_guid', $item->campaign_guid)->first()->toArray();

            if (empty($data)) {
                Log::info('can not find data from accunix project_burgerking_coupons! ('.$item->campaign_guid.'、'.$item->coupon_guid.')');
            } else {
                $id = $data['id'];
                unset($data['id']);

                if (ProjectBurgerkingCoupons::updateOrCreate(['id' => $id], $data)) {
                    Log::info('project_burgerking_coupons sync ok! ('.$item->campaign_guid.'、'.$item->coupon_guid.')');
                } else {
                    Log::error('project_burgerking_coupons sync failed! ('.$item->campaign_guid.'、'.$item->coupon_guid.')');
                }
            }
        });

        return ;
    }

    private function getPBCLast()
    {
        return ProjectBurgerkingCoupons::latest()->first();
    }

    private function getPBCCLast()
    {
        return ProjectBurgerkingCouponChildLogs::latest()->first();
    }
}
