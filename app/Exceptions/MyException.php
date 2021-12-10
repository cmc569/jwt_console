<?php

namespace App\Exceptions;

use App\Util\UtilResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MyException extends \Exception
{
    /**
     * 自訂 Exception 基礎類別
     * @param string $message 錯誤訊息
     */
    public function __construct(string $message)
    {
        parent::__construct($message, 400);
    }

    public function report()
    {
        Log::error("error code:".$this->getCode()." error: ".$this->getMessage()." file:".$this->getFile()." line:".$this->getLine());
    }

    public function render(): JsonResponse
    {
        return UtilResponse::errorResponse($this->getMessage());
    }
}
