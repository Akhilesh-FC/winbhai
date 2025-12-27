<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AllCampaignCommissionService;


class CommissionController extends Controller
{
    public function runAllCampaignCommission()
    {
        AllCampaignCommissionService::calculateAndCreditAllCampaignsCommission();

        return response()->json([
            'status' => true,
            'message' => 'All campaigns commission calculated & credited'
        ]);
    }
}
