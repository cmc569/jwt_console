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
            'offset'        => 'nullable|required_with:limit|integer',
            'limit'         => 'nullable|required_with:offset|integer',
            'filter'        => 'nullable|string',
            'start_date'    => 'nullable|required_with:end_date|date_format:Y-m-d',
            'end_date'      => 'nullable|required_with:start_date|date_format:Y-m-d',
            'csv'           => 'nullable|boolean',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $filter     = $request->input('filter') ?? null;
        $start_date = $request->input('start_date') ?? null;
        $end_date   = $request->input('end_date') ?? null;
        $offset     = $request->input('offset') ?? 0;
        $limit      = $request->input('limit') ?? 10;
        $csv        = $request->input('csv') ?? null;

        // 會員 user token, 過濾值, 加入的開始時間, 加入的結束時間, 取值開始位置, 每次顯示筆數, 是否 csv 下載
        $list = $this->members_repository->getMembers(null, $filter, $start_date, $end_date, $offset, $limit, $csv);

        $list['records'] = $list['records']->map(function ($item, $key) {
            $item->code = Crypto::encode($item->id);
            $item->gender = ($item->gender == 'F') ? '女' : '男';
            unset($item->id, $item->pwd, $item->status, $item->updated_at);
            
            return $item;
        })->filter();

        return UtilResponse::successResponse("success", $list);
    }

    //
    public function csv(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'filter'        => 'nullable|string',
            'start_date'    => 'nullable|required_with:end_date|date_format:Y-m-d',
            'end_date'      => 'nullable|required_with:start_date|date_format:Y-m-d',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $data = [
            'filter'        => $request->input('filter') ?? null,
            'start_date'    => $request->input('start_date') ?? null,
            'end_date'      => $request->input('end_date') ?? null,
        ];

        if ($this->members_repository->csvRegister($request->get('usersId'), json_encode($data))) {
            return UtilResponse::successResponse("success");
        }

        return UtilResponse::errorResponse("csv download register failed");
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

        $member->modify_role = null;
        $member->modify_name = null;
        $member->modify_account = null;
        if (!empty($member->lastModify->role_id)) {
            $member->modify_role = AccountRepository::getRoleById($member->lastModify->role_id);
            $member->modify_name = $member->lastModify->name;
            $member->modify_account = $member->lastModify->account;
        }
        unset($member->id, $member->pwd, $member->last_modify, $member->status, $member->lastModify);
        return UtilResponse::successResponse("success", $member);
    }

    //
    public function memberBirthday(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'member'    => 'required|string',
            'birthday'  => 'required|date_format:Y-m-d',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid parameters");
        }

        $member     = $request->input('member');
        $birthday   = $request->input('birthday');

        $member = Crypto::decode($member);
        if (empty($member)) {
            return UtilResponse::errorResponse("invalid member");
        }

        if ($this->members_repository->memberUpdateBirthday($member, $birthday)) {
            return UtilResponse::successResponse("success");
        }

        return UtilResponse::errorResponse("update failed");
    }

    //
    public function orderList(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'member'        => 'required|string',
            'source'        => ['nullable', Rule::in(['OOS', 'KIOSK', 'POS'])],
            'invoice'       => 'nullable|regex:/^\w{2}(\-)?\w{8}$/',
            'start_date'    => 'nullable|required_with:end_date|date_format:Y-m-d',
            'end_date'      => 'nullable|required_with:start_date|date_format:Y-m-d',
            'offset'        => 'nullable|required_with:limit|integer',
            'limit'         => 'nullable|required_with:offset|integer',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid parameters");
        }

        $data = $request->input();
        $data['member'] = Crypto::decode($data['member']);
        if (empty($data['member'])) {
            return UtilResponse::errorResponse("invalid member");
        }

        $data['mobile'] = $this->members_repository->getMemberById($data['member'])->mobile;
        if (empty($data['mobile'])) {
            return UtilResponse::errorResponse("no member mobile found");
        }

        if (!empty($data['source'])) {
            $data['source'] = $this->convertSource($data['source']);
        }

        if (!empty($data['invoice'])) {
            $data = array_merge($data, $this->seperateInvoice($data['invoice']));
        }

        $data['offset'] = $data['offset'] ?? 0;
        $data['limit']  = $data['limit']  ?? 10;

        $list = $this->members_repository->getOrders($data);
        return UtilResponse::successResponse("success", $list);
    }

    //
    public function orderDetail(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'order_id'        => 'required|string',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid parameters");
        }
        
        $list = $this->members_repository->getOrderById($request->input('order_id'));
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

    //
    private function convertSource(String $source)
    {
        switch ($source) {
            case 'OOS':
                return 1;
            case 'KIOSK':
                return 2;
            case 'POS':
                return 3;
        }
    }

    //
    private function seperateInvoice(String $invoice)
    {
        $invoice = str_replace('-', '', strtoupper($invoice));

        return [
            'invoice_word'  => substr($invoice, 0, 2),
            'invoice_no'    => substr($invoice, 2),
        ];
    }
}
