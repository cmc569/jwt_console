<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Util\UtilResponse;
use App\Http\Repositories\GivePointsRepository;
use App\Crypto\Crypto;

class GivePointsController extends Controller
{
    private $give_point_repository;

    public function __construct(GivePointsRepository $give_point_repository)
    {
        $this->give_point_repository = $give_point_repository;
    }

    public function index()
    {
        $data = $this->give_point_repository->getMassUploadRecords();
        $data = $data->map(function($item) {
            $item->code = Crypto::encode($item->code);
            return $item;
        })->filter();
        return UtilResponse::successResponse("success", $data);
    }

    public function messUploads(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'date'  => 'required|date_format:Y-m-d',
            'time'  => 'required|regex:/^\d{2}\:\d{2}\:\d{2}$/',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid parameters");
        }

        if (empty($request->file('csv')->isValid())) {
            return UtilResponse::errorResponse("no csv file uploaded");
        }

        $date = $request->input('date');
        $time = $request->input('time');

        //檢查發送時間區間
        if (($time < '23:00:00') && ($time > '07:00:00')) {
            return UtilResponse::errorResponse("invalid time spacific");
        }

        //取得csv檔案
        $csv = $request->file('csv');

        //檔案資訊
        $ext       = $csv->getClientOriginalExtension();
        $file_name = $csv->getClientOriginalName();
        $content   = $csv->getContent();

        //重置檔案名稱與存放路徑
        $send_at   = $request->input('date').' '.$request->input('time');
        $uuid      = md5($request->get('usersId').time());
        $name      = $uuid.'.'.$ext;
        $url       = "/uploads/mass_upload/{$name}";
        $csv->move(public_path('uploads/mass_upload'), $name);

        //解析與紀錄點數發放資訊
        $data = $this->parseCsv($content);
        $data = $this->filterData($data);
        
        $mobile = $data->pluck(0)->toArray();
        $mobile = $this->give_point_repository->mobileToCardNo($mobile);
        $mobile = $this->mobileMappingToCardNo($mobile);

        $data = $data->map(function($item) use($mobile) {
            $item[3] = $mobile[$item[0]];
            $item[2] = trim(str_replace('/', '-', preg_replace("/\r/", '', $item[2])));
            return $item;
        });
        
        //
        if ($this->give_point_repository->massPointRegister($file_name, $url, $send_at, $data->toArray())) {
            return UtilResponse::successResponse("success");
        } else {
            return UtilResponse::errorResponse("mass point request failed");
        }
    }

    public function parseCsv(String $content)
    {
        if (empty($content)) {
            return [];
        }

        $data = explode("\n", $content);
        if (empty($data)) {
            return [];
        }

        $csv = [];
        foreach ($data as $line) {
            if (!empty($line)) {
                $fields = explode(',', $line);
                $csv[] = $fields;
            }
        }

        return $csv;
    }

    private function filterData(Array $csv)
    {
        $csv = collect($csv)->map(function($item) {
            if (preg_match("/^09[0-9]{8}$/", $item[0])) {
                return $item;
            }
        })->filter();

        return $csv;
    }

    private function mobileMappingToCardNo(Array $mobile)
    {
        $data = [];
        foreach ($mobile as $v) {
            $data[$v['mobile']] = $v['stored_card_no'];
        }

        return $data;
    }

    public function messDelete(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'code'  => 'required|string',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid parameter");
        }

        $id = Crypto::decode($request->input('code'));
        if (empty($id)) {
            return UtilResponse::errorResponse("invalid record id");
        }

        $data = $this->give_point_repository->getMassPointRecord($id);
        if (empty($data)) {
            return UtilResponse::errorResponse("invalid record");
        }

        if (strtotime($data->send_at) < (time() - 1800)) {
            return UtilResponse::errorResponse("exceed time limit to delete");
        }

        if ($this->give_point_repository->massPointDelete($id)) {
            return UtilResponse::successResponse("success");
        } else {
            return UtilResponse::errorResponse("record delete failed");
        }
    }
}
