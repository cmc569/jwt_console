<?php

namespace App\Http\Repositories;

use App\Http\Models\CouponEditRecord;

class CouponEditRecordRepository
{
    public function setRecord(array $recordData)
    {
        try{
            $result = CouponEditRecord::create([
                'mobile' => $recordData['mobile'],
                'user_token' => $recordData['user_token'],
                'type' => $recordData['type'],
                'coupon_guid' => $recordData['coupon_guid'],
                'edited_account' => $recordData['edited_account'],
                'remark' => $recordData['remark']
            ]);
            return empty($result) ? false : true;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

}
