<?php


namespace App\Util;


use Exception;

class UtilJwt {

    protected static $key;

    function __construct() {
        self::$key = 'secret';
    }

    public static function encode(array $payload, string $alg = 'SHA256') {
        $key = md5(self::$key);
        $time = time();
        $arr = [
            'iss' => config('app.name', "accuProject"), //簽發者
            'iat' => $time, //簽發時間
            'exp' => $time + 3600, //過期時間
            'nbf' => $time, //該時間之前不接收處理該Token
            'sub' => '', //面向用戶
            'jti' => md5(uniqid('JWT') . $time) //該token唯一認證
        ];
        $payload = array_merge($arr, $payload);

        $jwt = self::urlsafeB64Encode(json_encode(['typ' => 'JWT', 'alg' => $alg])) . '.' . self::urlsafeB64Encode(json_encode($payload));

        return $jwt . '.' . self::signature($jwt, $key, $alg);
    }

    /**
     * @throws Exception
     */
    public static function decode(string $jwt) {
        $tokens = explode('.', $jwt);
        $key = md5(self::$key);

        if (count($tokens) != 3)
            throw new Exception("tokens is error");

        list($header64, $payload64, $sign) = $tokens;

        $header = json_decode(self::urlsafeB64Decode($header64), JSON_OBJECT_AS_ARRAY);
        if (empty($header['alg']))
            throw new Exception("alg is error");

        if (self::signature($header64 . '.' . $payload64, $key, $header['alg']) !== $sign)
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
