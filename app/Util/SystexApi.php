<?php
namespace App\Util;

class SystexApi
{
    protected $log;
    protected $orderId;

    public function __construct()
    {
        // set error log file path
        $this->log = base_path().'/private/storage/'.env('APP_NAME').'/log/systex_api';

        if (!is_dir($this->log)) {
            mkdir($this->log, 0777, true);
        }
    }
    ##

    //curl
    public function send($setHeader,$postData)
    {
        

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $setHeader);
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_URL, env('SYSTEX_API'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // 紀錄開始執行時間
        $timeStart = microtime(true);
        
        $result = curl_exec($ch);

        // 紀錄結束執行時間
        $timeEnd = microtime(true);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //log
        $log = "End-Point: ".env('SYSTEX_API')."\n";
        $log .= 'Request: '.$postData."\n";
        $log .= 'Response: '.$result."\n";
        $log .= 'Response Code: '.$httpcode."\n";

        // 紀錄打api的執行時間
        $log .= 'Response Time: '.number_format($timeEnd - $timeStart, 3)." seconds\n";

        $this->Logs($log, 'request');

        return json_decode($result,true);
    }

    //簽到交易
    public function SignOn()
    {
         $requst_body =json_encode([
            "EndPoint" => "SignOn",
            "DateTime" => date('ymdHis'),
            "SignOnType" => 2,
            "SignOnDate" => date('Ymd'),
            "Mid" => "233091780000997",
            "Tid" => "00099701"
         ]);

        $result = md5(env('SYSTEX_CHECK_PREFIX').$requst_body.'SYSTEX');
        $cbc = openssl_encrypt($result, 'DES-CBC',env('SYSTEX_CHECK_PASSPHRASE'),0,env('SYSTEX_CHECK_IV')); // 0 = PKCS7 + base64_encode
        $header = ["Mask: ".substr($cbc,0,4).$result.substr($cbc, -4)];

        return $this->send($header,$requst_body);        
    }
    ##

    //CheckSum
    public function CheckSum(array $data)
    {

        $str = $data['Mid'].$data['Tid'].$data['OrderId'].$data['DateTime'];
    
        //補0到8的倍數
        $blocksize=8;
        $pad = $blocksize - (strlen($str) % $blocksize);
        $str = $str . str_repeat("\0", $pad);

        /*
            options = 0 : 默認 pkcs7 padding + base64
            options = 1 : OPENSSL_RAW_DATA ， pkcs7 padding
            options = 2 : OPENSSL_ZERO_PADDING ， 需先補0 不自動補 + base64
            options = 3 : OPENSSL_NO_PADDING

            DES-EDE   key長度 = 16
            DES-EDE3  key長度 = 24
        */

        $res = $this->SignOn();

        return openssl_encrypt($str, 'DES-EDE', $res['WorkKey'],2,'');
    }

    public function QueryBonus(String $QRCard)
    {
        $data =[
            "Host" => "SVC",
            "EndPoint" => "QueryBonus",
            "SignOnType" => "2",
            "DateTime" => date('ymdHis'),
            "Mid" => "233091780000997",
            "Tid" => "00099701",
            "OrderId" => "5001000120210205013821",
            "QRCard" => $QRCard

        ];

        $header = ["CheckSum: ".$this->CheckSum($data)];
        $body=json_encode($data);

        return $this->send($header,$body);
    }

    public function QueryTxn(String $QRCard)
    {
        $data =[
            "Host" => "SVC",
            "EndPoint" => "QueryTxn",
            "SignOnType" => "2",
            "DateTime" => date('ymdHis'),
            "Mid" => "233091780000997",
            "Tid" => "00099701",
            "OrderId" => "5001000120210205013821",
            "QRCard" => $QRCard

        ];

        $header = ["CheckSum: ".$this->CheckSum($data)];
        $body=json_encode($data);

        return $this->send($header,$body);
    }

    public function AdjustPointMinus(string $bonusId, string $storedCardNo, string $amount)
    {
        $this->orderId = $this->createOrderId();

        $data =[
            "Host" => "SVC",
            "EndPoint" => "AdjustPointMinus",  
            "SignOnType" =>"2",
            "DateTime" => date('ymdHis'),
            "Mid" => "233091780000997",
            "Tid" => "00099701",
            "OrderId" => $this->orderId,
            "MerchOrderNo" => $this->orderId,
            "SvcCardNo" => $storedCardNo,
            "CouponList" =>[         
                [
                    "BonusId" => $bonusId,
                    "Amount" => $amount
                ]
            ]
        ];


        $header = ["CheckSum: ".$this->CheckSum($data)];
        $body=json_encode($data);

        return $this->send($header,$body);
    }

    public function AdjustPointPlus(string $bonusId, string $endDate,string $storedCardNo, string $amount)
    {
        $this->orderId = $this->createOrderId();

        $data =[
            "Host" => "SVC",
            "EndPoint" => "AdjustPointPlus",  
            "SignOnType" =>"2",
            "DateTime" => date('ymdHis'),
            "Mid" => "233091780000997",
            "Tid" => "00099701",
            "OrderId" => $this->orderId,
            "MerchOrderNo" => $this->orderId,
            "SvcCardNo" => $storedCardNo,
            "CouponList" =>[         
                [
                    "BonusId" => $bonusId,
                    "Amount" => $amount,
                    "StartDate" => "00010101",
                    "EndDate" => $endDate
                ]
            ]
        ];
          
        $header = ["CheckSum: ".$this->CheckSum($data)];
        $body=json_encode($data);

        return $this->send($header,$body);
    }

    //產生長度22 orderId   
    private function createOrderId() {
        return substr(md5(uniqid(rand(), true)),0,22);
    }
    ##

    //資訊紀錄
    protected function Logs($response, $filename) {
        $_SERVER['REQUEST_SCHEME'] = $_SERVER['REQUEST_SCHEME'] ?? '';
        $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? '';
        $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '';
        $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SERVER['REMOTE_HOST'] = $_SERVER['REMOTE_HOST'] ?? '';

        $request_detail = "Remote_Addr: {$_SERVER['REMOTE_ADDR']}\n";
        $request_detail .= "Remote_Host: {$_SERVER['REMOTE_HOST']}\n";
        $request_detail .= "End-Point: ".$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."\n";
        
        file_put_contents(
            $this->log . '/' . $filename.'_'.date("Ymd").'.log',
            date("Y-m-d H:i:s")."\n========================\n".
                print_r($request_detail, true)."\n".
                print_r($response, true)."\n".
                "========================\n",
            FILE_APPEND
        );
    }
    ##
}