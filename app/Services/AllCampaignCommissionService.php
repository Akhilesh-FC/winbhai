<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class AllCampaignCommissionService
{
    public static function calculateAndCreditAllCampaignsCommission()
    {
        // 1️⃣ ALL campaigns
        $campaigns = DB::table('campaigns')->get();

        foreach ($campaigns as $campaign) {

            $campaignId = $campaign->id;
            $ownerUserId = $campaign->user_id;
            $realRevenue = (float) ($campaign->real_revenue ?? 0);

            if ($realRevenue <= 0) {
                continue;
            }

            /* =========================
               2️⃣ CAMPAIGN USERS
            ========================== */
            $campaignUserIds = DB::table('users')
                ->where('campaign_id', $campaignId)
                ->where('campaign_user_id', $ownerUserId)
                ->pluck('id')
                ->toArray();

            if (empty($campaignUserIds)) {
                continue;
            }

            /* =========================
               3️⃣ TOTAL DEPOSIT
            ========================== */
            $totalDeposit = DB::table('payins')
                ->whereIn('user_id', $campaignUserIds)
                ->where('status', 2)
                ->where('cash', '>', 0)
                ->sum('cash');

            /* =========================
               4️⃣ NET PROFIT / LOSS
            ========================== */
            $netPL = 0;

            // WINGO
            $netPL += DB::table('bets')
                ->whereIn('userid', $campaignUserIds)
                ->sum(DB::raw('win_amount - trade_amount'));

            // AVIATOR
            $netPL += DB::table('aviator_bet')
                ->whereIn('uid', $campaignUserIds)
                ->sum(DB::raw('win - amount'));

            // CHICKEN
            $netPL += DB::table('chicken_bets')
                ->whereIn('user_id', $campaignUserIds)
                ->sum(DB::raw('win_amount - amount'));

            // THIRD PARTY
            $netPL += DB::table('game_history')
                ->whereIn('user_id', $campaignUserIds)
                ->sum(DB::raw('win_amount - bet_amount'));

            /* =========================
               5️⃣ COMMISSION CALCULATION
               👉 same logic as tumhara
            ========================== */
            if ($netPL < 0) {
                $commission = abs($netPL) * ($realRevenue / 100);
            } elseif ($netPL == 0) {
                $commission = 0;
            } else {
                $commission = -($totalDeposit * ($realRevenue / 100));
            }

            $commission = round($commission, 2);

            if ($commission == 0) {
                continue;
            }

            /* =========================
               6️⃣ CREDIT TO OWNER WALLET
            ========================== */
            DB::table('users')
                ->where('id', $ownerUserId)
                ->increment('third_party_wallet', $commission);

            /* =========================
               7️⃣ OPTIONAL LOG (RECOMMENDED)
            ========================== */
            DB::table('commission_logs')->insert([
                'user_id'     => $ownerUserId,
                'campaign_id' => $campaignId,
                'amount'      => $commission,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return true;
    }
}
