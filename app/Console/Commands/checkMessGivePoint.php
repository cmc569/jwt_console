<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\MassGivePointUploads;
use App\Http\Models\GivePoints;
use Log;
use File;

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
                
                $csv = null;
                if ($ng > 0) {
                    $csv = $this->createNgCsvFile($item->id);
                }

                $this->setMassRecord($item->id, 'Y', $ok, $ng, $csv);
            }
        });
        
    }

    private function setMassRecord(Int $id, String $process_status, Int $ok=null, Int $ng=null, String $csv=null)
    {
        return MassGivePointUploads::find($id)->update([
            'process_status' => $process_status, 
            'ok_count'       => $ok, 
            'ng_count'       => $ng,
            'ng_file_url'    => $csv,
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

    private function createNgCsvFile(Int $id)
    {
        $records = GivePoints::where('upload_id', $id)->where('process_status', 'F')->get();
        // print_r($records->toArray());exit;
        return $this->buildCsvFile($id, $records);
    }

    private function buildCsvFile($id, $records)
    {
        $fname = 'ng_'.$id.time().'.csv';
        $path = '/uploads/mass_upload/ng_csv';
        $fh = public_path($path);
        if (!File::isDirectory($fh)) {
            File::makeDirectory($fh, 0777, true); //mkdir 0777
        }
        $fh .= '/'.$fname;
        
        $header = '手機號碼,點數,到期日,發送時間,上傳時間'."\n";
        file_put_contents($fh, chr(0xEF).chr(0xBB).chr(0xBF), FILE_APPEND);
        file_put_contents($fh, $header, FILE_APPEND);
        if (empty($records)) {
            return false;
        } else {
            foreach ($records as $record) {
                $record['end_at'] = substr($record['end_at'], 0, 10);
                $body = "{$record['mobile']},{$record['point']},{$record['end_at']},{$record['send_at']},{$record['created_at']}\n";
                file_put_contents($fh, $body, FILE_APPEND);
            }

            $client = new \GuzzleHttp\Client();
            $url = 'https://project-burgerking-web-app-cms-dev-02.azurewebsites.net/api/upload/test';
            $res = $client->post($url, [
                'multipart' => [
                    [
                        'name'     => 'csv_file',
                        'contents' => fopen($fh, 'r'),
                        'filename' => '' //預設抓原始檔名
                    ],
                    [
                        'name'     => 'path',
                        'contents' => $path //選擇儲存位置
                    ]
                ]
            ]);

            if (is_file($fh)) {
                return $path.'/'.$fname;
            }

            return false;
        }
    }
}
