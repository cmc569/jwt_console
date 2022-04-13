<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Repositories\AccountRepository;
use App\Util\UtilResponse;
use App\Util\Validate;
use App\Crypto\Crypto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;

class AccountsController extends Controller
{
    private $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/account",
     *     tags={"帳戶相關"},
     *     summary="帳戶清單",
     *     description="",
     *     @OA\Response(
     *         response=200,
     *         description="{'data':{},'msg':'succsess'}",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="{'data':{},'msg':'error msg'}",
     *     )
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'filter'    => ['nullable', 'string'],
            'role'      => ['nullable', Rule::in(['總部', '行銷', '客服'])],
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }
        
        $role   = $request->input('role') ?? null;
        $filter = $request->input('filter') ?? null;

        if (!empty($role)) {
            $role = $this->accountRepository->getRoleByName($role);
        }

        $accounts = $this->accountRepository->getAccounts(null, $filter);
        $data = $accounts->map(function($item) use($role) {
            $item->code = Crypto::encode($item->id);
            unset($item->id, $item->deleted_at, $item->updated_at);
            if (empty($role)) {
                return $item;
            } else {
                // dd($item);
                // dd($role);
                // dd($item->role->id);
                if ($item->role_id == $role) {
                    return $item;
                }
            }
        })->filter();
        $data = array_values($data->toArray());
        return UtilResponse::successResponse("success", $data);
    }

    /**
     * 
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name'      => ['required', 'string'],
            'email'     => ['required', 'email:rfc,dns'],
            'account'   => ['required', 'string'],
            'role'      => ['required', Rule::in(['總部', '行銷', '客服'])],
            'password'  => ['required', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).{6,12}$/'],
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $data = $request->input();
        $data['role'] = $this->accountRepository->getRoleByName($data['role']);

        if ($this->isAccountNameExists($data['account'])) {
            return UtilResponse::errorResponse("account name exists");
        }

        if ($this->isEmailExists($data['email'])) {
            return UtilResponse::errorResponse("email exists");
        }

        if ($user = $this->accountRepository->save($data)) {
            $data = $this->accountRepository->getAccounts($user->id);
            if (!is_null($data)) {
                $data->code = Crypto::encode($data->id);
                unset($data->id, $data->deleted_at, $data->updated_at, $data->role_id);
            }
            
            return UtilResponse::successResponse("success", $data);
        } else {
            return UtilResponse::errorResponse("account update failed");
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/edit",
     *     tags={"帳戶相關"},
     *     summary="特定帳戶資訊",
     *     description="",
     *     @OA\Response(
     *         response=200,
     *         description="{'data':{},'msg':'succsess'}",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="{'data':{},'msg':'error msg'}",
     *     )
     */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->input(), [
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

    /**
     * 
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'code'              => ['required', 'string'],
            'name'              => ['required', 'string'],
            'role'              => ['required', Rule::in(['總部', '行銷', '客服'])],
            'old_password'      => ['required_with:password,password_confirm', 'string'],
            'password'          => ['required_with:old_password,password_confirm', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).{6,12}$/'],
            'password_confirm'  => ['required_with:password,old_password', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).{6,12}$/'],
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $data = $request->input();

        $id = Crypto::decode($data['code']);
        $data['code'] = $id;
        if (empty($id)) return UtilResponse::errorResponse("invalid access");

        $account = $this->accountRepository->getAccounts($id);
        if (empty($account)) return UtilResponse::errorResponse("invalid account");

        if (!empty($data['old_password'])) {
            $account->password = Crypto::decode($account->password);
            if (empty($account->password) || ($account->password != $data['old_password'])) {
                return UtilResponse::errorResponse("password incorrect");
            }

            if ($data['password'] != $data['password_confirm']) {
                return UtilResponse::errorResponse("password mismatch");
            }
        }

        $data['role'] = $this->accountRepository->getRoleByName($data['role']);
        unset($data['old_password'], $data['password_confirm']);

        if ($this->accountRepository->update($account, $data)) {
            return UtilResponse::successResponse("success");
        } else {
            return UtilResponse::errorResponse("account update failed");
        }
    }

    /**
     * 
     */
    public function delete(Request $request)
    {
        // dd($request->input());
        $validator = Validator::make($request->input(), [
            'code'  => 'required|string',
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        $id = Crypto::decode($request->input('code'));
        $auth_id = $request->get('usersId');
        if ($id == $auth_id) {
            return UtilResponse::errorResponse("deletion invalid");
        }

        if ($this->accountRepository->delete($id)) {
            return UtilResponse::successResponse("success");
        } else {
            return UtilResponse::errorResponse("account delete failed");
        }
    }

    /**
     * 
     */
    private function isEmailExists(String $email)
    {
        return $this->accountRepository->isExists('email', $email);
    }

    /**
     * 
     */
    private function isAccountNameExists(String $account)
    {
        return $this->accountRepository->isExists('account', $account);
    }
}
