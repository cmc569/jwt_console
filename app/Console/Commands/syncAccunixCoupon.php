<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Repositories\TransTicketRepository;

class syncAccunixCoupon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncAccunixCoupon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync accunix coupon data to project';

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
        $repo = new TransTicketRepository;
        $coupons = $repo->syncPBCC();

        if (empty($coupons)) {
            return ;
        } else {
            $repo->syncPBC($coupons);
        }
    }
}
