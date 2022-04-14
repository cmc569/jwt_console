<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Util\UtilResponse;
use App\Http\Repositories\TransTicketRepository;

class TransTicketController extends Controller
{
    private $trans_ticket_repository;

    public function __construct(TransTicketRepository $trans_ticket_repository)
    {
        $this->trans_ticket_repository = $trans_ticket_repository;
    }

    //
    public function list(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'filter'        => 'nullable|regex:/^U\w{32}$/i',
            'start_date'    => 'nullable|required_with:end_date|date_format:Y-m-d',
            'end_date'      => 'nullable|required_with:start_date|date_format:Y-m-d',
            'offset'        => 'nullable|required_with:limit|integer',
            'limit'         => 'nullable|required_with:offset|integer',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid parameters");
        }
        
        $filter     = $request->input('filter') ?? null;
        $start_date = $request->input('start_date') ?? null;
        $end_date   = $request->input('end_date') ?? null;
        $offset     = $request->input('offset') ?? 0;
        $limit      = $request->input('limit') ?? 10;

        $data = $this->trans_ticket_repository->getTickets($offset, $limit, $filter, $start_date, $end_date);
        
        return UtilResponse::successResponse("success", $data);
    }
}
