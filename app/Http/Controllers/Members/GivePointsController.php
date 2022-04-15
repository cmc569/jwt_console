<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Util\UtilResponse;
use App\Http\Repositories\GivePointsRepository;

class GivePointsController extends Controller
{
    private $give_point_repository;

    public function __construct(GivePointsRepository $give_point_repository)
    {
        $this->give_point_repository = $give_point_repository;
    }

    public function index()
    {
        return UtilResponse::successResponse("success", $data);
    }

    public function messUploads(Request $request)
    {
        dd($request->input());
    }
}
