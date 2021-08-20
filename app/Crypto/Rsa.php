<?php

namespace App\Crypto;



class Rsa {
    const CHAR_SET = "UTF-8";
    const BASE_64_FORMAT = "UrlSafeNoPadding";
    const RSA_ALGORITHM_KEY_TYPE = OPENSSL_KEYTYPE_RSA;
    const RSA_ALGORITHM_SIGN = OPENSSL_ALGO_SHA256;

    private $_config = [
        'public_key' => '',
        'private_key' => '',
    ];

    protected $key_len;

    public function __construct($private_key_filepath, $public_key_filepath) {
        $this->_config['private_key'] = $this->_getContents($private_key_filepath);
        $this->_config['public_key'] = $this->_getContents($public_key_filepath);
        $pub_id = openssl_get_publickey($this->_config['public_key']);
        $this->key_len = openssl_pkey_get_details($pub_id)['bits'];
    }

    /**
     * @param $file_path string
     * @return bool|string
     * @uses 獲取文件
     */
    private function _getContents($file_path) {
        file_exists($file_path) or die ('文件路徑錯誤');
        return file_get_contents($file_path);
    }

    /**
     * @return bool|resource
     * @uses 獲取私鑰
     */
    private function _getPrivateKey() {
        $priv_key = $this->_config['private_key'];
        return openssl_pkey_get_private($priv_key);
    }

    /**
     * @return bool|resource
     * @uses 獲取公鑰
     */
    private function _getPublicKey() {
        $public_key = $this->_config['public_key'];
        return openssl_pkey_get_public($public_key);
    }

    /*
    * 公鑰加密
    */
    public function publicEncrypt($data) {
        $encrypted = '';
        $part_len = $this->key_len / 8 - 11;
        $parts = str_split($data, $part_len);
        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_public_encrypt($part, $encrypted_temp, self::_getPublicKey());
            $encrypted .= $encrypted_temp;
        }
        return self::url_safe_base64_encode($encrypted);
    }

    /*
    * 私鑰解密
    */
    public function privateDecrypt($encrypted) {
        $decrypted = "";
        $part_len = $this->key_len / 8;
        $base64_decoded = self::url_safe_base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);
        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_private_decrypt($part, $decrypted_temp, self::_getPrivateKey());
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }
    function url_safe_base64_decode($data) {
        $base_64 = str_replace(array('-', '_'), array(' ', '/'), $data);
        return base64_decode($base_64);
    }
    function url_safe_base64_encode($data) {
        return str_replace(array(' ', '/', '='), array('-', '_', ''), base64_encode($data));
    }
}
