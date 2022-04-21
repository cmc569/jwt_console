<?php

namespace App\Http\Repositories;

use App\Http\Models\StoreValue;

class StoreValueRepository extends BaseRepository
{
    public function setStoreValue(array $storeData)
    {
        try{
            $result = StoreValue::create([
                'mobile' => $storeData['mobile'],
                'source_system' => 5,
                'order_id' => $storeData['order_id'],
                'systex_order_id' => $storeData['order_id'],
                'tender_name' => 'CMS',
                'price' => $storeData['price'],
                'stored_time' => NOW(),
                'edited_account' => $storeData['edited_account'],
                'expired_at' => $storeData['expired_at'] ?? null,
                'remark' => $storeData['remark']
            ]);
            return empty($result) ? false : true;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    public function orderList($mobile)
    {
        $list = StoreValue::select('order_id', 'price')
            ->where('mobile', $mobile)
            ->where('status', 'Y')
            ->get()->toArray();

        return $list;
    }
}
