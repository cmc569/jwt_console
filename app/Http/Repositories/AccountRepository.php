<?php

namespace App\Http\Repositories;

use App\Crypto\Crypto;
use App\Http\Models\Users;
use App\Util\UtilTime;
use Exception;

class AccountRepository extends BaseRepository {

    /**
     * @throws Exception
     */
    public function getAccounts() {
        try {
            return Users::with('role')->with('permissions')->get();
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

}
