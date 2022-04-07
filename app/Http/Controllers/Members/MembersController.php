<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Http\Repositories\AccountRepository;
use App\Http\Repositories\MembersRepository;
use App\Util\UtilResponse;
use App\Util\Validate;
use App\Crypto\Crypto;

class MembersController extends Controller
{
    private $members_repository;

    public function __construct(MembersRepository $members_repository)
    {
        $this->members_repository = $members_repository;
    }

    //
    public function index()
    {
        $list = $this->members_repository->getMembers();
        $data = $this->filterColumn($list);

        return UtilResponse::successResponse("success", $data);
    }

    //
    public function getMembers(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'offset'        => 'integer',
            'limit'         => 'integer',
            'filter'        => 'nullable|string',
            'start_date'    => 'nullable|date_format:Y-m-d',
            'end_date'      => 'nullable|date_format:Y-m-d',
            'csv'           => 'nullable|boolean',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $filter = $request->input('filter') ?? null;
        $start_date = $request->input('start_date') ?? null;
        $end_date = $request->input('end_date') ?? null;
        $offset = $request->input('offset') ?? 0;
        $limit = $request->input('limit') ?? 10;
        $csv = $request->input('csv') ?? null;

        // 會員 user token, 過濾值, 加入的開始時間, 加入的結束時間, 取值開始位置, 每次顯示筆數, 是否 csv 下載
        $list = $this->members_repository->getMembers(null, $filter, $start_date, $end_date, $offset, $limit, $csv);

        $list = $list->map(function ($item, $key) {
            $item->code = Crypto::encode($item->id);
            $item->gender = ($item->gender == 'F') ? '女' : '男';
            unset($item->id, $item->pwd, $item->status, $item->updated_at);
            
            return $item;
        })->filter();

        return UtilResponse::successResponse("success", $list);
    }

    //
    public function member(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'member' => 'string',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid member");
        }
        $data = $request->input();
        $data['member'] = Crypto::decode($data['member']);
        $member = $this->members_repository->getMemberById($data['member']);
        $member->gender = ($member->gender == 'F') ? '女' : '男';
        $member->modify_role = AccountRepository::getRoleById($member->lastModify->role_id);
        $member->modify_name = $member->lastModify->name;
        $member->modify_account = $member->lastModify->account;
        unset($member->id, $member->pwd, $member->last_modify, $member->status, $member->lastModify);
        return UtilResponse::successResponse("success", $member);
    }

    //
    private function filterColumn($list)
    {
        return $list->map(function ($item, $key) {
            unset($item->id, $item->pwd, $item->status, $item->updated_at);
            return $item;
        })->filter();
    }

}
