<?php
namespace App\Util;

class AccunixCouponApi
{
    protected $baseUrl = 'https://api-tf.accunix.net/api/project/burger-king';
    protected $log;
    protected $AccessToken;
    protected $headers;

    //建構
    public function __construct()
    {
        $this->log = base_path().'/private/storage/'.env('APP_NAME').'/log/accunix_api/coupon';

        if (!is_dir($this->log)) {
            mkdir($this->log, 0777, true);
        }

        $this->headers = [
            'Content-Type: application/json;'
        ];
    }
    ##

    //設定access token
    public function setAccessToken(String $AccessToken)
    {
        $this->AccessToken = $AccessToken;
        $this->setHeaders(["Authorization: Bearer {$AccessToken}"]);

        return $this;
    }

    //設定額外檔頭
    protected function setHeaders(Array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }
    ##


    public function couponChildrenList(String $token)
    {
        if (!empty($token)) {
            $post_data['userToken'] = $token;
        }

        $this->url = "{$this->baseUrl}/coupon-children-list?".http_build_query($post_data);

        return $this->send($post_data, 'GET');
    }

    //取消核銷
    public function unverify(String $code = NULL)
    {
        $this->url = "{$this->baseUrl}/unverify";

        if (!empty($code)) {
            $post_data['code'] = $code;
        }

        return $this->send($post_data);
    }

    public function check(String $code=NULL)
    {
        $this->url = "{$this->baseUrl}/check";

        if (!empty($code)) {
            $post_data['code'] = $code;
        }

        return $this->send($post_data);
    }

    protected function send(Array $post_data, String $method=NULL)
    {
        $method = empty($method) ? 'POST' : strtoupper($method);

        //發出
        $ch = curl_init($this->url);

        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        }

        if ($method == 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        }

        if (!empty($this->headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        try {
            $json = curl_exec($ch);
            $response = json_decode($json, true);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } catch (Exception $e) {
            $this->Logs($e->getMessage(), 'error', 'exception');

            return [
                'status'    => 402,
                'message'   => '其他錯誤',
            ];
        }
        curl_close($ch);
        ##

        //log
        $log = "End-Point: {$this->url}({$method})\n";
        $log .= 'Request: '.json_encode($post_data, JSON_UNESCAPED_UNICODE)."\n";
        $log .= 'Response: '.json_encode($response, JSON_UNESCAPED_UNICODE)."\n";
        $log .= 'Response Code: '.$status."\n";

        $this->Logs($log, 'request');
        ##

        // $status = ($status == 200) ? 200 : 400;
        $return = [
            'status'    => $status,
            'message'   => $response['message'] ?? 'success',
        ];

        if ($status == 200) {
            if (isset($response['message'])) unset($response['message']);
            if (!empty($response)) {
                $return['data'] = $response;
            }
        }

        return $return;
    }

    //資訊紀錄
    protected function Logs($response, $path, $filename='')
    {
        // set log
        $filename = $path ?? "";
        $path = $path ?? $this->log;

        $path = empty($filename) ? $this->log : $this->log.'/'.$filename;

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        ##

        $_SERVER['REQUEST_SCHEME'] = $_SERVER['REQUEST_SCHEME'] ?? '';
        $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? '';
        $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '';
        $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SERVER['REMOTE_HOST'] = $_SERVER['REMOTE_HOST'] ?? '';

        $request_detail = "Remote_Addr: {$_SERVER['REMOTE_ADDR']}\n";
        $request_detail .= "Remote_Host: {$_SERVER['REMOTE_HOST']}\n";
        $request_detail .= "End-Point: ".$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."\n";

        file_put_contents(
            $path.'/'.$filename.'_'.date("Ymd").'.log',
            date("Y-m-d H:i:s")."\n========================\n".
            print_r($request_detail, true)."\n".
            print_r($response, true)."\n".
            "========================\n",
            FILE_APPEND
        );
    }
    ##
}
