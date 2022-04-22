<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Http\Models\Members;
use App\Http\Repositories\StoreValueRepository;
use App\Util\UtilResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Util\SystexApi;

class StoreValueController extends Controller
{
    /**
     * @var StoreValueRepository
     */
    private $storeValueRepository;

    public function __construct(StoreValueRepository $storeValueRepository)
    {
        $this->storeValueRepository = $storeValueRepository;
    }

    public function plus(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'mobile'         => 'required|string',
            'price'          => 'required|numeric',
            'edited_account' => 'required|string',
            'expired_at'     => 'required|date_format:Y-m-d',
            'remark'         => 'max:50'
        ]);

        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $member = Members::where('mobile', $request->mobile)->first();

        if (empty($member)) {
            return UtilResponse::errorResponse("no member mobile found");
        }

        $systexApi = new SystexApi();

        $expiredAt = date_create($request->expired_at);
        $expiredAt = date_format($expiredAt,"Ymd");
        $res = $systexApi->AdjustPointPlus(env('SYSTEX_VALUE_BONUSID'), $expiredAt, $member->stored_card_no, $request->price);

        if($res['ReturnCode'] != "0") {
            return UtilResponse::errorResponse($res['ReturnMessage']);
        }
        $request->merge(['order_id' => $res['MerchOrderNo']]);

        $res = $this->storeValueRepository->setStoreValue($request->input());
        if($res == false) {
            return UtilResponse::errorResponse('insert error');
        }

        return UtilResponse::successResponse("success");
    }

    public function minus(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'mobile'         => 'required|string',
            'order_id'       => 'required|string',
            'price'          => 'required|numeric',
            'edited_account' => 'required|string',
            'remark'         => 'max:50'
        ]);

        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $member = Members::where('mobile', $request->mobile)->first();

        if (empty($member)) {
            return UtilResponse::errorResponse("no member mobile found");
        }
        $systexApi = new SystexApi();

        $res = $systexApi->AdjustPointMinus(env('SYSTEX_VALUE_BONUSID'), $member->stored_card_no, $request->price);

        if($res['ReturnCode'] != "0") {
            return UtilResponse::errorResponse($res['ReturnMessage']);
        }
        $request->merge(['order_id' => $res['MerchOrderNo']]);
        $request->merge(['price' => -1 * $request->price]);

        $res = $this->storeValueRepository->setStoreValue($request->input());
        if($res == false) {
            return UtilResponse::errorResponse('insert error');
        }
        return UtilResponse::successResponse("success");
    }

    public function orderList(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'mobile'         => 'required|string'
        ]);

        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }
        $list = $this->storeValueRepository->orderList($request->mobile);

        if($list) {
            return UtilResponse::successResponse("success");
        } else {
            return UtilResponse::errorResponse('error');
        }
    }
}
