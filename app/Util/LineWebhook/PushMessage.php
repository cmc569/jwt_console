<?php

namespace App\Util\LineWebhook;

use App\Util\UtilResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Utils;

class PushMessage {
    protected $client;
    protected $api_host;
    protected $headers;

    public function __construct() {
        $this->client = new Client();
        $this->api_host = "https://api.line.me/v2/bot";
        $this->headers = array(
            'Authorization: Bearer ' . config('app.lineBotBearToken'),
            'Content-Type: application/json'
        );
    }

    /** 推送訊息
     * @params
     * messageBody String Required 每則訊息格式請依照LINE官方Messages格式: https://developers.line.biz/en/reference/messaging-api/
     *
     * @messageBody
     * '{"to": {{userToken}}, "messages":[{{ messageObject }}]}'
     *
     * @Response 200
     * status  bool    success: true; failed: false
     * message string  error message
     * @throws GuzzleException
     * @throws \Exception
     */
    public function push(string $messagesBody) {
        $curl = curl_init();
        $url = $this->api_host . '/message/push';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $messagesBody,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ));
        $response = json_decode(curl_exec($curl));
        if (curl_errno($curl)) {
            Log::info("url=" . $url . "\nerr_log=" . curl_error($curl) . "\n\n");
            return ["status" => false, "message" => curl_errno($curl)];
        }
        curl_close($curl);
        if (isset($response->message)){
            return ["status" => false, "message" => $response->message];
        }
        return ["status" => true, "message" => ""];
    }
}
