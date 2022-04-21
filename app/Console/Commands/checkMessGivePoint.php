<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\MassGivePointUploads;
use App\Http\Models\GivePoints;
use Log;

class checkMessGivePoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkMessGivePoint';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check & update mess give points status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $records = $this->getMassRecords();
        
        $records->map(function($item) {
            if (!empty($this->isGivePointStatus($item->id, 'P'))) {
                $this->setMassRecord($item->id, 'P');
            } else if (empty($this->isGivePointStatus($item->id, 'N'))) {
                $ok = $this->countGivePoints($item->id, 'Y');
                $ng = $this->countGivePoints($item->id, 'F');
                $this->setMassRecord($item->id, 'Y', $ok, $ng);
            }
        });
        
    }

    private function setMassRecord(Int $id, String $process_status, Int $ok=null, Int $ng=null)
    {
        return MassGivePointUploads::find($id)->update([
            'process_status' => $process_status, 
            'ok_count'       => $ok, 
            'ng_count'       => $ng
        ]);
    }

    private function getMassRecords()
    {
        return MassGivePointUploads::where('process_status', 'N')->get();
    }

    private function isGivePointStatus(Int $id, String $status)
    {
        return GivePoints::where('upload_id', $id)->where('process_status', $status)->exists();
    }

    private function countGivePoints(Int $id, String $status)
    {
        return GivePoints::where('upload_id', $id)->where('process_status', $status)->count();
    }
}
