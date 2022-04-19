<?php

namespace App\Http\Repositories;

use App\Http\Models\Members;
use App\Http\Models\GivePoints;
use App\Http\Models\MassGivePointUploads;
use App\Util\UtilTime;
use Exception;
use DB;

class GivePointsRepository extends BaseRepository
{
   
    public function getMassUploadRecords()
    {
        return MassGivePointUploads::select('filename', 'url', 'send_at', 'total', 'process_status', 'result', 'created_at')->get();
    }

    public function mobileToCardNo(Array $mobile)
    {
        return  Members::select('mobile', 'stored_card_no')->whereIn('mobile', $mobile)->get()->toArray();
    }

    public function massPointRegister(String $name, String $url, String $send_at, Array $data)
    {
        DB::beginTransaction();

        try {
            //建立上傳資訊
            $csv = MassGivePointUploads::create([
                'filename'      => $name,
                'url'           => $url,
                'send_at'       => $send_at,
                'total'         => count($data),
            ]);

            //紀錄欲發放點數
            foreach ($data as $v) {
                GivePoints::create([
                    'mobile'    => $v[0],
                    'card_no'   => $v[3] ?? null,
                    'operation' => 'ADD',
                    'point'     => $v[1] ?? 0,
                    'send_at'   => $send_at,
                    'end_at'    => empty($v[2]) ? null : $v[2],
                    'upload_id' => $csv->id,
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
        
    }

}
