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
    public function getAccounts(Int $id=null) {
        $users = Users::with('role')->with('permissions');
        if (is_null($id)) return $users->get();

        return $users->find($id);
    }

}
