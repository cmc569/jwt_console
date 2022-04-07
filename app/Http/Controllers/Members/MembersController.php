<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Http\Repositories\MembersRepository;
use App\Util\UtilResponse;
use App\Util\Validate;

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
            return $validator->errors();
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
            unset($item->id, $item->pwd, $item->status, $item->updated_at);
            return $item;
        })->filter();

        return UtilResponse::successResponse("success", $list);
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
