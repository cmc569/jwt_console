<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Http\Models\Members;
use App\Http\Repositories\CouponEditRecordRepository;
use App\Http\Repositories\NixCouponRepository;
use App\Util\AccunixCouponApi;
use App\Util\AccunixLineApi;
use App\Util\UtilResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CouponController extends Controller
{

    /**
     * @var NixCouponRepository
     */
    private $nixCouponRepository;
    /**
     * @var CouponEditRecordRepository
     */
    private $recordRepository;

    public function __construct(NixCouponRepository $nixCouponRepository, CouponEditRecordRepository $recordRepository)
    {
        $this->nixCouponRepository = $nixCouponRepository;
        $this->recordRepository = $recordRepository;
    }

    public function campaignList()
    {
        $campaigns = $this->nixCouponRepository->getActivityCampaign();

        return $campaigns;
    }

    public function couponList(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'campaign_guid'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }
        $coupons = $this->nixCouponRepository->getCoupons($request->campaign_guid);

        return $coupons;
    }

    //補發
    public function couponGift(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'mobile'         => 'required|string',
            'coupon_guid'    => 'required|string',
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

        if (NULL == $member->user_token) {
            return UtilResponse::errorResponse("no token");
        }

        $accunixLineApi = new AccunixLineApi(env('ACCUNIX_LINE_BOT_ID'));

        $accunixLineApi->setAccessToken(env('ACCUNIX_API_TOKEN'));
        $res = $accunixLineApi->couponGift($member->user_token, $request->coupon_guid);

        if($res['status'] != 200) {
            return UtilResponse::errorResponse("accunix error");
        }

        $request->merge(['user_token' => $member->user_token]);
        $request->merge(['type' => '補發']);
        $res = $this->recordRepository->setRecord($request->input());

        if($res == false) {
            return UtilResponse::errorResponse('insert error');
        }

        return UtilResponse::successResponse("success");
    }

    //核銷
    public function couponVerify(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'mobile'         => 'required|string',
            'coupon_guid'    => 'required|string',
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

        if (NULL == $member->user_token) {
            return UtilResponse::errorResponse("no token");
        }

        $code = '';
        $accunixCouponApi = new AccunixCouponApi();
        $accunixCouponApi->setAccessToken(env('ACCUNIX_API_TOKEN'));
        $campaigns = $accunixCouponApi->couponChildrenList($member->user_token);

        foreach ($campaigns['data'] as $campaign) {
            foreach ($campaign['coupons'] as $coupon) {
                if($coupon['guid'] == $request->coupon_guid) {
                    $code = $coupon['code'];
                }
            }
        }

        $accunixLineApi = new AccunixLineApi(env('ACCUNIX_LINE_BOT_ID'));
        $accunixLineApi->setAccessToken(env('ACCUNIX_API_TOKEN'));
        $res = $accunixLineApi->couponVerify($code);

        if($res['status'] != 200) {
            return UtilResponse::errorResponse("accunix error");
        }

        $request->merge(['user_token' => $member->user_token]);
        $request->merge(['type' => '核銷']);
        $res = $this->recordRepository->setRecord($request->input());

        if($res == false) {
            return UtilResponse::errorResponse('insert error');
        }

        return UtilResponse::successResponse("success");

    }

    //取消核銷
    public function unverify(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'mobile'         => 'required|string',
            'coupon_guid'    => 'required|string',
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

        if (NULL == $member->user_token) {
            return UtilResponse::errorResponse("no token");
        }

        $code = '';
        $accunixCouponApi = new AccunixCouponApi();
        $accunixCouponApi->setAccessToken(env('ACCUNIX_API_TOKEN'));
        $campaigns = $accunixCouponApi->couponChildrenList($member->user_token);

        foreach ($campaigns['data'] as $campaign) {
            foreach ($campaign['coupons'] as $coupon) {
                if($coupon['guid'] == $request->coupon_guid) {
                    $code = $coupon['code'];
                }
            }
        }

        $res = $accunixCouponApi->unverify($code);
        if($res['status'] != 200) {
            return UtilResponse::errorResponse("accunix error");
        }

        $request->merge(['user_token' => $member->user_token]);
        $request->merge(['type' => '取消核銷']);
        $res = $this->recordRepository->setRecord($request->input());

        if($res == false) {
            return UtilResponse::errorResponse('insert error');
        }

        return UtilResponse::successResponse("success");
    }
}
