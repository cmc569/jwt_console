<?php

namespace App\Http\Repositories;

use App\Crypto\Crypto;
use App\Http\Models\Members;
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
            if (!is_null($filter)) {
                $members = $members->Where('name', 'LIKE', $filter)
                                ->orWhere('user_token', 'LIKE', $filter)
                                ->orWhere('email', 'LIKE', $filter)
                                ->orWhere('mobile', 'LIKE', $filter);
            }

            if (preg_match("/^\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}$/", $start) &&
                    preg_match("/^\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}$/", $end)) {

                $members = $members->where('created_at', '>=', $start)->where('created_at', '<=', $end);
            }

            if (empty($csv)) {
                $members = $members->offset($offset)->limit($limit);
            }

            return $members->get();

        } else {
            return $this->getMemberByUserToken($user_token);
        }

    }

    /**
     * 
     */
    public static function getMemberById(Int $id)
    {
        return Members::find($id);
    }

    /**
     * 
     */
    public static function getMemberByMobile(String $mobile)
    {
        return Members::where('mobile', $mobile)->first();
    }

    /**
     * 
     */
    public static function getMemberByUserToken(String $user_token)
    {
        return Members::where('user_token', $user_token)->first();
    }
}
