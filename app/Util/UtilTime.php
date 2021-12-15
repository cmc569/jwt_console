<?php
namespace App\Util;
class UtilTime {
    public static function timeNow(): string {
        date_default_timezone_set("Asia/Taipei");
        return date("Y-m-d H:i:s");
    }
}
