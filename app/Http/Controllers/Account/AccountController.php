<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Repositories\AccountRepository;
use App\Util\UtilResponse;
use App\Util\Validate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AccountController extends Controller
{
    private $accountRepository;

    public function __construct(AccountRepository $accountRepository) {
        $this->accountRepository = $accountRepository;
    }
    /**
     * 
     */
    public function index()
    {
        $accounts = $this->accountRepository->getAccounts();
        $data = $accounts->map(function($item) {
            unset($item->deleted_at, $item->updated_at, $item->role_id);
            return $item;
        });
        return UtilResponse::successResponse("success", $data);
    }
}
