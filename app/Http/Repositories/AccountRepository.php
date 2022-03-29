<?php

namespace App\Http\Repositories;

use App\Crypto\Crypto;
use App\Http\Models\Users;
use App\Http\Models\Roles;
use App\Util\UtilTime;
use Exception;
use DB;

class AccountRepository extends BaseRepository
{
    /**
     * 
     */
    public function getRoleByName(String $role)
    {
        $roles = Roles::where('name', $role)->first('id');
        return $roles->id ?? null;
    }

    /**
     * 
     */
    public function isExists(String $key, String $val)
    {
        return Users::where($key, $val)->exists();
    }

    /**
     * @throws Exception
     */
    public function getAccounts(Int $id=null)
    {
        $users = Users::with('role')->with('permissions');
        if (is_null($id)) return $users->get();

        return $users->find($id);
    }

    /**
     * 
     */
    public function save(Array $data)
    {
        return Users::create([
            'name'      => $data['name'],
            'account'   => $data['account'],
            'email'     => $data['email'],
            'password'  => Crypto::encode($data['password']),
            'role_id'   => $data['role'],
        ]);
    }
    
    /**
     * 
     */
    public function update($account, $data): Bool
    {
        DB::beginTransaction();

        try {
            $account->name = $data['name'];
            $account->role_id = $data['role'];
            if (!empty($data['password'])) {
                $account->password = Crypto::encode($data['password']);
            }
            $account->save();

            DB::commit();
            return true;
        } catch (\Eception $e) {
            DB::rollback();
            return false;
        }
    }

    /**
     * 
     */
    public function delete(Int $id): Bool
    {
        $account = Users::find($id);

        if (!empty($account)) {
            return $account->delete();
        }

        return false;
    }

}
