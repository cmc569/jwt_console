<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Repositories\AccountRepository;
use App\Util\UtilResponse;
use App\Util\Validate;
use App\Crypto\Crypto;
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
            $item->code = Crypto::encode($item->id);
            unset($item->id, $item->deleted_at, $item->updated_at, $item->role_id);
            return $item;
        });
        return UtilResponse::successResponse("success", $data);
    }

    /**
     * 
     */
    public function show(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'code'  => 'required|string',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $id = Crypto::decode($request->input('code'));
        $account = $this->accountRepository->getAccounts($id);

        if (!is_null($account)) {
            $account->code = Crypto::encode($account->id);
            unset($account->id, $account->deleted_at, $account->updated_at, $account->role_id);
        }
        return UtilResponse::successResponse("success", $account);
    }
}
