<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Util\UtilResponse;

class TransTicketController extends Controller
{
    //
    public function list(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'start_date'    => 'nullable|required_with:end_date|date_format:Y-m-d',
            'end_date'      => 'nullable|required_with:start_date|date_format:Y-m-d',
            'user_token'    => 'nullable|regex:/^U\w{32}$/i',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid parameters");
        }
        
        dd($request->input());
    }
}
