<?php
namespace App\Util;

class SystexGiftApi
{
    protected $log;
    protected $endpoint;
    protected $channelId;
    protected $checkPrefix;
    protected $checkSuffix;
    protected $key;

    /**
     * 初始化精誠商品券API參數
     * @param String $log 儲存log的位置
     */
    public function __construct(String $log = null)
    {
        $this->log = base_path().'/private/storage/'.env('APP_NAME').'/log/systexgift_api';
        if (!is_dir($this->log)) {
            mkdir($this->log, 0777, true);
        }

        $this->api = env('SYSTEX_GIFT_API');
        $this->channel = env('SYSTEX_GIFT_CHANNEL');
        $this->checkPrefix = env('SYSTEX_GIFT_CHECK_PREFIX');
        $this->checkSuffix = env('SYSTEX_GIFT_CHECK_SUFFIX');
        $this->key = env('SYSTEX_GIFT_KEY');
    }

    /**
     * 打精誠商品券API
     * @param String $apiFunc 要打的API功能名稱
     * @param Array $postData 要送給精誠API的參數
     * @param Array $hashParam 要進行CheckSum hash運算的參數(參數順序同精誠CheckSum規則)
     * @return Array 經過json decode的精誠API回應內容
     */
    public function send(String $apiFunc, Array $postData, Array $hashParam)
    {
        $sendData = $this->packagePostData($postData, $hashParam);
        $sendDataJson = json_encode($sendData);

        // 透過curl發出請求
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sendDataJson);
        curl_setopt($ch, CURLOPT_URL, $this->api."/".$apiFunc);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //log
        $log = "End-Point: {$this->api}{$apiFunc}\n";
        $log .= "Request: {$sendDataJson}\n";
        $log .= "Response: {$result}\n";
        $log .= "Response Code: {$httpCode}\n";

        $this->logs($log, 'request');

        return json_decode($result, true);
    }

    /**
     * 包裝要送給精誠API的post data(加上Channel及CheckSum)
     * @param Array $postData 要送給精誠API的參數
     * @param Array $hashParam 要進行CheckSum hash運算的參數(參數順序同精誠CheckSum規則)
     * @return Array 包裝後的post data
     */
    protected function packagePostData($postData, $hashParam)
    {
        $postData['Channel'] = $this->channel;
        $checkSum = $this->genCheckSum($postData, $hashParam);
        $postData['Checksum'] = $checkSum;

        return $postData;
    }

    /**
     * 產生精誠gift api的check sum
     * @param Array $data 要送給精誠API的參數
     * @param Array $hashParam 要進行CheckSum hash運算的參數(參數順序同精誠CheckSum規則)
     * @return String 計算後的check sum
     */
    protected function genCheckSum($data, $hashParam)
    {
        $hashStr = "";

        $hashStr .= $this->checkPrefix;

        foreach ($hashParam as $paramKey) {
            if (array_key_exists($paramKey, $data)) {
                $hashStr .= $data[$paramKey];
            }
        }

        $hashStr .= $this->checkSuffix;
        $hashStr .= $this->key;

        return strtoupper(md5($hashStr));
    }

    /*
     * 票券作廢
     */
    public function voidCoupon($postData)
    {
        $orderId = $this->createOrderId();
        $postData = [
            'Channel' => $this->channel,
            'Order_id' => $orderId,
            'channel_order_id' => $postData['channel_order_id'],
            'couponNoList' => [
                'Coupon' => $postData['Coupon']
            ],
            'voidnum' => $postData['voidnum'],
            'voidamount' => $postData['voidamount'],
            'systex_date' => date('ymdHis')
        ];

        $hashParam = [
            'Channel',
            'Order_id',
            'channel_order_id',
            'voidnum',
            'voidamount',
            'systex_date'
        ];
        $results = $this->send('voidCoupon', $postData, $hashParam);

        $results['order_id'] = $orderId;

        return $results;
    }

    //產生長度22 orderId
    private function createOrderId() {
        return substr(md5(uniqid(rand(), true)),0,20);
    }
    ##

    /**
     * 資訊紀錄
     * @param String $response 要記錄的api回應內容
     * @param String $filename log的檔名
     */

    protected function logs($response, $filename)
    {
        $_SERVER['REQUEST_SCHEME'] = $_SERVER['REQUEST_SCHEME'] ?? '';
        $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? '';
        $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '';
        $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SERVER['REMOTE_HOST'] = $_SERVER['REMOTE_HOST'] ?? '';

        $request_detail = "Remote_Addr: {$_SERVER['REMOTE_ADDR']}\n";
        $request_detail .= "Remote_Host: {$_SERVER['REMOTE_HOST']}\n";
        $request_detail .= "End-Point: {$_SERVER['REQUEST_SCHEME']}://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}\n";

        file_put_contents(
            $this->log . '/' . $filename.'_'.date("Ymd").'.log',
            date("Y-m-d H:i:s")."\n========================\n".
            print_r($request_detail, true)."\n".
            print_r($response, true)."\n".
            "========================\n",
            FILE_APPEND
        );
    }
}
