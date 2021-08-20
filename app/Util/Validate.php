<?php

namespace App\Util;
use Illuminate\Support\Facades\Log;

class Validate {
    /**
     * 驗證email
     * @parameter $email
     * @return bool
     **/
    public static function CheckEmail($email): bool {
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        return preg_match($regex, $email);
    }
    /**
     * 驗證密碼
     * @parameter $password
     * @return bool
     **/
    public static function CheckPassword($password): bool {
        $regex = '/^[a-z|A-Z0-9]{6,12}$/';
        return preg_match($regex, $password);
    }
    /**
     * 驗證電話
     * @parameter $phone
     * @return bool
     **/
    public static function CheckPhone($phone): bool {
        // 判斷是否大於10碼
        $regex = '/^[0-9]{10,}$/';
        return preg_match($regex, $phone);
    }
    /**
     * 驗證base64
     * @parameter $base64
     * @return bool
     **/
    public static function CheckDataBase64($base): bool {
        $regex = '/^data:image\/(jpg|gif|jpeg|png|mp4);base64,([^\"]*)$/';
        return preg_match($regex, $base);
    }
}
