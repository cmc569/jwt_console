<?php

namespace App\Http\Repositories;

use App\Http\Models\Members;
use App\Http\Models\CsvOutput;
use App\Http\Models\Orders;
use App\Http\Models\OrderInvoices;
use App\Util\UtilTime;
use Exception;
use DB;

class MembersRepository extends BaseRepository
{
    /**
     * 
     */
    public function getMembers(
        String $user_token = null,  //會員 user token
        String $filter = null,      //過濾值
        String $start = null,       //加入的開始時間
        String $end = null,         //加入的結束時間
        Int $offset = null,         //取值開始位置
        Int $limit = null,          //每次顯示筆數
        Bool $csv = null            //是否 csv 下載
    )
    {
        if (is_null($user_token)) {
            $members = Members::where('status', 'Y');
            if (!empty($filter)) {
                $members = $members->Where('name', 'LIKE', $filter)
                                ->orWhere('user_token', 'LIKE', $filter)
                                ->orWhere('email', 'LIKE', $filter)
                                ->orWhere('mobile', 'LIKE', $filter);
            }

            if (preg_match("/^\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}$/", $start) &&
                    preg_match("/^\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}$/", $end)) {

                $members = $members->where('created_at', '>=', $start)->where('created_at', '<=', $end);
            }

            $total = $members->count();
            if (empty($csv)) {
                $members = $members->offset($offset)->limit($limit);
            }

            $data = [
                'total'     => $total,
                'records'   => $members->get(),
            ];

            return $data;

        } else {
            return $this->getMemberByUserToken($user_token);
        }

    }

    /**
     * 
     */
    public function csvRegister(Int $user_id, String $json)
    {
        return CsvOutput::create([
            'user_id'   => $user_id,
            'rules'     => $json,
        ]);
    }

    /**
     * 
     */
    public function getCsvJob(String $status='N')
    {
        return CsvOutput::with('user')->where('process_status', $status)->get();
    }

    /**
     * 
     */
    public function updateCsvJob(Array $ids, String $status, String $fh=null, String $date=null)
    {
        return CsvOutput::whereIn('id', $ids)->update(['process_status' => $status, 'csv' => $fh, 'sent' => $date]);
    }

    /**
     * 
     */
    public static function getMemberById(Int $id)
    {
        return Members::with('lastModify')->find($id);
    }

    /**
     * 
     */
    public static function getMemberByMobile(String $mobile)
    {
        return Members::with('lastModify')->where('mobile', $mobile)->first();
    }

    /**
     * 
     */
    public static function getMemberByUserToken(String $user_token)
    {
        return Members::with('lastModify')->where('user_token', $user_token)->first();
    }

    /**
     * 
     */
    public function memberUpdateBirthday(Int $member, String $birthday)
    {
        return Members::where('id', $member)->update(['birthday' => $birthday.' 00:00:00']);
    }

    /**
     * 
     */
    public function getOrders(Array $data)
    {
        $orders = Orders::select(
                    DB::raw('orders.id, orders.mobile, orders.order_id,
                        orders.source_system,
                        (CASE orders.source_system WHEN "1" THEN "OOS" WHEN "2" THEN "KIOSK" WHEN "3" THEN "POS" END) AS `source`,
                        orders.checkout_time, order_invoice.shop_name, 
                        order_invoice.invoice_word, order_invoice.invoice_no, 
                    order_invoice.total_amount')
                )
                ->leftJoin('order_invoice', 'order_invoice.order_id', '=', 'orders.order_id')
                ->where('orders.mobile', $data['mobile'])->where('orders.status', 'Y');

        if (!empty($data['source'])) {
            $orders = $orders->where('orders.source_system', $data['source']);
        }

        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $orders = $orders->where('orders.created_at', '>=', $data['start_date'].' 00:00:00')
                    ->where('orders.created_at', '<=', $data['end_date'].' 23:59:59');
        }

        if (!empty($data['invoice'])) {
            $orders = $orders->where('order_invoice.invoice_word', $data['invoice_word'])
                    ->where('order_invoice.invoice_no', $data['invoice_no']);
        }

        $total = $orders->count();
        $orders = $orders->offset($data['offset'])->limit($data['limit'])->get();

        return ['total' => $total, 'records' => $orders];
    }
}
