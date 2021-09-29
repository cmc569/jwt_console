<?php

namespace App\Util\LineWebhook;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class PushMessage {
    protected $api_host;
    protected $headers;

    public function __construct() {
        $this->api_host = "https://api.line.me/v2/bot";
        $this->headers = array(
            'Authorization: Bearer ' . config('app.lineBotBearToken'),
            'Content-Type: application/json'
        );
    }
    /** 推送訊息(單人)
     * @params
     * userTokens String   Required Max: 1 user IDs
     * messages   [Object] Required Max: 5 (每則訊息格式請依照LINE官方Messages格式: https://developers.line.biz/en/reference/messaging-api/)
     *
     * @Response 200
     * status  bool    success: true; failed: false
     * message string  error message
     */
    public function push(string $userToken, array $messages) {
        if (!is_string($userToken)){
            return ["status" => false, "message" => "userToken is not string"];
        }
        if (empty($userToken)){
            return ["status" => false, "message" => "userToken is null"];
        }
        if (!is_array($messages)){
            return ["status" => false, "message" => "messages is not array"];
        }
        if (count($messages) > 5){
            return ["status" => false, "message" => "messages is overed 5"];
        }
        $messageBody = '{"to": '.$userToken.', "messages": '.json_encode($messages)."}'";
        $path = '/message/push';
        return $this->curl($path, 'POST', $messageBody);
    }
    /** 推送訊息(多人)
     * @params
     * userTokens Array    Required Max: 500 user IDs
     * messages   [Object] Required Max: 5 (每則訊息格式請依照LINE官方Messages格式: https://developers.line.biz/en/reference/messaging-api/)
     *
     * @Response 200
     * status  bool    success: true; failed: false
     * message string  error message
     */
    public function multicast(array $userTokens, array $messages) {
        if (!is_array($userTokens)){
            return ["status" => false, "message" => "userTokens is not array"];
        }
        if (count($userTokens) > 500){
            return ["status" => false, "message" => "userTokens is overed 500"];
        }
        if (!is_array($messages)){
            return ["status" => false, "message" => "messages is not array"];
        }
        if (count($messages) > 5){
            return ["status" => false, "message" => "messages is overed 5"];
        }
        $messageBody = '{"to": '.json_encode($userTokens).', "messages": '.json_encode($messages)."}'";
        $path = '/message/multicast';
        return $this->curl($path, 'POST', $messageBody);
    }

    private function curl(string $path, string $methods, string $messageBody){
        if (empty(config('app.lineBotBearToken'))){
            return ["status" => false, "message" => "lineBotBearToken is empty"];
        }
        $curl = curl_init();
        $url = $this->api_host . $path;
        $opt = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $methods,
            CURLOPT_POSTFIELDS => $messageBody,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        );
        curl_setopt_array($curl, $opt);
        $response = json_decode(curl_exec($curl));
        if (curl_errno($curl)) {
            Log::info("url=" . $url . "\nerr_log=" . curl_error($curl) . "\n\n");
            return ["status" => false, "message" => curl_error($curl)];
        }
        curl_close($curl);
        if (isset($response->message)){
            return ["status" => false, "message" => $response->message];
        }
        return ["status" => true, "message" => ""];
    }
}
