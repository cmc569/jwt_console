<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Repositories\MembersRepository;
use Illuminate\Support\Facades\File;
use App\Http\Services\MailService;

class csvOutput extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csvOutput';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'csv output and mail';

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
        $members_repository = new MembersRepository;
        $jobs = $members_repository->getCsvJob();

        $job_ids = $jobs->pluck('id')->toArray();

        if (empty($job_ids)) {
            return;
        }

        // $members_repository->updateCsvJob($job_ids, 'P');

        $jobs->map(function($item) use ($members_repository) {
            $fname = 'csv_'.$item->id.uniqid().'.csv';
            $rules = json_decode($item->rules);
            $csv = $members_repository->getMembers(null, $rules->filter, $rules->start_date, $rules->end_date, null, null, true);
            
            $fh = base_path().'/private/storage/csv';
            if (!File::isDirectory($fh)) {
                File::makeDirectory($fh, 0777, true); //mkdir 0777
            }
            $fh .= '/'.$fname;
            // echo $fh;
            $header = 'LineToken,姓名,卡號,性別,生日,手機,Email,加入時間'."\n";
            file_put_contents($fh, chr(0xEF).chr(0xBB).chr(0xBF), FILE_APPEND);
            file_put_contents($fh, $header, FILE_APPEND);
            if (!empty($csv)) {
                $csv['records']->map(function($d) use($fh) {
                    $d->gender = ($d->gender == 'M') ? '男' : '女';
                    $body = $d->user_token.','.$d->name.',No.'.$d->stored_card_no.','.$d->gender.','.substr($d->birthday, 0, 10).','.$d->mobile.','.$d->email.','.$d->created_at."\n";
                    file_put_contents($fh, $body, FILE_APPEND);
                });
            }

            $email = $item->user->email;
            $content = '此為系統自動發送，請勿回覆';
            \Log::info('Ask for member download. ('.$email.', '.$content.')');
            MailService::send($email, '會員列表', $content, $fh);
        });
    }
}
