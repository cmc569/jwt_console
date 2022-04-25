<?php

namespace App\Http\Repositories;

use App\Http\Models\Members;
use App\Http\Models\GivePoints;
use App\Http\Models\MassGivePointUploads;
use App\Util\UtilTime;
use Exception;
use DB;
use Log;

class GivePointsRepository extends BaseRepository
{
   
    public function getMassUploadRecords()
    {
        return MassGivePointUploads::select(
            'id as code', 
            'filename', 
            'url', 
            'send_at', 
            'total', 
            'process_status', 
            'result', 
            'created_at'
        )->get();
    }

    public function mobileToCardNo(Array $mobile)
    {
        return  Members::select('mobile', 'stored_card_no')->whereIn('mobile', $mobile)->get()->toArray();
    }

    public function givePointRegister(Array $data)
    {
        return GivePoints::create([
            'mobile'    => $data['mobile'],
            'card_no'   => $data['card_no'] ?? null,
            'operation' => $data['method'],
            'point'     => $data['point'] ?? 0,
            'send_at'   => date("Y-m-d H:i:s"),
            'end_at'    => $data['end_at'] ?? null,
            'upload_id' => null,
            'remark'    => $data['remark'] ?? null,
        ]);
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
            Log::error('DB error('.$e->getMessage().')');

            DB::rollback();
            return false;
        }
        
    }

    public function getMassPointRecord(Int $id)
    {
        return MassGivePointUploads::find($id);
    }

    public function massPointDelete(Int $id)
    {
        DB::beginTransaction();

        try {
            GivePoints::where('upload_id', $id)->delete();
            MassGivePointUploads::find($id)->delete();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            Log::error('DB error('.$e->getMessage().')');

            DB::rollback();
            return false;
        }
    }

}
