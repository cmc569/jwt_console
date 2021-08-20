<?php
namespace App\Util;
class UtilResponse{
    /**
     * 客製化Json回傳
     * @parameter $status: Bool, $message: String, $data: Any
     * @return \Illuminate\Http\JsonResponse
    **/
    public static function toJson($status = true, $message = "", $data = []): \Illuminate\Http\JsonResponse {
        return response()->json(
            [
                "status" => $status,
                "message" => $message,
                "data" => $data,
            ]);
    }
}
