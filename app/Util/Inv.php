<?php
namespace App\Util;

use Illuminate\Support\Facades\Log;

class Inv {
    private $appId;
    private $apiKey;

    function __construct() {
        $this->appId = env("INV_APP_ID", "");
        $this->apiKey = env("INV_API_KEY", "");
    }

    /** 手機條碼載具驗證
     * @parameter
     * barCode String Require 載具編號
     * @return array
     **/
    public function verifyCarrierCode($barCode): array {
        if (empty($this->appId)) {
            return ["status" => false, "message"=> "appId is null"];
        }
        $api_host = 'https://www-vc.einvoice.nat.gov.tw/BIZAPIVAN/biz';
        $txId = strtotime(date("Y-m-d h:i:s"), 0);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_host,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'version=1.0&action=bcv&barCode=' . $barCode . '&TxID=' . $txId . '&appId=' . $this->appId,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));
        if (curl_errno($curl)) {
            Log::info("url=" . $api_host . "\nerr_log=" . (curl_errno($curl) ?? "") . "\n\n");
            return ["status" => false, "message"=> curl_errno($curl)];
        }
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        return ["status" => $response->isExist == 'Y', "message"=> ""];
    }
}
