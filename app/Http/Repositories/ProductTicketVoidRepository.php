<?php

namespace App\Http\Repositories;

use App\Http\Models\ProductTicketVoid;

class ProductTicketVoidRepository extends BaseRepository
{
    public function setVoid(array $voidData)
    {
        try{
            $result = ProductTicketVoid::create([
                'mobile' => $voidData['mobile'],
                'source_system' => '5',
                'order_id' => $voidData['order_id'],
                'old_order_id' => $voidData['old_order_id'],
                'product_id' => $voidData['product_id'],
                'voidnum' => $voidData['voidnum'],
                'voidamount' => $voidData['voidamount'],
                'tender_name' => 'CMS',
                'remark' => $voidData['remark']
            ]);
            return empty($result) ? false : true;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}
