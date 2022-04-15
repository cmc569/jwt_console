<?php

namespace App\Http\Repositories;

use App\Http\Models\GivePoints;
use App\Http\Models\MassGivePointUploads;
use App\Util\UtilTime;
use Exception;
use DB;

class GivePointsRepository extends BaseRepository
{
   
    public function getMassUploadRecords()
    {
        return MassGivePointUploads::all();
    }
}
