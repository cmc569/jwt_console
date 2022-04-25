<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Util\SystexApi;
use App\Http\Models\GivePoints;
use Log;

class sendGivePoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendGivePoints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'manual give points to user';

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
        $systex = new SystexApi;

        // $data = $systex->AdjustPointPlus(env('SYSTEX_POINT_BONUSID'), '20221010', '8233091780000195', 30);
        // echo $systex->orderId."\n\n";
        // print_r($data);exit;

        // $data = $systex->AdjustPointMinus(env('SYSTEX_POINT_BONUSID'), '8233091780000245', 10);
        // echo $systex->orderId."\n\n";
        // print_r($data);exit;
        // $data = $systex->AdjustPointMinus(env('SYSTEX_POINT_BONUSID'), '8233091780000195', 30);
        // echo $systex->orderId."\n\n";
        // print_r($data);exit;

        // $data = $systex->QueryTxn('8233091780000245');
        // $data = $systex->QueryTxn('8233091780000195');
        // print_r($data);exit;

        // $data = $systex->QueryBonus('8233091780000245');
        // print_r($data);exit;
        // $data = $systex->QueryBonus('8233091780000195');
        // print_r($data);exit;

        // exit;

        $records = $this->getRecords();
        
        if (empty($records)) {
            return;
        }

        $ids = $records->pluck('id')->toArray();
        if (empty($this->setRecords($ids, 'P'))) {
            Log::error('DB發點變更為處理中狀態失敗(ids: '.print_r($ids, true).')');
            return;
        }

        $systex = new SystexApi;
        foreach ($records as $v) {
            $method = $v['operation'];
            if (!in_array($method, ['ADD', 'SUB'])) {
                $this->setRecords([$v['id']], 'F');
                continue;
            }

            $end_at = str_replace('-', '', substr($v['end_at'], 0, 10)) ?? null;
            if (($method == 'ADD') && !preg_match("/^\d{8}$/", $end_at)) {
                $this->setRecords([$v['id']], 'F');
                continue;
            }

            $card_no = $v['card_no'];
            if (!preg_match("/^\d+$/", $card_no)) {
                $this->setRecords([$v['id']], 'F');
                continue;
            }

            $point = $v['point'];
            if (!preg_match("/^\d+$/", $point)) {
                $this->setRecords([$v['id']], 'F');
                continue;
            }
            
            //
            $response = $this->pointHandler($systex, $method, $card_no, $point, $end_at);print_r($response);
            $status = ($response['ReturnCode'] == '0') ? 'Y' : 'F';
            Log::info('呼叫精誠加扣點api(id: '.$v['id'].') '.print_r($response, true));

            if (empty($this->setRecords([$v['id']], $status, json_encode($response, JSON_UNESCAPED_UNICODE)))) {
                Log::error('DB發點紀錄失敗(id: '.$v['id'].')');

                //精誠點數已發、需補正
                if ($status == 'Y') {
                    Log::info('精誠點數需補正(id: '.$v['id'].') ');

                    // $method = ($method == 'ADD') ? 'SUB' : 'ADD';
                    // $response = $this->pointHandler($systex, $method, $card_no, $point, $end_at);
                    // Log::info('精誠點數補(扣)回(id: '.$v['id'].') '.print_r($response, true));
                }
            } else {
                Log::error('DB發點紀錄完成(id: '.$v['id'].')');
            }
        }

    }

    private function getRecords(String $process_status='N')
    {
        return givePoints::where('process_status', $process_status)->where('send_at', '<=', date("Y-m-d H:i:s"))->get();
    }

    private function setRecords(Array $ids, String $process_status, String $response=null)
    {
        return givePoints::whereIn('id', $ids)->update(['process_status' => $process_status, ]);
    }

    private function pointHandler(SystexApi $systex, String $method, String $card_no, Int $point, String $end_at=null)
    {
        $response = null;

        switch ($method) {
            case 'ADD':
                $response = $systex->AdjustPointPlus(env('SYSTEX_POINT_BONUSID'), $end_at, $card_no, $point);
                break;
            case 'SUB':
                $response = $systex->AdjustPointMinus(env('SYSTEX_POINT_BONUSID'), $card_no, $point);
                break;
        }

        return [
            'order_id'  => $systex->orderId,
            'response'  => $response,
        ];
    }
}
