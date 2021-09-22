<?php

namespace App\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class AccuNixApi
{
    protected $client;
    protected $api_host;
    protected $headers;

    public function __construct()
    {
        $this->client = new Client();
        $this->api_host = "https://api-tf.accunix.net/api/line/" . config('app.accuNixLINEBotId');
        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' .  config('app.accuNixAuthToken')
        ];
    }

    /** 身份驗證
     * @params
     *  userToken	String	Required
     *  roleId	    Number	Required    規則id(PM會先於AccuNix後台設置)
     *  data	    Object              詳見Data
     *
     * @Data
     * {
     *    "data": {
     *        "info": {
     *            "name": "林艾可",
     *            "birth": "1990-01-01",
     *            "email": "email@email.com",
     *            "phone": "0912345678",
     *            "gender": "M",
     *            "address": "台北市松山區敦化南路一段2號5樓"
     *        },
     *        "customize": {
     *            "key-1": "string-1",
     *            "key-2": "string-2"
     *        }
     *    }
     * }
     *
     * @Response 200
     * {
     *      "message": "success"
     * }
     * @Response 404 (Bad Request)
     * {
     *        "message": "error: {{ErrorToken}}"
     * }
     * @Response 422 (Request Validate Failed)
     *
     * @throws GuzzleException
     * @throws \Exception
     */
    public function authenticate(string $userToken, string $roleId, array $data = [])
    {
        $uri = '/authenticate';
        $result = $this->curl('POST', $uri, [
            'headers' => $this->headers,
            'json' => compact('userToken', 'roleId', 'data')
        ]);

        if ($result['message'] !== 'success') {
            throw new \Exception($result['message'] ?? "api return blank error message");
        }
    }

    /** 寄送訊息
     * @params
     * userToken	String	Required
     * messages	    Array	Required    每則訊息格式請依照LINE官方Messages格式: https://developers.line.biz/en/reference/messaging-api/
     * messageId	Integer Options	    常用訊息id，與 messages 二擇一，若有此參數則忽略 messages
     *
     * @messages
     * [
     *      {"text": "Hello, world1", "type": "text"},
     *      {"text": "Hello, world2", "type": "text"}
     * ]
     * @messageId
     * messageId int Options
     *
     * @Response 200
     * @Response 404 (Bad Request)
     * @Response 422 (Request Validate Failed)
     *
     * @throws GuzzleException
     * @throws \Exception
     */
    public function sendMessage(string $userToken, array $messages = [], int $messageId)
    {
        $json = compact('userToken', 'messages');
        if ($messageId != 0){
            $json = compact('userToken', 'messageId');
        }
        $uri = '/message/send';
        $result = $this->curl('POST', $uri, [
            'headers' => $this->headers,
            'json' => $json
        ]);

        if ($result['message'] !== 'success') {
            throw new \Exception($result['message'] ?? "api return blank error message");
        }
    }

    /** 寫入好友資訊
     * @params
     *  userToken	String	Required
     *  data	    Object              詳見Data
     *
     * @Data
     * {
     *      "data": {
     *          "info": {
     *              "name": "林艾可",
     *              "birth": "1990-01-01",
     *              "email": "email@email.com",
     *              "phone": "0912345678",
     *              "gender": "M",
     *              "address": "台北市松山區敦化南路一段2號5樓"
     *          },
     *          "customize": {
     *              "key-1": "string-1",
     *              "key-2": "string-2"
     *          }
     *      }
     * }
     *
     * @Response 200
     * @Response 404 (Bad Request)
     * @Response 422 (Request Validate Failed)
     *
     * @throws GuzzleException
     * @throws \Exception
     */
    public function addUserInfo(string $userToken, array $data = [])
    {
        $uri = '/users/data';
        $result = $this->curl('PUT', $uri, [
            'headers' => $this->headers,
            'json' => compact('userToken', 'data')
        ]);

        if ($result['message'] !== 'success') {
            throw new \Exception($result['message'] ?? "api return blank error message");
        }
    }

    /** 新增標籤
     * @params
     * name	        String	Required    標籤名稱
     * description	String	            標籤說明
     * days	        Int	    Required    不得為 0，若為 -1 則為永遠，不得小於 -1，不得大於 365。
     *
     * @Response
     * message String
     * @Response 200
     * @Response 401 (Api Token 錯誤)
     * @Response 404 (Bad Request)
     * @Response 422 (Request Validate Failed)
     * @Response 429 (標籤額度不足)
     *
     * @throws GuzzleException
     * @throws \Exception
     */
    public function tagCreate(String $name, String $description, int $days)
    {
        $uri = '/tag/create';
        $result = $this->curl('POST', $uri, [
            'headers' => $this->headers,
            'json' => compact('name', 'description', 'days')
        ]);

        if ($result['message'] !== 'success') {
            throw new \Exception($result['message'] ?? "api return blank error message");
        }
    }

    /** 取得好友推薦目標資訊
     * @params
     * referral_id	int	推薦目標id
     *
     * @Response 200
     * {
     *      "data": {
     *          "name": "推薦好友目標",
     *          "end_at": "2021-01-22 18:52:00",
     *          "start_at": "2021-01-13 18:52:00",
     *          "is_active": 0,
     *          "created_at": "2021-01-13 18:52:32",
     *          "updated_at": "2021-01-14 18:13:56",
     *          "description": "說明",
     *          "total_share_count": 0
     *      },
     *      "message": "Success"
     * }
     * @Response 401 (Api Token 錯誤)
     * @Response 404 (Bad Request)
     * @Response 422 (Request Validate Failed)
     * {
     *      "message": "referral_id 錯誤"
     * }
     * @throws GuzzleException
     * @throws \Exception
     */
    public function getReferralInfo(string $referralId)
    {
        $uri = 'referral/info?referral_id='.$referralId;
        $result = $this->curl('GET', $uri, [
            'headers' => $this->headers
        ]);

        if ($result['message'] !== 'success') {
            throw new \Exception($result['message'] ?? "api return blank error message");
        }
    }

    /** 好友貼標
     * @params
     * userTokens	[String]	Required, userTokens 最多為10筆
     * tags	        [String]	Required  tags 最多為3筆
     *
     * @Response
     * message String
     * @Response 200
     * @Response 401 (Api Token 錯誤)
     * @Response 404 (Bad Request)
     * @Response 422 (Request Validate Failed)
     *
     * @throws GuzzleException
     * @throws \Exception
     */
    public function tagAdd(array $userTokens, array $tags)
    {
        $uri = '/tag/add';
        $result = $this->curl('POST', $uri, [
            'headers' => $this->headers,
            'json' => compact('userTokens', 'tags')
        ]);

        if ($result['message'] !== 'success') {
            throw new \Exception($result['message'] ?? "api return blank error message");
        }
    }

    /** 取得User推薦好友數
     * @params
     * user_token	String	分享者的user_token
     * referral_id	int	    推薦目標id
     *
     * @Response 200
     * {
     *      "data": {
     *          "name": "NAME",
     *          "picture": "url",
     *          "share_count": 0
     *      },
     *      "message": "Success"
     * }
     * @Response 404 (Bad Request)
     * @Response 422 (Request Validate Failed)
     * {
     *      "message": "user_token 錯誤"
     * }
     *
     * @throws GuzzleException
     */
    public function referralShareUser(string $userToken, string $referralId)
    {
        $uri = 'referral/share-user';
        $result = $this->curl('GET', $uri, [
            'headers' => $this->headers,
            'json' => compact('userToken', 'referralId')
        ]);

        if ($result['message'] !== 'success') {
            throw new \Exception($result['message'] ?? "api return blank error message");
        }
    }

    /** 取得好友分享連結
     * @params
     * sharer_token	String	Required 分享者的user_token
     *
     * @Response 200
     * @Response 404 (Bad Request)
     * @Response 422 (Request Validate Failed)
     *
     * @throws GuzzleException
     */
    public function getShareLink(string $sharerToken)
    {
        $uri = '/users/getShareLink';
        $result = $this->curl('POST', $uri, [
            'headers' => $this->headers,
            'json' => compact('sharerToken')
        ]);

        if ($result['message'] !== 'success') {
            throw new \Exception($result['message'] ?? "api return blank error message");
        }
    }

    /** 取得好友分享連結
     * @params
     * user_token	String	Required 請用LINE官方給的userId帶入
     * options	    String	Nullable 可傳入多個參數，一個以上使用逗號隔開，目前可用參數如下:
     * ⋆ tags: 可以帶出所有被貼過的標籤資料
     * ⋆ auth: 若此user_token曾經有打過身分驗證API即會顯示資訊
     *
     * @Response 200
     * @Response 404 (Bad Request)
     * @Response 422 (Request Validate Failed)
     *
     * @throws GuzzleException
     */
    public function getUserProfile(string $userToken, string $options)
    {
        $uri = '/users/getUserProfile';
        $result = $this->curl('GET', $uri, [
            'headers' => $this->headers,
            'json' => compact('userToken', 'options')
        ]);

        if ($result['message'] !== 'success') {
            throw new \Exception($result['message'] ?? "api return blank error message");
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $fields
     *
     * @return mixed
     * @throws GuzzleException
     */
    private function curl(string $method, string $uri, array $fields = [])
    {
        $url = $this->api_host . $uri;
        $response = $this->client->request($method, $url, $fields);
        $result = json_decode($response->getBody()->getContents(), true);
        $accessToken = $fields['headers']['Authorization'] ?? "";
        Log::info("url=" . $url . "\nmessages=" . ($result['message'] ?? "") . "\nAuthorization=" . $accessToken . "\nres=" . print_r($response, true) . "\n\n");
        return $result;
    }

}
