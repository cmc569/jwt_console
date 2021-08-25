<?php
namespace App\Util;
use Illuminate\Http\JsonResponse;

class UtilResponse{
    /**
     * 客製化Json回傳
     * @parameter $status: Bool, $message: String, $data: Any
     * @param bool $status
     * @param string $message
     * @param array $data
     * @return JsonResponse
     */
    public static function toJson(bool $status = true, string $message = "", array $data = []): JsonResponse {
        $statusCode = $status ? 200 : 400;
        $response = [
            "status_code" => $statusCode,
            "message" => $message,
        ];
        if (!empty($data)) {
            $response["data"] = $data;
        }

        return response()->json($response, $statusCode);
    }
}
