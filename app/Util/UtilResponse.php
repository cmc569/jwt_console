<?php

namespace App\Util;

use Illuminate\Http\JsonResponse;
use phpseclib3\Math\PrimeField\Integer;

class UtilResponse {

    public static function toJson(int $statusCode = 200, string $message = "", array $data = []): JsonResponse {
        $response = [
            "msg" => $message,
            "data" => $data
        ];
        return response()->json($response, $statusCode);
    }

    public static function successResponse($message, $data = []): JsonResponse {
        $response = [
            "msg" => $message
        ];
        if (!empty($data)){
            $response["data"] = $data;
        }
        return response()->json($response);
    }

    public static function errorResponse(string $message = ""): JsonResponse {
        $response = [
            "msg" => $message
        ];
        return response()->json($response, 400);
    }
}
