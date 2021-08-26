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
        $this->api_host = "https://api-tf.accunix.net/api/line/" . config('app.accunixLINEBotId');
        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' .  config('app.authToken')
        ];
    }

    /**
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

    /**
     * @throws GuzzleException
     */
    public function addTag(array $userTokens, array $tags)
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
