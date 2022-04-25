<?php

namespace App\Http\Repositories;

use Illuminate\Support\Facades\DB;

class NixCouponRepository extends BaseRepository
{
    public function getActivityCampaign()
    {
        $campaigns = DB::connection('accunix_db')
            ->select("
                    SELECT `title`, `campaign_guid`
                    FROM project_burgerking_coupon_campaigns
                    WHERE start_at < now()
                      AND end_at > now()
                      AND is_active = 1
                      AND count_ungifted > 0");

        return $campaigns;
    }

    public function getCoupons($campaignGuid)
    {
        $coupons = DB::connection('accunix_db')
            ->select("
                    SELECT `title`, `guid`
                    FROM project_burgerking_coupons
                    WHERE deleted_at IS NULL
                       AND campaign_guid = ?", [$campaignGuid]);

        return $coupons;
    }
}
