<?php


namespace App\Util;


use Exception;

class UtilJwt {
    /**
     * @var string
     */
    private static $secret;

    private static $instance;

    private function __construct() {}

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$secret = config("app.jwtSecret", "secret");
        }
        return self::$instance;
    }

    public static function encode(array $payload, string $alg = 'SHA256'): string {
        $key = md5(self::$secret);
        $time = time();
        $arr = [
            'iss' => config('app.name', "accuProject"), //簽發者
            'iat' => $time, //簽發時間
            'exp' => $time + 21600, //過期時間
            'nbf' => $time, //該時間之前不接收處理該Token
            'sub' => '', //面向用戶
            'jti' => md5(uniqid('JWT') . $time) //該token唯一認證
        ];
        $payload = array_merge($arr, $payload);

        $jwt = self::urlsafeB64Encode(json_encode(['typ' => 'JWT', 'alg' => $alg])) . '.' . self::urlsafeB64Encode(json_encode($payload));
        $signature = self::signature($jwt, $key, $alg);
        return $jwt . '.' . $signature;
    }

    /**
     * @throws Exception
     */
    public static function decode(string $jwt) {
        $tokens = explode('.', $jwt);
        $key = md5(self::$secret);
        if (count($tokens) != 3)
            throw new Exception("tokens is error");

        list($header64, $payload64, $sign) = $tokens;

        $header = json_decode(self::urlsafeB64Decode($header64), JSON_OBJECT_AS_ARRAY);
        if (empty($header['alg']))
            throw new Exception("alg is error");

        $signature = self::signature($header64 . '.' . $payload64, $key, $header['alg']);
        if ($signature !== $sign)
            throw new Exception("signature is error");

        $payload = json_decode(self::urlSafeB64Decode($payload64), JSON_OBJECT_AS_ARRAY);

        $timeNow = $_SERVER['REQUEST_TIME'];
        if (isset($payload['iat']) && $payload['iat'] > $timeNow)
            throw new Exception("iat is error");

        if (isset($payload['exp']) && $payload['exp'] < $timeNow)
            throw new Exception("token is expired");

        return $payload;
    }

    public static function urlSafeB64Decode(string $input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padLen = 4 - $remainder;
            $input .= str_repeat('=', $padLen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public static function urlSafeB64Encode(string $input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    public static function signature(string $input, string $key, string $alg) {
        try {
            return hash_hmac($alg, $input, $key);
        } catch (Exception $e) {
            return "";
        }
    }

}
