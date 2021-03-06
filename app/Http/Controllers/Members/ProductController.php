<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Http\Models\ProductTicketOrders;
use App\Http\Models\ProductTickets;
use App\Http\Repositories\ProductTicketOrdersRepository;
use App\Http\Repositories\ProductTicketsRepository;
use App\Http\Repositories\ProductTicketVoidRepository;
use App\Util\SystexGiftApi;
use App\Util\UtilResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    /**
     * @var ProductTicketOrdersRepository
     */
    private $ordersRepository;
    /**
     * @var ProductTicketsRepository
     */
    private $ticketsRepository;
    /**
     * @var ProductTicketVoidRepository
     */
    private $voidRepository;

    public function __construct(ProductTicketOrdersRepository $ordersRepository, ProductTicketsRepository $ticketsRepository, ProductTicketVoidRepository $voidRepository)
    {
        $this->ordersRepository = $ordersRepository;
        $this->ticketsRepository = $ticketsRepository;
        $this->voidRepository = $voidRepository;
    }

    public function orderList(Request $request)
    {

        $validator = Validator::make($request->input(), [
            'mobile'         => 'required|string'
        ]);
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }
        $list = $this->ordersRepository->orderList($request->mobile);

        return UtilResponse::successResponse("success", $list);
    }

    public function couponList(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'mobile'     => 'required|string',
            'order_id'   => 'required|string'
        ]);
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $list = $this->ticketsRepository->couponList($request->mobile, $request->order_id);

        return UtilResponse::successResponse("success", $list);
    }

    public function voidCoupon(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'mobile'      => 'required|string',
            'voidnum'     => 'required',
            'voidamount'  => 'required',
            'coupon'      => 'required',
            'remark'      => 'max:50'
        ]);
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $productTicket = ProductTickets::where('coupon_no', $request->coupon[0]['couponNo'])
            ->where('mobile', $request->mobile)
            ->first();

        $productTicketOrders = ProductTicketOrders::where('order_id', $productTicket->product_ticket_order_id)
            ->where('mobile', $request->mobile)
            ->first();

        $systexGiftApi = new SystexGiftApi();

        $postData = [
            'channel_order_id' => $productTicketOrders->systex_order_id,
            'Coupon' => $request->coupon,
            'voidnum' => $request->voidnum,
            'voidamount' => $request->voidamount
        ];

        $results = $systexGiftApi->voidCoupon($postData);
        if ($results['return_code'] != '000') {
            return UtilResponse::errorResponse('SYSTEX::'.$results['return_code']);
        }
        try{
            $res = $this->ticketsRepository->updateTicketsStatus($request->mobile, $request->coupon);
        } catch (\Exception $e) {
            return UtilResponse::errorResponse("write failed");
        }

        if (true != $res) {
            return UtilResponse::errorResponse('write failed');
        }
        $results['order_id'] = 123;
        $results['channel_order_id'] = 456;

        $voidData = [
            'mobile' => $request->mobile,
            'order_id' => $results['order_id'],
            'old_order_id' => $results['channel_order_id'],
            'product_id' => $productTicket->product_id,
            'voidnum' => $request->voidnum,
            'voidamount' => $request->voidamount,
            'remark' => $request->remark
        ];

        try{
            $results = $this->voidRepository->setVoid($voidData);
        } catch (\Exception $e) {
            return UtilResponse::errorResponse("write failed");
        }

        if(false == $results) {
            return UtilResponse::errorResponse("write failed");
        }

        return UtilResponse::successResponse("success");
    }

}
