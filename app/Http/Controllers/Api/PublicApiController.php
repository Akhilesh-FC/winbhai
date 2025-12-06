<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Payin;
use App\Models\Withdraw_history;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Setting;
use App\Models\All_image;
use App\Models\Slider;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Account_detail;
use DateTime;
use App\Models\Wallet_history;
use Illuminate\Support\Facades\Http;
use App\Models\CustomerService;
use Illuminate\Support\Facades\Log;
use App\Helper\jilli;
use URL;

class PublicApiController extends Controller
{
    public function affiliation_wallet_add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'amount'  => 'required|numeric|min:1'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status'  => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
    
        $userid = $request->user_id;
        $amount = $request->amount;
    
        // ---------- COMMISSION CALCULATION ----------
        $totalCommission = DB::table('users')
            ->where('referral_user_id', $userid)
            ->sum('commission');
    
        $withdrawnCommission = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->where('status', 2)
            ->where('type', 2)
            ->sum('amount');
    
        $availableCommission = max(0, $totalCommission - $withdrawnCommission);
    
        // 👉 Yahi final logic tumne bola tha
        if ($amount > $availableCommission) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient affiliate commission balance.'
            ], 400);
        }
    
        // Increase wallet
        DB::table('users')
            ->where('id', $userid)
            ->increment('wallet', $amount);
    
        return response()->json([
            'status' => 200,
            'message' => 'Amount added from affiliate commission successfully.',
            'amount_added' => $amount
        ]);
    }

    public function affiliation_usdtwithdraw(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'usdt_wallet_address_id' => 'required|integer|exists:usdt_wallet_address,id',
            'amount_inr' => 'required|numeric|min:940',
            'amount' => 'numeric|min:10',
            'type' => 'required|numeric'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }
    
        // Only Type 2 allowed
        if ($request->type != 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid type. Only type = 0 is allowed.'
            ], 400);
        }
    
        $userid = $request->user_id;
        $amount_inr = $request->amount_inr; // INR amount to withdraw (from commission)
        $usdt_amount = $request->amount;    // USDT amount user wants to receive
        $walletAddrId = $request->usdt_wallet_address_id;
        $type = 0;  // fixed for usdt affiliation withdraw
    
        // ---------- USDT min/max check ----------
        if ($usdt_amount < 10 || $usdt_amount > 5000) {
            return response()->json([
                'status' => 400,
                'message' => 'Minimum Withdraw is $10 and Maximum is $5000.'
            ], 400);
        }
    
        // ---------- Commission Calculation ----------
        // Total earned commission
        $totalCommission = DB::table('users')
            ->where('referral_user_id', $userid)
            ->sum('commission');
    
        // Total already withdrawn
        $withdrawnCommission = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->where('type', 0)
            ->where('status', 2)
            ->sum('amount');
    
        $availableCommission = max(0, $totalCommission - $withdrawnCommission);
    
        if ($amount_inr > $availableCommission) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient affiliate commission balance.'
            ], 400);
        }
    
        // ---------- Pending withdrawal check ----------
        $lastWithdrawal = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->where('type', 0)
            ->orderBy('created_at', 'desc')
            ->first();
    
        if ($lastWithdrawal && $lastWithdrawal->status == 1) {
            return response()->json([
                'status' => 400,
                'message' => 'Your previous withdrawal is still pending.'
            ], 400);
        }
    
        // ---------- Daily limit: 3 successful ----------
        $withdrawCount = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->where('type', 0)
            ->where('status', 2)
            ->whereDate('created_at', now())
            ->count();
    
        if ($withdrawCount >= 3) {
            return response()->json([
                'status' => 400,
                'message' => 'You can withdraw only 3 times in a day.'
            ], 400);
        }
    
        // ---------- Get USDT wallet address ----------
        $walletRow = DB::table('usdt_wallet_address')->where('id', $walletAddrId)->first();
    
        if (!$walletRow) {
            return response()->json([
                'status' => 400,
                'message' => 'USDT wallet address not found.'
            ], 400);
        }
    
        // ---------- Generate 20-digit order ID ----------
        $order_id =
            mt_rand(1000, 9999) .
            mt_rand(1000, 9999) .
            mt_rand(1000, 9999) .
            mt_rand(1000, 9999) .
            mt_rand(10, 99);
    
        // ---------- Convert INR to USDT ----------
        // formula: 1 USDT = 94 INR (or whatever you used earlier)
        $usdt_amount = $amount_inr / 94;
    
        DB::beginTransaction();
        try {
    
            // Insert into withdraw_history
            $data = [
                'user_id' => $userid,
                'account_id' => $walletAddrId,
                'usdt_wallet_address' => $walletRow->wallet_address,
                'amount' => $amount_inr,
                'usdt_amount' => $usdt_amount,
                'type' => 0,
                'order_id' => $order_id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
    
            $withdraw = DB::table('withdraw_histories')->insert($data);
    
            if (!$withdraw) {
                DB::rollBack();
                return response()->json(['status' => 500, 'message' => 'Failed to create withdrawal request.'], 500);
            }
    
            DB::commit();
    
            return response()->json([
                'status' => 200,
                'message' => 'USDT Affiliate withdrawal request submitted successfully.'
            ], 200);
    
        } catch (\Exception $e) {
    
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function affiliation_indianpay_withdraw(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'upi_id' => 'required|string',
            'amount' => 'required|numeric|min:500',
            'type' => 'required|numeric'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }
    
        // Type must be 2 only
        if ($request->type != 2) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid type. Only type = 2 is allowed.'
            ], 400);
        }
    
        $userid = $request->user_id;
        $upi_id = $request->upi_id;
        $amount = $request->amount;
        $type = 2;  // fixed affiliation IndianPay withdraw
    
        // ---------- COMMISSION CALCULATION ----------
        // Total earned commission
        $totalCommission = DB::table('users')
            ->where('referral_user_id', $userid)
            ->sum('commission');
    
        // Total withdrawn commission (successful only)
        $withdrawnCommission = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->where('status', 2)
            ->where('type', 2)
            ->sum('amount');
    
        // Available affiliate commission
        $availableCommission = max(0, $totalCommission - $withdrawnCommission);
    
        if ($amount > $availableCommission) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient affiliate commission balance.'
            ], 400);
        }
    
        // ---------- Check last pending withdrawal ----------
        $lastWithdrawal = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->where('type', 2)
            ->orderBy('created_at', 'desc')
            ->first();
    
        if ($lastWithdrawal && $lastWithdrawal->status == 1) {
            return response()->json([
                'status' => 400,
                'message' => 'Your previous withdrawal is pending.'
            ], 400);
        }
    
        // ---------- Daily limit 5 ----------
        $withdrawCount = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->where('type', 2)
            ->where('status', 2)
            ->whereDate('created_at', now())
            ->count();
    
        if ($withdrawCount >= 3) {
            return response()->json([
                'status' => 400,
                'message' => 'You can withdraw only 3 times per day.'
            ], 400);
        }
    
        // ---------- Generate 20-digit order_id ----------
        $order_id =
            mt_rand(1000, 9999) .
            mt_rand(1000, 9999) .
            mt_rand(1000, 9999) .
            mt_rand(1000, 9999) .
            mt_rand(10, 99);
    
        DB::beginTransaction();
        try {
            // INSERT withdrawal record
            $insertData = [
                'user_id' => $userid,
                'upi_id' => $upi_id,
                'amount' => $amount,
                'type' => $type,
                'order_id' => $order_id,
                'status' => 1, // pending
                'created_at' => now(),
                'updated_at' => now(),
            ];
    
            $withdraw = DB::table('withdraw_histories')->insert($insertData);
    
            if (!$withdraw) {
                DB::rollBack();
                return response()->json(['status' => 500, 'message' => 'Failed to create withdrawal request.'], 500);
            }
    
            DB::commit();
    
            return response()->json([
                'status' => 200,
                'message' => 'Affiliation IndianPay withdrawal request submitted successfully.'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function affiliate_withdraw(Request $request) 
    {
        $validator = Validator::make($request->all(), [ 
            'user_id'    => 'required',
            'account_id' => 'required',
            'type'       => 'required|numeric',///1 normal withdraw
            'amount'     => 'required|numeric'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
        
         // ⭐ TYPE VALIDATION — Only 1 allowed
        if ($request->type != 1) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid type. Only type = 1 is allowed.'
            ], 400);
        }
    
        $userid     = $request->user_id;
        $accountid  = $request->account_id;
        $type       = 1; // 🔒 FIXED AFTER VALIDATION
        $amount     = $request->amount;
    
        // ⭐ account_id request se hi aayega—no DB check required
        // ⭐ no need to check account_details table
    
        // -------------------------------------------------------------
        // ⭐ Commission Summary — Available withdrawal amount fetch karo
        // -------------------------------------------------------------
        $totalCommission = DB::table('users')
            ->where('referral_user_id', $userid)
            ->sum('commission');
    
        $withdrawnCommission = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->where('status', 2)
            ->sum('amount');
    
        $availableCommission = max(0, $totalCommission - $withdrawnCommission);
    
        if ($amount > $availableCommission) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient affiliate commission balance.'
            ], 400);
        }
    
        // -------------------------------------------------------------
        // Pending withdrawal check
        // -------------------------------------------------------------
        $lastWithdrawal = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->orderBy('id', 'desc')
            ->first();
    
        if ($lastWithdrawal && $lastWithdrawal->status == 1) {
            return response()->json([
                'status' => 400,
                'message' => 'Your previous withdrawal is pending.'
            ], 400);
        }
    
        // -------------------------------------------------------------
        // Limit 5 successful withdrawals per day
        // -------------------------------------------------------------
        $withdrawCount = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->where('status', 2)
            ->whereDate('created_at', now())
            ->count();
    
        if ($withdrawCount >= 3) {
            return response()->json([
                'status' => 400,
                'message' => 'You can withdraw only 3 times per day.'
            ], 400);
        }
    
        // -------------------------------------------------------------
        // Minimum / Maximum limit check
        // -------------------------------------------------------------
        if ($amount < 200 || $amount > 25000) {
            return response()->json([
                'status' => 400,
                'message' => 'Minimum withdraw 200 and maximum 25000 allowed.'
            ], 400);
        }
    
        // -------------------------------------------------------------
        // Create order ID
        // -------------------------------------------------------------
        $orderid = date('YmdHis') . rand(11111, 99999);
    
        // -------------------------------------------------------------
        // Insert withdrawal request
        // -------------------------------------------------------------
        DB::table('withdraw_histories')->insert([
            'user_id'   => $userid,
            'amount'    => $amount,
            'account_id'=> $accountid,
            'type'      => 'affiliate',   // ⭐ change name
            'order_id'  => $orderid,
            'status'    => 1,
            'typeimage' => "https://root.winbhai.in/uploads/fastpay_image.png",
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);
    
        return response()->json([
            'status' => 200,
            'message' => 'Affiliate withdrawal request submitted successfully.'
        ], 200);
    }

    public function indianpay_withdraw(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'upi_id' => 'required|string',
            'amount' => 'required|numeric|min:100',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }
    
        $userid = $request->user_id;
        $upi_id = $request->upi_id;
        $amount = $request->amount;
        $type = 2; // ✅ fixed UIndiapay ithdraw type
    
        // ✅ Minimum withdraw check
        if ($amount < 100) {
            return response()->json([
                'status' => 400,
                'message' => 'Minimum Withdraw is 100 INR.'
            ], 400);
        }
    
        // ✅ Check pending withdrawal
        $lastWithdrawal = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->orderBy('created_at', 'desc')
            ->first();
    
        if ($lastWithdrawal && $lastWithdrawal->status == 1) {
            return response()->json([
                'status' => 400,
                'message' => 'You cannot withdraw again until your previous request is approved or rejected.'
            ], 400);
        }
    
        // ✅ Limit to 5 successful withdrawals per day
        $withdrawCount = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->whereDate('created_at', now())
            ->where('status', 2)
            ->count();
    
        if ($withdrawCount >= 5) {
            return response()->json([
                'status' => 400,
                'message' => 'You can only withdraw 5 times in a day.'
            ], 400);
        }
    
        // ✅ Fetch user
        $user = DB::table('users')->where('id', $userid)->first();
    
        if (!$user) {
            return response()->json(['status' => 400, 'message' => 'User not found.'], 400);
        }
    
        if ($user->first_recharge != 1) {
            return response()->json([
                'status' => 400,
                'message' => 'First recharge is mandatory before withdrawal.'
            ], 400);
        }
    
        // ✅ Check wallet balance
        if ($user->wallet < $amount) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient Balance.'
            ], 400);
        }
    
        // ✅ Generate 20-digit order_id
        $order_id =
            mt_rand(1000, 9999) .
            mt_rand(1000, 9999) .
            mt_rand(1000, 9999) .
            mt_rand(1000, 9999) .
            mt_rand(10, 99);
    
        DB::beginTransaction();
        try {
            // ✅ Insert into withdraw_histories
            $insertData = [
                'user_id' => $userid,
                'upi_id' => $upi_id,
                'amount' => $amount,
                'type' => $type,
                'order_id' => $order_id,
                'status' => 1, // pending
                'created_at' => now(),
                'updated_at' => now(),
            ];
    
            $withdraw = DB::table('withdraw_histories')->insert($insertData);
    
            if (!$withdraw) {
                DB::rollBack();
                return response()->json(['status' => 500, 'message' => 'Failed to create withdrawal request.'], 500);
            }
    
            // // ✅ Insert record into wallet_histories
            // $walletHistory = [
            //     'userid' => $userid,
            //     'amount' => $amount,
            //     'subtypeid' => 7,
            //     'description' => 'indianpay Withdraw Request',
               
            //     //'status' => 1,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ];
            // DB::table('wallet_history')->insert($walletHistory);
    
            // ✅ Deduct from user's wallet
            $updated = DB::table('users')
                ->where('id', $userid)
                ->where('wallet', '>=', $amount)
                ->decrement('wallet', $amount);
    
            if ($updated === 0) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => 'Failed to deduct amount from wallet.'], 400);
            }
    
            DB::commit();
    
            return response()->json([
                'status' => 200,
                'message' => 'indianpay withdrawal request submitted successfully.'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function betSummaryProfit_loss(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'user_id'   => 'required|integer',
        'from_date' => 'nullable|date',
        'to_date'   => 'nullable|date',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first(),
        ]);
    }

    $userId = $request->user_id;
    $from = $request->from_date ? $request->from_date . ' 00:00:00' : null;
    $to = $request->to_date ? $request->to_date . ' 23:59:59' : null;

    // 🟢 1️⃣ WINGO (bets table)
    $betsQuery = DB::table('bets')
        ->where('userid', $userId);

    if ($from && $to) {
        $betsQuery->whereBetween('created_at', [$from, $to]);
    }

    $bets = $betsQuery
        ->selectRaw('COALESCE(SUM(trade_amount),0) as total_bet, COALESCE(SUM(win_amount),0) as total_win')
        ->first();

    $bets_profit = max(($bets->total_win ?? 0) - ($bets->total_bet ?? 0), 0);
    $bets_loss = max(($bets->total_bet ?? 0) - ($bets->total_win ?? 0), 0);

    // 🟢 2️⃣ AVIATOR (aviator_bet table)
    $aviatorQuery = DB::table('aviator_bet')
        ->where('uid', $userId);

    if ($from && $to) {
        $aviatorQuery->whereBetween('datetime', [$from, $to]);
    }

    $aviator = $aviatorQuery
        ->selectRaw('COALESCE(SUM(amount),0) as total_bet, COALESCE(SUM(win),0) as total_win')
        ->first();

    $aviator_profit = max(($aviator->total_win ?? 0) - ($aviator->total_bet ?? 0), 0);
    $aviator_loss = max(($aviator->total_bet ?? 0) - ($aviator->total_win ?? 0), 0);

    // 🟢 3️⃣ CHICKEN ROAD (chicken_bets table)
    $chickenQuery = DB::table('chicken_bets')
        ->where('user_id', $userId);

    if ($from && $to) {
        $chickenQuery->whereBetween('created_at', [$from, $to]);
    }

    $chicken = $chickenQuery
        ->selectRaw('COALESCE(SUM(amount),0) as total_bet, COALESCE(SUM(win_number),0) as total_win')
        ->first();

    $chicken_profit = max(($chicken->total_win ?? 0) - ($chicken->total_bet ?? 0), 0);
    $chicken_loss = max(($chicken->total_bet ?? 0) - ($chicken->total_win ?? 0), 0);

    // 🧮 GRAND TOTALS
    $grand_total_bet = ($bets->total_bet ?? 0) + ($aviator->total_bet ?? 0) + ($chicken->total_bet ?? 0);
    $grand_total_win = ($bets->total_win ?? 0) + ($aviator->total_win ?? 0) + ($chicken->total_win ?? 0);
    $grand_profit = max($grand_total_win - $grand_total_bet, 0);
    $grand_loss = max($grand_total_bet - $grand_total_win, 0);

    // ✅ Final JSON Response
    return response()->json([
        'status' => 200,
        'message' => 'Bet summary fetched successfully',
        'data' => [
            'from_date' => $request->from_date ?? 'All time',
            'to_date' => $request->to_date ?? 'All time',

            'wingo' => [
                'total_bet' => $bets->total_bet,
                'total_win' => $bets->total_win,
                'profit' => $bets_profit,
                'loss' => $bets_loss,
            ],
            'aviator' => [
                'total_bet' => $aviator->total_bet,
                'total_win' => $aviator->total_win,
                'profit' => $aviator_profit,
                'loss' => $aviator_loss,
            ],
            'chicken' => [
                'total_bet' => $chicken->total_bet,
                'total_win' => $chicken->total_win,
                'profit' => $chicken_profit,
                'loss' => $chicken_loss,
            ],
            'grand_total' => [
                'total_bet' => $grand_total_bet,
                'total_win' => $grand_total_win,
                'profit' => $grand_profit,
                'loss' => $grand_loss,
            ]
        ],
    ]);
}
    
    public function campaign_create(Request $request)
    {
        $userId = $request->input('user_id');
        $campaignName = $request->input('campaign_name');
        $uniqueCode = $request->input('unique_code');
    
        // ✅ Step 1: Validation
        if (!$userId || !$campaignName || !$uniqueCode) {
            return response()->json([
                'status' => 400,
                'message' => 'user_id, campaign_name, and unique_code are required',
            ], 400);
        }
    
        // ✅ Step 2: Check if user exists
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }
    
        // ✅ Step 3A: Check if unique_code already exists in users table (referral_code)
        $existingInUsers = DB::table('users')->where('referral_code', $uniqueCode)->first();
        if ($existingInUsers) {
            return response()->json([
                'status' => 400,
                'message' => 'This unique code already exists in users table. Please choose another one.',
            ], 400);
        }
    
        // ✅ Step 3B: Check if unique_code already exists in campaigns table
        $existingInCampaigns = DB::table('campaigns')->where('unique_code', $uniqueCode)->first();
        if ($existingInCampaigns) {
            return response()->json([
                'status' => 400,
                'message' => 'This unique code already exists in campaigns table. Please choose another one.',
            ], 409);
        }
    
        // ✅ Step 4: Generate referral link
       $referralLink = "https://winbhai.in/signup?campaign=" . $uniqueCode;

    
        // ✅ Step 5: Insert into campaigns table
        DB::table('campaigns')->insert([
            'user_id' => $userId,
            'campaign_name' => $campaignName,
            'unique_code' => $uniqueCode,
            'referral_link' => $referralLink,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        // ✅ Step 6: Return response
        return response()->json([
            'status' => 200,
            'message' => 'Campaign created successfully',
            'data' => [
                'user_id' => $userId,
                'campaign_name' => $campaignName,
                'unique_code' => $uniqueCode,
                'referral_link' => $referralLink,
            ],
        ]);
    }

    public function campaign_list(Request $request)
    {
        $userId = $request->input('user_id');
    
        if (!$userId) {
            return response()->json([
                'status' => 400,
                'message' => 'User ID is required',
            ], 400);
        }
    
        $campaigns = DB::table('campaigns')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();
    
        if ($campaigns->isEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'No campaigns found for this user',
                'data' => [],
            ]);
        }
    
        return response()->json([
            'status' => 200,
            'message' => 'Campaign list fetched successfully',
            'data' => $campaigns,
        ]);
    }

    public function campaign_summary(Request $request)
    {
        $campaignId = $request->input('campaign_id');
    
        if (!$campaignId) {
            return response()->json([
                'status' => false,
                'message' => 'campaign_id is required',
            ], 400);
        }
    
        // ✅ Step 1: Get Campaign Details
        $campaign = DB::table('campaigns')->where('id', $campaignId)->first();
        if (!$campaign) {
            return response()->json([
                'status' => false,
                'message' => 'Campaign not found',
            ], 404);
        }
    
        $uniqueCode = $campaign->unique_code;
    
        // ✅ Step 2: Get All Users Registered Using This Campaign Code
        $registeredUsers = DB::table('users')
            ->where('referral_code', $uniqueCode)
            ->get();
    
        $totalRegistrations = $registeredUsers->count();
        $userIds = $registeredUsers->pluck('id')->toArray();
    
        // ✅ Default values (initialize all to 0)
        $firstDepositUsers = 0;
        $numberDeposits = 0;
        $totalDeposit = 0;
        $totalWithdrawal = 0;
        $totalCommission = 0;
        $linkClicks = 0;
    
        // ✅ Step 3: If users exist, calculate details
        if (!empty($userIds)) {
            // 🟩 First Deposit Users
            $firstDepositUsers = DB::table('users')
                ->whereIn('id', $userIds)
                ->whereNotNull('first_recharge_amount')
                ->where('first_recharge_amount', '>', 0)
                ->count();
    
            // 🟩 Total Deposits
            $deposits = DB::table('payins')
                ->whereIn('user_id', $userIds)
                ->where('status', 1)
                ->get();
    
            $numberDeposits = $deposits->count();
            $totalDeposit = $deposits->sum('cash');
    
            // 🟩 Total Withdrawals (if table exists)
            if (Schema::hasTable('payouts')) {
                $withdrawals = DB::table('payouts')
                    ->whereIn('user_id', $userIds)
                    ->where('status', 1)
                    ->get();
    
                $totalWithdrawal = $withdrawals->sum('amount');
            }
    
            // 🟩 Total Commission
            $totalCommission = $registeredUsers->sum('commission');
    
            // 🟩 Link Clicks (if link_clicks table exists)
            if (Schema::hasTable('link_clicks')) {
                $linkClicks = DB::table('link_clicks')
                    ->where('campaign_id', $campaignId)
                    ->count();
            }
        }
    
        // ✅ Step 4: Final consistent response
        return response()->json([
            'status' => true,
            'message' => 'Campaign analytics fetched successfully',
            'data' => [
                'Campaign_ID' => $campaignId,
                'Campaign_Name' => $campaign->campaign_name,
                'Unique_Code' => $uniqueCode,
                'Registrations' => (int)$totalRegistrations,
                'First_Deposits' => (int)$firstDepositUsers,
                'Number_Deposits' => (int)$numberDeposits,
                'Total_Deposit' => (float)$totalDeposit,
                'Total_Withdrawal' => (float)$totalWithdrawal,
                'Your_Commission' => (float)$totalCommission,
                'Link_Clicks' => (int)$linkClicks,
                'Transaction' => (int)$numberDeposits,
            ],
        ]);
    }

    public function campaign_analytics(Request $request)
    {
        $userId = $request->input('user_id');
    
        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'user_id is required',
            ], 400);
        }
    
        // ✅ Step 1: Fetch all campaigns of this user
        $campaigns = DB::table('campaigns')
            ->where('user_id', $userId)
            ->get();
    
        if ($campaigns->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No campaigns found for this user',
            ], 404);
        }
    
        // Get all unique codes for this user's campaigns
        $uniqueCodes = $campaigns->pluck('unique_code')->toArray();
    
        // ✅ Step 2: Get all users who registered via any of these campaign codes
        $registeredUsers = DB::table('users')
            ->whereIn('referral_code', $uniqueCodes)
            ->get();
    
        $userIds = $registeredUsers->pluck('id')->toArray();
    
        // Initialize all totals
        $totalRegistrations = 0;
        $firstDeposits = 0;
        $totalDeposit = 0;
        $totalWithdrawal = 0;
        $totalLoss = 0;
        $totalCommission = 0;
    
        if (!empty($userIds)) {
            // 🟩 Total registrations
            $totalRegistrations = count($userIds);
    
            // 🟩 First Deposits
            $firstDeposits = DB::table('users')
                ->whereIn('id', $userIds)
                ->whereNotNull('first_recharge_amount')
                ->where('first_recharge_amount', '>', 0)
                ->count();
    
            // 🟩 Total Deposit
            $totalDeposit = DB::table('payins')
                ->whereIn('user_id', $userIds)
                ->where('status', 1)
                ->sum('cash');
    
            // 🟩 Total Withdrawal
            $totalWithdrawal = DB::table('withdraw_histories')
                ->whereIn('user_id', $userIds)
                ->where('status', 1)
                ->sum('amount');
    
            // 🟩 Total Commission
            $totalCommission = DB::table('users')
                ->whereIn('id', $userIds)
                ->sum('commission');
    
            // 🟩 Total Loss
            $totalLoss = DB::table('bets')
                ->whereIn('user_id', $userIds)
                ->sum('amount');
        }
    
        // ✅ Step 3: All-time totals (for comparison)
        $allTimeRegistrations = DB::table('users')->count();
        $allTimeFirstDeposits = DB::table('users')
            ->whereNotNull('first_recharge_amount')
            ->where('first_recharge_amount', '>', 0)
            ->count();
        $allTimeTotalDeposit = DB::table('payins')->where('status', 1)->sum('cash');
        $allTimeTotalWithdrawal = DB::table('withdraw_histories')->where('status', 1)->sum('amount');
        $allTimeTotalCommission = DB::table('users')->sum('commission');
        $allTimeTotalLoss = DB::table('bets')->sum('amount');
    
        // ✅ Step 4: Daily breakdown (last 7 days)
        $dailyData = [];
        for ($i = 0; $i < 7; $i++) {
            $date = now()->subDays($i)->toDateString();
    
            $dailyRegistrations = DB::table('users')
                ->whereDate('created_at', $date)
                ->whereIn('referral_code', $uniqueCodes)
                ->count();
    
            $dailyFirstDeposits = DB::table('users')
                ->whereDate('created_at', $date)
                ->whereIn('referral_code', $uniqueCodes)
                ->whereNotNull('first_recharge_amount')
                ->where('first_recharge_amount', '>', 0)
                ->count();
    
            $dailyTotalDeposit = DB::table('payins')
                ->whereDate('created_at', $date)
                ->whereIn('user_id', $userIds)
                ->where('status', 1)
                ->sum('cash');
    
            $dailyTotalWithdrawal = DB::table('withdraw_histories')
                ->whereDate('created_at', $date)
                ->whereIn('user_id', $userIds)
                ->where('status', 1)
                ->sum('amount');
    
            $dailyTotalCommission = DB::table('users')
                ->whereDate('created_at', $date)
                ->whereIn('id', $userIds)
                ->sum('commission');
    
            $dailyTotalLoss = DB::table('bets')
                ->whereDate('created_at', $date)
                ->whereIn('user_id', $userIds)
                ->sum('amount');
    
            $dailyData[] = [
                'date' => $date,
                'registrations' => (int) $dailyRegistrations,
                'first_deposits' => (int) $dailyFirstDeposits,
                'total_deposit' => (float) $dailyTotalDeposit,
                'total_withdrawal' => (float) $dailyTotalWithdrawal,
                'total_commission' => (float) $dailyTotalCommission,
                'total_loss' => (float) $dailyTotalLoss,
            ];
        }
    
        // ✅ Step 5: Final JSON Response
        return response()->json([
            'status' => true,
            'message' => 'User campaign analytics summary',
            'data' => [
                'user_id' => $userId,
                'total_campaigns' => $campaigns->count(),
                'campaigns' => $campaigns,
                'summary' => [
                    'registrations' => (int)$totalRegistrations,
                    'first_deposits' => (int)$firstDeposits,
                    'total_deposit' => (float)$totalDeposit,
                    'total_withdrawal' => (float)$totalWithdrawal,
                    'total_commission' => (float)$totalCommission,
                    'total_loss' => (float)$totalLoss,
                ],
                'all_time' => [
                    'registrations' => (int)$allTimeRegistrations,
                    'first_deposits' => (int)$allTimeFirstDeposits,
                    'total_deposit' => (float)$allTimeTotalDeposit,
                    'total_withdrawal' => (float)$allTimeTotalWithdrawal,
                    'total_commission' => (float)$allTimeTotalCommission,
                    'total_loss' => (float)$allTimeTotalLoss,
                ],
                'daily_breakdown' => $dailyData,
            ]
        ]);
    }
    
    public function campaign_commission_summary(Request $request)
    {
    $userId = $request->input('user_id');

    if (!$userId) {
        return response()->json([
            'status' => false,
            'message' => 'user_id is required',
        ], 400);
    }

    // ✅ Step 1: Get all campaigns of this user
    $campaigns = DB::table('campaigns')
        ->where('user_id', $userId)
        ->get();

    if ($campaigns->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No campaigns found for this user',
        ], 404);
    }

    // ✅ Step 2: Get unique codes of all campaigns
    $uniqueCodes = $campaigns->pluck('unique_code')->toArray();

    // ✅ Step 3: Get all users registered through those campaigns
    $registeredUsers = DB::table('users')
        ->whereIn('referral_code', $uniqueCodes)
        ->get();

    $userIds = $registeredUsers->pluck('id')->toArray();

    // Initialize
    $totalCommission = 0;
    $withdrawnCommission = 0;
    $availableToWithdraw = 0;

    if (!empty($userIds)) {
        // 🟩 Total commission earned from referred users
        $totalCommission = DB::table('users')
            ->whereIn('id', $userIds)
            ->sum('commission');
    }

    // 🟩 Total withdrawn commission
    $withdrawnCommission = DB::table('withdraw_histories')
        ->where('user_id', $userId)
        ->where('status', 1)
        ->sum('amount');

    // 🟩 Ensure available never goes negative
    $availableToWithdraw = max(0, $totalCommission - $withdrawnCommission);
    
    // 🟩 Revenue Data (Dynamic)
   // 🟩 Revenue Data (Dynamic, only 1 value return)
$revenueData = DB::table('revenue')
    ->select('revenue')
    ->first();

$revenue = $revenueData ? $revenueData->revenue : 0;



    // ✅ Final Response
    return response()->json([
        'status' => true,
        'message' => 'Commission summary fetched successfully',
       'data' => [
    'total_commission'       => (float)$totalCommission,
    'available_to_withdraw'  => (float)$availableToWithdraw,
    'revenue'                => $revenue   // <--- now single value
]

    ]);
}

    public function AccountStatement(Request $request)
    {
    $userId = $request->input('user_id');

    if (!$userId) {
        return response()->json([
            'status' => 400,
            'message' => 'User ID is required',
        ], 400);
    }

    // 🟢 1️⃣ Get deposits (payins)
    $credits = DB::table('payins')
        ->select(
            DB::raw('DATE(created_at) as date_time'),
            DB::raw('SUM(cash) as total_credit'),
            DB::raw('COUNT(*) as credit_count')
        )
        ->where('user_id', $userId)
        ->groupBy(DB::raw('DATE(created_at)'))
        ->get();

    // 🟢 2️⃣ Get withdrawals (withdraw_histories)
    $debits = DB::table('withdraw_histories')
        ->select(
            DB::raw('DATE(created_at) as date_time'),
            DB::raw('SUM(amount) as total_debit'),
            DB::raw('COUNT(*) as debit_count')
        )
        ->where('user_id', $userId)
        ->groupBy(DB::raw('DATE(created_at)'))
        ->get();

    // 🧩 Merge results date-wise
    $summary = collect();

    // Add all credit entries
    foreach ($credits as $c) {
        $summary->put($c->date_time, [
            'date_time' => $c->date_time,
            'credit' => (float) $c->total_credit,
            'debit' => 0,
            'balance' => (float) $c->total_credit,
            'description' => 'Deposit',
            'round' => $c->credit_count,
        ]);
    }

    // Merge debit entries
    foreach ($debits as $d) {
        if ($summary->has($d->date_time)) {
            $entry = $summary->get($d->date_time);
            $entry['debit'] = (float) $d->total_debit;
            $entry['balance'] = $entry['credit'] - $entry['debit'];

            // 🧮 Ensure no negative balance
            if ($entry['balance'] < 0) {
                $entry['balance'] = 0;
            }

            $entry['round'] = $entry['round'] + $d->debit_count;

            // Description handling
            $entry['description'] = ($entry['credit'] > 0 && $entry['debit'] > 0)
                ? 'Deposit & Withdraw'
                : ($entry['debit'] > 0 ? 'Withdraw' : 'Deposit');

            $summary->put($d->date_time, $entry);
        } else {
            $balance = 0 - (float) $d->total_debit;

            // 🧮 Ensure no negative balance
            if ($balance < 0) {
                $balance = 0;
            }

            $summary->put($d->date_time, [
                'date_time' => $d->date_time,
                'credit' => 0,
                'debit' => (float) $d->total_debit,
                'balance' => $balance,
                'description' => 'Withdraw',
                'round' => $d->debit_count,
            ]);
        }
    }

    // 🕒 Sort by date (latest first)
    $summary = $summary->sortByDesc('date_time')->values();

    // ✅ Final Response
    return response()->json([
        'status' => 200,
        'message' => 'Wallet summary fetched successfully',
        'data' => $summary,
    ]);
}

    public function betHistory_winbhai(Request $request)
    {
    $userId = $request->input('user_id');

    if (!$userId) {
        return response()->json([
            'status' => 400,
            'message' => 'User ID is required',
        ], 400);
    }

    // 🟢 1️⃣ WINGO (bets table)
    $bets = DB::table('bets')
        ->select('id', 'game_id', 'amount', 'trade_amount', 'win_amount', 'games_no', 'win_number', 'status', 'created_at', 'updated_at')
        ->where('userid', $userId)
        ->whereIn('game_id', [1, 2, 3, 4])
        ->get()
        ->map(function ($bet) {
            // Game title mapping
            $gameTitles = [
                1 => "Wingo 30 sec",
                2 => "Wingo 1 min",
                3 => "Wingo 3 min",
                4 => "Wingo 5 min",
            ];

            // Virtual multiplier
            $virtual = DB::table('virtual_games')
                ->where('number', $bet->amount)
                ->first();

            $multiplier = $virtual->multiplier ?? 0;

            // 🧮 Profit/Loss calc (negative na ho)
            $profitLoss = ($bet->win_amount ?? 0) - ($bet->trade_amount ?? 0);
            if ($profitLoss < 0) {
                $profitLoss = 0;
            }

            // ✅ Win/Loss status
            $winStatus = $profitLoss > 0 ? 'Win' : 'Loss';

            return [
                'bet_id' => $bet->games_no,
                'title' => $gameTitles[$bet->game_id] ?? 'Wingo Game',
                'rate' => $multiplier,
                'stake' => $bet->trade_amount,
                'profit_loss' => $profitLoss,
                'win_status' => $winStatus,
                'result' => $bet->win_number ?? null,
                'placed_at' => $bet->created_at,
                'settled_at' => $bet->updated_at,
            ];
        });

    // 🟢 2️⃣ AVIATOR (aviator_bet table)
    $aviator = DB::table('aviator_bet')
        ->select('id', 'amount', 'multiplier', 'win', 'game_sr_num', 'status', 'datetime', 'updated_at')
        ->where('uid', $userId)
        ->get()
        ->map(function ($bet) {
            // 🧮 Profit/Loss calc (negative na ho)
            $profitLoss = ($bet->win ?? 0) - ($bet->amount ?? 0);
            if ($profitLoss < 0) {
                $profitLoss = 0;
            }

            // ✅ Win/Loss status
            $winStatus = $profitLoss > 0 ? 'Win' : 'Loss';

            return [
                'bet_id' => $bet->game_sr_num,
                'title' => 'Aviator Bets',
                'rate' => $bet->multiplier ?? 0,
                'stake' => $bet->amount,
                'profit_loss' => $profitLoss,
                'win_status' => $winStatus,
                'result' => $bet->multiplier ?? 0,
                'placed_at' => $bet->datetime,
                'settled_at' => $bet->updated_at,
            ];
        });

    // 🟢 3️⃣ CHICKEN ROAD (chicken_bets table)
    $chicken = DB::table('chicken_bets')
        ->select('id', 'user_id', 'amount', 'win_number', 'multiplier', 'status', 'created_at', 'updated_at')
        ->where('user_id', $userId)
        ->get()
        ->map(function ($bet) {
            // 🧮 Profit/Loss calc (negative na ho)
            $profitLoss = ($bet->win_number ?? 0) - ($bet->amount ?? 0);
            if ($profitLoss < 0) {
                $profitLoss = 0;
            }

            // ✅ Win/Loss status
            $winStatus = $profitLoss > 0 ? 'Win' : 'Loss';

            return [
                'bet_id' => $bet->id,
                'title' => 'Chicken Road',
                'rate' => $bet->multiplier ?? 0,
                'stake' => $bet->amount,
                'profit_loss' => $profitLoss,
                'win_status' => $winStatus,
                'result' => $bet->multiplier ?? 0,
                'placed_at' => $bet->created_at,
                'settled_at' => $bet->updated_at,
            ];
        });
        
        
        $jili = DB::table('game_history')
    ->select('id', 'game_id', 'game_name', 'game_round', 'bet_amount', 'win_amount', 'wallet_before', 'wallet_after', 'callback_time', 'created_at')
    ->where('user_id', $userId)
    ->get()
    ->map(function ($bet) {

        // 🧮 Profit/Loss = win_amount - bet_amount
        $profitLoss = ($bet->win_amount ?? 0) - ($bet->bet_amount ?? 0);
        if ($profitLoss < 0) {
            $profitLoss = 0;
        }

        // 🟢 Win / Loss status
        $winStatus = $profitLoss > 0 ? "Win" : "Loss";

        return [
            'bet_id'       => $bet->game_round ?? $bet->id,
            'title'        => $bet->game_name ?? "JILI Game",
            'rate'         => 0, // JILI multiplier not provided
            'stake'        => $bet->bet_amount,
            'profit_loss'  => $profitLoss,
            'win_status'   => $winStatus,
            'result'       => $bet->win_amount,
           // 'wallet_before'=> $bet->wallet_before,
            //'wallet_after' => $bet->wallet_after,
            'placed_at'    => $bet->created_at,
            'settled_at'   => $bet->callback_time,
        ];
    });

        
        

    // ✅ Final JSON Response
    return response()->json([
        'status' => 200,
        'message' => 'Bet history fetched successfully',
        'data' => [
            'bets' => $bets,
            'aviator_bets' => $aviator,
            'chicken_bets' => $chicken,
            'jili_bets'     => $jili,   // ⭐ NEW JILI HISTORY
        ],
    ]);
}

    public function getPendingBets(Request $request)
    {
    $userId = $request->input('user_id');

    if (!$userId) {
        return response()->json([
            'status' => 400,
            'message' => 'User ID is required',
        ], 400);
    }

    // 🧩 Helper function to format & count only status = 0 bets
    $formatBets = function ($bets, $tableType) {
        $formatted = [];
        $count = 1;

        $gameName = match ($tableType) {
            'bets' => 'Wingo',
            'aviator_bet' => 'Aviator',
            'chicken_bets' => 'Chicken Road',
            default => 'Unknown',
        };

        foreach ($bets as $bet) {
            // sirf pending (status = 0) bets ka hi count
            if ($bet->status == 0) {
                $formatted[] = [
                    'id' => $bet->id,
                    'user_id' => $bet->userid ?? $bet->uid ?? $bet->user_id,
                    'amount' => $bet->amount,
                    'game_id' => $bet->game_id,
                    'game_name' => $gameName,
                    'status' => $bet->status,
                    'placed_date_time' => date('d-m-Y h:i:s A', strtotime($bet->created_at)),
                    'bet_sequence' => $count++, // sequence increase only for pending bets
                ];
            }
        }

        return $formatted;
    };

    // 🟢 Fetch pending bets from each table
    $bets = DB::table('bets')
        ->select('id', 'userid', 'amount', 'game_id', 'status', 'created_at')
        ->where('userid', $userId)
        ->orderBy('created_at', 'asc')
        ->get();

    $aviator = DB::table('aviator_bet')
        ->select('id', 'uid', 'amount', 'game_id', 'status', 'created_at')
        ->where('uid', $userId)
        ->orderBy('created_at', 'asc')
        ->get();

    $chicken = DB::table('chicken_bets')
        ->select('id', 'user_id', 'amount', 'game_id', 'status', 'created_at')
        ->where('user_id', $userId)
        ->orderBy('created_at', 'asc')
        ->get();

    // ✅ Final response
    return response()->json([
        'status' => 200,
        'message' => 'Pending bets fetched successfully',
        'data' => [
            'bets' => $formatBets($bets, 'bets'),
            'aviator_bet' => $formatBets($aviator, 'aviator_bet'),
            'chicken_bets' => $formatBets($chicken, 'chicken_bets'),
        ],
    ]);
}

    public function usdtwithdraw(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'usdt_wallet_address_id' => 'required|integer|exists:usdt_wallet_address,id',
            'amount_inr' => 'required|numeric|min:940',
            'amount' => 'numeric|min:10',
            'type' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }
    
        $userid = $request->user_id;
        $amount_inr = $request->amount_inr; // INR amount to deduct
        $usdt_amount = $request->amount;    // USDT amount (for limits etc)
        $walletAddrId = $request->usdt_wallet_address_id;
        $type = $request->type;
    
        // 1. Minimum and maximum amount check (keep same logic)
        if ($usdt_amount < 10 || $usdt_amount > 5000) {
            return response()->json([
                'status' => 400,
                'message' => 'Minimum Withdraw is $10 and Maximum is $5000.'
            ], 400);
        }
    
        // 2. Check if there's a pending withdrawal
        $lastWithdrawal = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->orderBy('created_at', 'desc')
            ->first();
    
        if ($lastWithdrawal && $lastWithdrawal->status == 1) {
            return response()->json([
                'status' => 400,
                'message' => 'You cannot withdraw again until your previous request is approved or rejected.'
            ], 400);
        }
    
        // 3. Limit to three successful withdrawals per day
        $withdrawCount = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->whereDate('created_at', now())
            ->where('status', 2) // Assuming 2 is for successful
            ->count();
    
        if ($withdrawCount >= 3) {
            return response()->json([
                'status' => 400,
                'message' => 'You can only withdraw 3 times in a day.'
            ], 400);
        }
    
        // 4. Check first recharge and betting condition (kept as before)
        $user = DB::table('users')->where('id', $userid)->first();
    
        if (!$user) {
            return response()->json(['status' => 400, 'message' => 'User not found.'], 400);
        }
    
        if ($user->recharge > 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Need to bet amount 0 to be able to Withdraw.'
            ], 400);
        }
    
        if ($user->first_recharge != 1) {
            return response()->json([
                'status' => 400,
                'message' => 'First recharge is mandatory.'
            ], 400);
        }
    
        // 5. Check wallet balance
        if ($user->wallet < $amount_inr) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient Balance.'
            ], 400);
        }
    
        // Verify usdt_wallet_address row and get actual address (already validated by exists rule,
        // but fetch to store address text in withdraw_histories)
        $walletRow = DB::table('usdt_wallet_address')->where('id', $walletAddrId)->first();
        if (!$walletRow) {
            return response()->json([
                'status' => 400,
                'message' => 'USDT wallet address not found.'
            ], 400);
        }
    
        // Generate 20-digit numeric order_id
        $order_id = 
            mt_rand(1000, 9999) . 
            mt_rand(1000, 9999) . 
            mt_rand(1000, 9999) . 
            mt_rand(1000, 9999) . 
            mt_rand(10, 99);
    
        // Use DB transaction: insert withdraw_histories and deduct wallet atomically
        DB::beginTransaction();
        try {
             $usdt_amount = $amount_inr * 94;
            $insertData = [
                'user_id' => $userid,
                // store the account id (usdt_wallet_address id) as requested
                'account_id' => $walletAddrId,
                // also store the actual wallet address string for convenience
                'usdt_wallet_address' => $walletRow->wallet_address ?? null,
                'amount' => $amount_inr,
                'usdt_amount' => $usdt_amount,
                'type' => $type,
                'order_id' => $order_id,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
    
            $withdraw = DB::table('withdraw_histories')->insert($insertData);
    
            if (!$withdraw) {
                DB::rollBack();
                return response()->json(['status' => 500, 'message' => 'Failed to create withdrawal request.'], 500);
            }
    
            // Deduct from user's wallet
            $updated = DB::table('users')
                ->where('id', $userid)
                ->where('wallet', '>=', $amount_inr)
                ->decrement('wallet', $amount_inr);
    
            if ($updated === 0) {
                // wallet deduction failed (possible race / insufficient balance)
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => 'Failed to deduct amount from wallet.'], 400);
            }
    
            DB::commit();
    
            return response()->json([
                'status' => 200,
                'message' => 'USDT Withdrawal request submitted successfully'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error if you have logging
            // Log::error('usdtwithdraw error: '.$e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }
	
    public function getAllNotices()
    {
        $notices = DB::select('SELECT title, content, image, status FROM Notice');
    
        return response()->json([
            'status' => 'success',
            'data' => $notices,
        ]);
    }
    
    public function country(Request $request)
	{
		$search = $request->input('search');

		// Fetch all columns from the country table with search on multiple columns
		$query = DB::table('country');

		if (!empty($search)) {
			$query->where('sortname', 'LIKE', "%{$search}%")
				->orWhere('name', 'LIKE', "%{$search}%")
				->orWhere('phone_code', 'LIKE', "%{$search}%");
		}

		$countries = $query->get();

		return response()->json([
			'status' => 'success',
			'data' => $countries,
		]);
	}

	protected function generateRandomUID() {
					$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$digits = '0123456789';

					$uid = '';

					// Generate first 4 alphabets
					for ($i = 0; $i < 4; $i++) {
						$uid .= $alphabet[rand(0, strlen($alphabet) - 1)];
					}

					// Generate next 4 digits
					for ($i = 0; $i < 4; $i++) {
						$uid .= $digits[rand(0, strlen($digits) - 1)];
					}

					return $this->check_exist_memid($uid);
					
				}

	protected function check_exist_memid($uid){
					$check = DB::table('users')->where('u_id',$uid)->first();
					if($check){
						return $this->generateRandomUID(); // Call the function using $this->
					} else {
						return $uid;
					}
				}
				
//     public function login(Request $request)
//     {
//     // Base validation
//     $rules = [
//         'identity'    => 'required|string',
//         'password'    => 'required|string|min:6',
//         'country_code' => 'nullable'
//     ];

//     $validator = Validator::make($request->all(), $rules);

//     // Conditionally require country_code only when identity is purely numeric (mobile login)
//     $validator->sometimes('country_code', 'required|string', function ($input) {
//         return ctype_digit((string) $input->identity);
//     });

//     if ($validator->fails()) {
//         return response()->json([
//             'message' => $validator->errors()->first(),
//             'status'  => '400'
//         ], 400);
//     }

//     // Inputs
//     $identity     = $request->input('identity');
//     $password     = $request->input('password');
//     $country_code = $request->input('country_code');

//     // Determine login type
//     if (ctype_digit($identity)) {
//         // Mobile login
//         $user = DB::table('users')
//             ->where('mobile', $identity)
//             ->where('country_code', $country_code)
//             ->where('password', $password) // ⚠️ plain text match
//             ->first();
//     } else {
//         // Username login
//         $user = DB::table('users')
//             ->where('username', $identity)
//             ->where('password', $password)
//             ->first();
//     }

//     if ($user) {
//         // ✅ Step 1: Generate new token via Sanctum
//         $userModel   = User::find($user->id); 
//         $login_token = $userModel->createToken('UserApp')->plainTextToken;

//         // ✅ Step 2: Save token into users table
//         DB::table('users')->where('id', $user->id)->update([
//             'login_token' => $login_token,
//             'updated_at'  => now()
//         ]);

//         return response()->json([
//             'message'     => 'Login successful',
//             'status'      => '200',
//             'id'          => $user->id,
//             'login_token' => $login_token,
//             'mobile' => $user->mobile
//         ], 200);
//     } else {
//         return response()->json([
//             'message' => 'Invalid credentials',
//             'status'  => '401',
//         ], 401);
//     }
// }

    public function login(Request $request)
{
    $rules = [
        'identity'     => 'required|string',
        'password'     => 'required|string|min:6',
        'country_code' => 'nullable'
    ];

    $validator = Validator::make($request->all(), $rules);

    $validator->sometimes('country_code', 'required|string', function ($input) {
        return ctype_digit((string) $input->identity);
    });

    if ($validator->fails()) {
        return response()->json([
            'message' => $validator->errors()->first(),
            'status'  => '400'
        ], 400);
    }

    $identity     = $request->identity;
    $password     = $request->password;
    $country_code = $request->country_code;

    // ✅ STEP 1: Fetch user (without password condition)
    if (ctype_digit($identity)) {
        $user = DB::table('users')
            ->where('mobile', $identity)
            ->where('country_code', $country_code)
            ->first();
    } else {
        $user = DB::table('users')
            ->where('username', $identity)
            ->first();
    }

    // ❌ User not found
    if (!$user) {
        return response()->json([
            'message' => 'Invalid credentials',
            'status'  => '401'
        ], 401);
    }

    // ❌ User blocked by admin
    if ((int)$user->status === 0) {
        return response()->json([
            'message' => 'User blocked by admin. Contact admin.',
            'status'  => '403'
        ], 403);
    }

    // ❌ Password mismatch
    if ($user->password !== $password) {
        return response()->json([
            'message' => 'Invalid credentials',
            'status'  => '401'
        ], 401);
    }

    // ✅ STEP 2: Login success
    $userModel   = User::find($user->id);
    $login_token = $userModel->createToken('UserApp')->plainTextToken;

    DB::table('users')->where('id', $user->id)->update([
        'login_token' => $login_token,
        'updated_at'  => now()
    ]);

    return response()->json([
        'message'      => 'Login successful',
        'status'       => '200',
        'id'           => $user->id,
        'login_token'  => $login_token,
        'mobile'       => $user->mobile,
        'account_type' => $user->account_type
    ], 200);
}


    // public function login(Request $request)
    // {
    //     //dd($request);
    //     $rules = [
    //         'identity'    => 'required|string',
    //         'password'    => 'required|string|min:6',
    //         'country_code' => 'nullable'
    //     ];
    
    //     $validator = Validator::make($request->all(), $rules);
    
    //     $validator->sometimes('country_code', 'required|string', function ($input) {
    //         return ctype_digit((string) $input->identity);
    //     });
    
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'message' => $validator->errors()->first(),
    //             'status'  => '400'
    //         ], 400);
    //     }
    
    //     $identity     = $request->identity;
    //     $password     = $request->password;
    //     $country_code = $request->country_code;
    
    //     if (ctype_digit($identity)) {
    //         $user = DB::table('users')
    //             ->where('mobile', $identity)
    //             ->where('country_code', $country_code)
    //             ->where('password', $password)
    //             ->first();
    //     } else {
    //         $user = DB::table('users')
    //             ->where('username', $identity)
    //             ->where('password', $password)
    //             ->first();
    //     }
    
    //     if ($user) {
    
    //         $userModel   = User::find($user->id);
    //         $login_token = $userModel->createToken('UserApp')->plainTextToken;
    
    //         DB::table('users')->where('id', $user->id)->update([
    //             'login_token' => $login_token,
    //             'updated_at'  => now()
    //         ]);
    
    //         return response()->json([
    //             'message'      => 'Login successful',
    //             'status'       => '200',
    //             'id'           => $user->id,
    //             'login_token'  => $login_token,
    //             'mobile'       => $user->mobile,
    //             'account_type' => $user->account_type  // <-- DEMO FLAG
    //         ], 200);
    
    //     } else {
    
    //         return response()->json([
    //             'message' => 'Invalid credentials',
    //             'status'  => '401',
    //         ], 401);
    //     }
    // }
    
    

    // ✅ Mobile login
    private function getUserByCredentialsMobile($mobile, $password, $country_code) {
        return DB::table('users')
            ->where('mobile', $mobile)
            ->where('country_code', $country_code)
            ->where('password', $password) // plain match
            ->first();
    }
    
    // ✅ Username login
    private function getUserByCredentialsUsername($username, $password) {
        return DB::table('users')
            ->where('username', $username)
            ->where('password', $password) // plain match
            ->first();
    }
    
    // ✅ Update login_token
    private function updateLoginToken($user_id, $login_token) {
        DB::table('users')->where('id', $user_id)->update([
            'login_token' => $login_token
        ]);
    }

    private function generateSecureRandomString($length = 8)
    {
    	//$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Only uppercase letters
        $characters = '0123456789'; // You can expand this to include more characters if needed.
        $randomString = '';
    
        // Loop to generate the random string
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
        }
    
        return $randomString;
}

    private function getUserByCredentials($identity, $password) {
    $user = User::where(function ($query) use ($identity) {
                $query->where('email', $identity)
                      ->orWhere('mobile', $identity);
            })
            ->where('password', $password)
            ->where('status', 1)
            ->first();

    return $user;
}

    public function register(Request $request)
    {
        try {
            // Step 1: Validate Input
            $validator = Validator::make($request->all(), [
                //'email' => 'required|email|unique:users,email',
                'username' => 'required|string|unique:users,username',
                'country_code' => 'required',
                'mobile' => 'required|numeric|digits:10|unique:users,mobile',
                'password' => 'required|min:8',
                'password_confirmation' => 'required|min:8|same:password',
                // allow referral code if it exists either in users(referral_code) OR campaigns(unique_code)
                'referral_code' => [
                    'nullable',
                    'string',
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $existsInUsers = DB::table('users')->where('referral_code', $value)->exists();
                            $existsInCampaigns = DB::table('campaigns')->where('unique_code', $value)->exists();
                            if (! $existsInUsers && ! $existsInCampaigns) {
                                $fail('The selected referral code is invalid.');
                            }
                        }
                    },
                ],
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status'  => 400,
                    'message' => $validator->errors()->first()
                ], 200);
            }
    
            // Step 2: Generate Required Data
            $randomReferralCode = 'ZUP' . strtoupper(Str::random(6));
            $baseUrl = URL::to('/');
            $uid = $this->generateRandomUID();
    
            // Step 3: Prepare User Data
            $data = [
                'username'       => $request->username,
                'u_id'           => $uid,
                'mobile'         => $request->mobile,
                'password'       => $request->password, // ⚠ plain text (not secure)
                'userimage'      => $baseUrl . "/uploads/profileimage/1.png",
                ///www/wwwroot/root.winbhai.in/public/uploads/profileimage
                'status'         => 1,
                'referral_code'  => $randomReferralCode,
                'wallet'         => 0,
                'country_code'   => $request->country_code,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
    
            // Step 4: Add Referrer
            if ($request->filled('referral_code')) {
                $refCode = $request->referral_code;
    
                // 1) check users table first
                $referrerUser = DB::table('users')->where('referral_code', $refCode)->first();
                if ($referrerUser) {
                    $data['referral_user_id'] = $referrerUser->id;
                } else {
                    // 2) if not found in users, check campaigns table (unique_code)
                    $campaign = DB::table('campaigns')->where('unique_code', $refCode)->first();
                    if ($campaign) {
                        // set referral_user_id to the campaign's user_id
                        $data['referral_user_id'] = $campaign->user_id;
                    } else {
                        // fallback (shouldn't normally happen because of validator), keep default
                        $data['referral_user_id'] = 1;
                    }
                }
            } else {
                $data['referral_user_id'] = 1;
            }
    
            // Step 5: Insert User via DB Facade
            $userId = DB::table('users')->insertGetId($data);
    
            // Step 6: Retrieve User model instance to create token
            $user = User::find($userId);
            $token = $user->createToken('UserApp')->plainTextToken;
            
            // ✅ Step 7: Save same token in users table login_token column
            DB::table('users')->where('id', $userId)->update([
                'login_token' => $token
            ]);
    
            return response()->json([
                'status'  => 200,
                'message' => 'Registration successful',
                'data'    => [
                    'userId' => $userId,
                    'token'  => $token
                ]
            ], 200);
    
        } catch (\Throwable $e) {  // catch everything (Exception + Error)
            Log::error('Registration Error:', ['error' => $e->getMessage()]);
    
            return response()->json([
                'status'  => 500,
                'message' => 'Something went wrong, please try again later.',
                'error'   => $e->getMessage() // ⚠️ Production me ise hata dena better hai
            ], 500);
        }
    }

    // public function register(Request $request)
    // {
    //     try {
    //         // Step 1: Validate Input
    //         $validator = Validator::make($request->all(), [
    //             //'email' => 'required|email|unique:users,email',
    //             'username' => 'required|string|unique:users,username',
    //             'country_code' => 'required',
    //             'mobile' => 'required|numeric|digits:10|unique:users,mobile',
    //             'password' => 'required|min:8',
    //             'password_confirmation' => 'required|min:8|same:password',
    //             'referral_code' => 'nullable|string|exists:users,referral_code',
    //         ]);
    
    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status'  => 400,
    //                 'message' => $validator->errors()->first()
    //             ], 200);
    //         }
    
    //         // Step 2: Generate Required Data
    //         $randomReferralCode = 'ZUP' . strtoupper(Str::random(6));
    //         $baseUrl = URL::to('/');
    //         $uid = $this->generateRandomUID();
    
    //         // Step 3: Prepare User Data
    //         $data = [
    //             'username'       => $request->username,
    //             'u_id'           => $uid,
    //             'mobile'         => $request->mobile,
    //             'password'       => $request->password, // ⚠ plain text (not secure)
    //             'userimage'      => $baseUrl . "/image/download.png",
    //             'status'         => 1,
    //             'referral_code'  => $randomReferralCode,
    //             'wallet'         => 0,
    //             'country_code'   => $request->country_code,
    //             'created_at'     => now(),
    //             'updated_at'     => now(),
    //         ];
    
    //         // Step 4: Add Referrer
    //         if ($request->filled('referral_code')) {
    //             $referrer = DB::table('users')->where('referral_code', $request->referral_code)->first();
    //             $data['referral_user_id'] = $referrer ? $referrer->id : null;
    //         } else {
    //             $data['referral_user_id'] = 1;
    //         }
    
    //         // Step 5: Insert User via DB Facade
    //         $userId = DB::table('users')->insertGetId($data);
    
    //         // Step 6: Retrieve User model instance to create token
    //         $user = User::find($userId);
    //         $token = $user->createToken('UserApp')->plainTextToken;
            
    //         // ✅ Step 7: Save same token in users table login_token column
    //     DB::table('users')->where('id', $userId)->update([
    //         'login_token' => $token
    //     ]);

    
    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Registration successful',
    //             'data'    => [
    //                 'userId' => $userId,
    //                 'token'  => $token
    //             ]
    //         ], 200);
    
    //     } catch (\Throwable $e) {  // catch everything (Exception + Error)
    //         Log::error('Registration Error:', ['error' => $e->getMessage()]);
    
    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Something went wrong, please try again later.',
    //             'error'   => $e->getMessage() // ⚠️ Production me ise hata dena better hai
    //         ], 500);
    //     }
    // }

    public function Account_view(Request $request)
    {
       
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
    
    $userid=$request->user_id;
       $accountDetails = DB::select("SELECT * FROM `account_details` WHERE user_id=$userid
");



        if ($accountDetails) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $accountDetails
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
 
    public function add_account(Request $request)
    {
    // ✅ Ensure method is POST
    if (!$request->isMethod('post')) {
        return response()->json([
            'status' => 405,
            'message' => 'Unsupported request method'
        ], 405);
    }

    // ✅ Validate input
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'name' => 'required|string|max:255',
        'account_number' => 'required|string|max:50',
        'ifsc_code' => 'required|string|max:20',
        // Optional fields can be added if needed later
        // 'bank_name' => 'nullable|string|max:255',
        // 'branch' => 'nullable|string|max:255',
        // 'upi_id' => 'nullable|string|max:255',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    // ✅ Extract input data
    $user_id = $request->user_id;

    // ✅ Check if user already has account details
    // $existing = DB::table('account_details')->where('user_id', $user_id)->first();

    // if ($existing) {
    //     return response()->json([
    //         'status' => 400,
    //         'message' => 'Account already exists for this user.'
    //     ], 400);
    // }

    // ✅ Insert new account details
    $data = [
        'user_id' => $user_id,
        'name' => $request->name,
        'account_number' => $request->account_number,
        'ifsc_code' => $request->ifsc_code,
        'status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    $inserted = DB::table('account_details')->insert($data);

    if ($inserted) {
        return response()->json([
            'status' => 200,
            'message' => 'Account added successfully!'
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'Internal error while inserting account!'
        ], 400);
    }
}

    public function add_usdt_wallet_address(Request $request)
    {
    // ✅ Step 1: Validation
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer|exists:users,id',
        'wallet_address' => 'required|string|max:255',
        'wallet_type' => 'required|string|in:TRC20,ERC20,BEP20',
        'phone_no' => 'required|string|regex:/^[0-9]{10,15}$/',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    // ✅ Step 2: Prepare data
    $data = [
        'user_id' => $request->user_id,
        'wallet_address' => $request->wallet_address,
        'wallet_type' => $request->wallet_type,
        'phone_no' => $request->phone_no,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    // ✅ Step 3: Check if address already exists for user
    $exists = DB::table('usdt_wallet_address')
        ->where('user_id', $request->user_id)
        ->where('wallet_address', $request->wallet_address)
        ->exists();

    // if ($exists) {
    //     return response()->json([
    //         'status' => 409,
    //         'message' => 'Wallet address already exists for this user.'
    //     ], 409);
    // }

    // ✅ Step 4: Insert data
    $insert = DB::table('usdt_wallet_address')->insert($data);

    if ($insert) {
        return response()->json([
            'status' => 200,
            'message' => 'USDT wallet address added successfully.'
        ]);
    } else {
        return response()->json([
            'status' => 500,
            'message' => 'Failed to add wallet address. Please try again.'
        ], 500);
    }
}

    public function view_usdt_wallet_address(Request $request)
    {
        // ✅ Step 1: Validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
    
        // ✅ Step 2: Fetch data
        $wallets = DB::table('usdt_wallet_address')
            ->where('user_id', $request->user_id)
            ->select('id', 'user_id', 'wallet_address', 'wallet_type', 'phone_no', 'created_at', 'updated_at')
            ->get();
    
        // ✅ Step 3: Check if user has wallets
        if ($wallets->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No wallet address found for this user.'
            ], 404);
        }
    
        // ✅ Step 4: Success response
        return response()->json([
            'status' => 200,
            'message' => 'Wallet addresses fetched successfully.',
            'data' => $wallets
        ]);
    }

    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'account_id' => 'required',
            'type' => 'required',
            'amount' => 'required|numeric'
        ]);
        $validator->stopOnFirstFailure(); 
        if($validator->fails()){
             $response = [
                            'status' => 400,
                          'message' => $validator->errors()->first() 
                          ]; 
    		
    		return response()->json($response,400);
    		
        }
    
        $userid = $request->input('user_id');
        $accountid = $request->input('account_id');
        $amount = $request->input('amount');
        $type = $request->input('type');
       
       $user_details = DB::table('account_details')->where('user_id', $userid)->first();

            $account_id = $user_details->id ?? null;
            
            if (empty($account_id)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'account_id is required'
                ], 400);
            }

     /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
         // Check if there's a pending withdrawal
        $lastWithdrawal = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->orderBy('created_at', 'desc')
            ->first();
    
        if ($lastWithdrawal && $lastWithdrawal->status == 1) { // Assuming 1 is for pending
            return response()->json([
                'status' => 400,
                'message' => 'You cannot withdraw again until your previous request is approved or rejected.'
            ], 400);
        }
    
        // Limit to three withdrawals per day
        $withdrawCount = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->whereDate('created_at', now())
            ->where('status', 2) // Assuming 2 is for successful withdrawal
            ->count();
    
        if ($withdrawCount >= 5) {
            $response = [
                'status' => 400,
                'message' => 'You can only withdraw 5 times in a day.'
            ];
            return response()->json($response, 400);
        }
    
        
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        
         $date = date('YmdHis');
         
            $rand = rand(11111, 99999);
            $orderid = $date . $rand;
        if ($amount >= 200 && $amount<=25000) {
          
            $wallet=DB::select("SELECT `wallet`,`recharge`,`first_recharge`,`winning_wallet` FROM `users` WHERE id=$userid");
          $user_wallet=$wallet[0]->wallet;
          $user_recharge=$wallet[0]->recharge;
          //dd($user_recharge);
          $first_recharge=$wallet[0]->first_recharge;
          if($user_recharge == 0){
              if($first_recharge == 1){
            if($user_wallet >= $amount){
          $data= DB::table('withdraw_histories')->insert([
        'user_id' => $userid,
        'amount' => $amount,
        'account_id' => $accountid,
        'type' => $type,
       // 'upi_id' =>$upi_id,
        'order_id' => $orderid,
        'status' => 1,
    	'typeimage'=>"https://root.winbhai.in/uploads/fastpay_image.png",
        'created_at' => now(),
        'updated_at' => now(),
    ]);
          DB::select("UPDATE `users` SET `wallet`=`wallet`-$amount,`winning_wallet`=`winning_wallet`-$amount WHERE id=$userid;");
     if ($data) {
                 $response = [
            'status' =>200,
            'message' => 'Withdraw Request Successfully ..!',
        ];
    
        return response()->json($response,200);
    
            } else {
                 $response = [
            'status' =>400,
            'message' => 'Internal error..!',
        ];
    
        return response()->json($response,400);
                
            }
            }else{
          $response = [
            'status' =>400,
            'message' => 'insufficient Balance..!',
        ];
    
        return response()->json($response,400);
     }  
              }else{
          $response = [
            'status' =>400,
            'message' => 'first rechage is mandatory..!',
        ];
    
        return response()->json($response,400);
     }     
          }else {
             $response = [
            'status' =>400,
            'message' => 'need to bet amount 0 to be able to Withdraw',
        ];
    
        return response()->json($response,400);   
          }
            
        }else{
            $response['message'] = "minimum Withdraw 200 And Maximum Withdraw 25000";
                $response['status'] = "400";
                return response()->json($response,200);
        }
      
    }

    public function claim_list(Request $request)
    {
       
         $validator = Validator::make($request->all(), [
            'userid' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
    
    $userid=$request->userid;
       $accountDetails = DB::select("SELECT * FROM `gift_claim` WHERE `userid`=$userid order by id DESC");



        if ($accountDetails) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $accountDetails
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
 
    public function coupon_show()
    {
        try {
            // Coupons table ka data fetch karo
            $coupons = DB::table('coupons')
                ->select('id', 'title', 'coupon_code', 'percentage', 'description','use_limit_per_user', 'created_at', 'updated_at')
                ->get();
    
            // Agar koi data nahi mila
            if ($coupons->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No coupons found',
                    'data' => []
                ], 404);
            }
    
            // Success response
            return response()->json([
                'status' => true,
                'message' => 'Coupons fetched successfully',
                'data' => $coupons
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function giftCartApply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required',
            'code' => 'required',
        ]);
    
         $validator->stopOnFirstFailure();
    	
        if($validator->fails()){
             $response = [
                            'status' => 400,
                           'message' => $validator->errors()->first()
                          ]; 
    		
    		return response()->json($response,400);
    		
        }
    
      
            $userid = $request->input('userid');
            $code = $request->input('code');
    
            $data = DB::table('gift_cart')->where('code', $code)->where('status', 1)->first();
    
            if ($data) {
                $fixPeople = $data->number_people;
                $availedPeople = $data->availed_num;
    
                if ($availedPeople < $fixPeople) {
                    $claimUser = DB::table('gift_claim')->where('gift_code', $code)->where('userid', $userid)->first();
    
                    if (!$claimUser) {
                        date_default_timezone_set('Asia/Kolkata');
                        $datetime = date('Y-m-d H:i:s');
    
                        $giftCartAmount = $data->amount;
    
                        if (!empty($giftCartAmount)) {
                            DB::table('gift_claim')->insert(['userid' => $userid, 'gift_code' => $code, 'amount' => $giftCartAmount]);
                            DB::table('users')->where('id', $userid)->update(['third_party_wallet' => DB::raw('third_party_wallet + ' . $giftCartAmount), 'bonus' => DB::raw('bonus + ' . $giftCartAmount)]);
                            DB::table('gift_cart')->where('id', $data->id)->update(['availed_num' => DB::raw('availed_num + 1')]);
    
                            $data = [
                                'userid' => $userid,
                                'amount' => $giftCartAmount,
                                'subtypeid' => 20,
                                'created_at' => $datetime,
                                'updated_at' => $datetime
                            ];
                            DB::table('wallet_history')->insert($data);
    
                            $response['message'] = " Add $giftCartAmount Rs. Successfully";
                            $response['status'] = "200";
                            return response()->json($response,200);
                        } else {
                            $response['message'] = "No record found";
                            $response['status'] = "400";
                            return response()->json($response,400);
                        }
                    } else {
                        $response['message'] = "You have already availed this offer!";
                        $response['status'] = "400";
                        return response()->json($response,400);
                    }
                } else {
                    $response['message'] = "No longer available this offer.";
                    $response['status'] = "400";
                    return response()->json($response,400);
                }
            } else {
                $response['message'] = "Invalid Gift Code!";
                $response['status'] = "400";
                return response()->json($response,400);
            }
        
    }
    
    
   public function bonusInfo(Request $request)
{
    $uid = $request->user_id;
    $couponCode = $request->coupon_code;

    // ===============================
    // BASIC VALIDATION
    // ===============================
    if (empty($uid)) {
        return response()->json([
            'status' => 400,
            'message' => 'user_id required'
        ]);
    }

    $couponData = null;

    // ===============================
    // ✅ COUPON CHECK (FIRST)
    // ===============================
    if (!empty($couponCode)) {

        // 1️⃣ Coupon exists
        $coupon = DB::table('gift_cart')
            ->where('code', $couponCode)
            ->first();

        if (!$coupon) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid coupon code'
            ]);
        }

        // 2️⃣ Coupon expiry check
        if (strtotime($coupon->expire_date) < strtotime(date('Y-m-d'))) {
            return response()->json([
                'status' => 400,
                'message' => 'Coupon expired'
            ]);
        }

        // 3️⃣ Same user already used?
        $sameUserUsed = DB::table('gift_claim')
            ->where('gift_code', $couponCode)
            ->where('userid', $uid)
            ->exists();

        if ($sameUserUsed) {
            return response()->json([
                'status' => 400,
                'message' => 'You have already used this coupon'
            ]);
        }

        // ✅ Coupon valid & allowed
        $couponData = [
            'coupon_code'  => $coupon->code,
            'expire_date'  => $coupon->expire_date,
            'bonus_amount' => (float) $coupon->amount
        ];
    }

    // ===============================
    // ✅ LAST 7 DAYS BETTING LOSS
    // ===============================
    $fromDate = now()->subDays(7);

    $betsLoss = DB::table('bets')
        ->where('userid', $uid)
        ->where('status', 2) // LOSS
        ->where('created_at', '>=', $fromDate)
        ->sum('amount');

    $aviatorLoss = DB::table('aviator_bet')
        ->where('uid', $uid)
        ->where('status', 2)
        ->where('created_at', '>=', $fromDate)
        ->sum('amount');

    $chickenLoss = DB::table('chicken_bets')
        ->where('user_id', $uid)
        ->where('status', 2)
        ->where('created_at', '>=', $fromDate)
        ->sum('amount');

    $totalLoss = ($betsLoss ?? 0)
               + ($aviatorLoss ?? 0)
               + ($chickenLoss ?? 0);

    // ===============================
    // ✅ FINAL RESPONSE
    // ===============================
    return response()->json([
        'status' => 200,
        'message' => 'Bonus data fetched successfully',
        'data' => [
            'last_7_days_loss' => (float) $totalLoss,
            'coupon' => $couponData
        ]
    ]);
}



    public function feedback(Request $request)
    {
     
            $validator = Validator::make($request->all(), [
                'userid' => 'required',
                'description' => 'required',
            ]);
    
            $validator->stopOnFirstFailure();
    
            if ($validator->fails()) {
                $response = [
                    'status' => 400,
                    'message' => $validator->errors()->first()
                ];
    
                return response()->json($response,400);
            }
    
            $data = array(
                'userid' => $request->input('userid'),
                'description' => $request->input('description'),
                'status' => 1,
                'created_at' => now(),
                 'updated_at' => now(),
            );
    
            $data1 = DB::table('feedbacks')->insert($data);
    
            if ($data1) {
                $response = [
                    'message' => 'Successfully',
                    'status' => 200,
                    'data' => $data1
                ];
    
                return response()->json($response,200);
            } else {
                return response()->json(['message' => 'Failed','status' => 400], 400);
            }
    }

    public function pay_modes(Request $request)
    {
        if ($request->isMethod('get')) {
            $userid = $request->input('userid');
    		$type = $request->input('type');
    		if($type == ''){
            $check = DB::table('users')->where('first_recharge', '1')->where('id', $userid)->first();
    
            $pay_modes = DB::table('pay_modes')->where('status', '1')->get();
    
            if ($pay_modes->isNotEmpty()) {
                $response['msg'] = "Successfully";
                $response['data'] = $pay_modes->toArray();
    
                if ($check && $check->first_recharge == '1') {
                    $response['minimum'] = 500;
                    $response['status'] = "200";
                } else {
                    $response['minimum'] = 100;
                    $response['status'] = "400";
                }
    
                return response()->json($response);
            } else {
                // If no data is found, set an appropriate response
                $response['msg'] = "No record found";
                $response['status'] = "400";
                return response()->json($response);
            }
    	 } else {
            $check = DB::table('users')->where('first_recharge', '1')->where('id', $userid)->first();
    
            $pay_modes = DB::table('pay_modes')->where('status', '1')->where('type', $type)->get();
    
            if ($pay_modes->isNotEmpty()) {
                $response['msg'] = "Successfully";
                $response['data'] = $pay_modes->toArray();
    
                if ($check && $check->first_recharge == '1') {
                    $response['minimum'] = 500;
                    $response['status'] = "200";
                } else {
                    $response['minimum'] = 100;
                    $response['status'] = "400";
                }
    
                return response()->json($response);
            } else {
                // If no data is found, set an appropriate response
                $response['msg'] = "No record found";
                $response['status'] = "400";
                return response()->json($response);
            }
        }
        } else {
            return response()->json(['error' => 'Unsupported request method'], 400);
        }
    }
	
    public function transaction_history_list()
    {
      $subtype=DB::select("SELECT `id`,`name` FROM `subtype` WHERE 1");

        if ($subtype) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $subtype
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function transaction_history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
        }
        
        $userid = $request->userid;
        $subtype = $request->subtypeid;
         //$offset = $request->offset ?? 0;
        $from_date = $request->created_at;
        //$to_date = $request->created_at;
        //$status = $request->status;
    
    // $status=DB::SELECT("SELECT `status` FROM `users` WHERE id=$userid"); 
    // //dd($status);
    // 	$ddd=$status[0]->status;
    // 	//dd($ddd);
    // if($ddd == 1){hea
        $where = [];
    
        if (!empty($userid)) {
            $where[] = "wallet_history.`userid` = '$userid'";
        }
    
        if (!empty($from_date)) {
    		$newDateString = date("Y-m-d", strtotime($from_date));
    		
            $where[] = "DATE(`wallet_history`.`created_at`) = '$newDateString'";
    		
        }
        if (!empty($subtype)) {
            $where[] = "`wallet_history`.`subtypeid` = '$subtype'";
        }
        //
        //
        
        $query = "
           SELECT subtype.name as type , wallet_history.amount as amount, wallet_history.created_at as datetime FROM `wallet_history` LEFT JOIN `subtype` on wallet_history.subtypeid = subtype.id
        ";
    
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
    
        $query .= " ORDER BY wallet_history.id DESC";
    
        $results = DB::select($query);
        //dd($results);
    if(!empty($results)){
        return response()->json([
            'status' => 200,
            'message' => 'Data found',
            'data' => $results
        ]);
    }else{
         return response()->json(['message' => 'No record found','status' => 400,
                    'data' => []], 400);
    }
    // }else{
        
    //  $response['message'] = "User block by admin..!";
    //                 $response['status'] = "401";
    //                 return response()->json($response,401);
        
    // }
        
    }

    public function image_all()
    {
        $user = DB::select("SELECT `image` FROM `all_images`");
          if($user){
          $response =[ 'success'=>"200",'data'=>$user,'message'=>'Successfully'];return response ()->json ($response,200);
      }
      else{
       $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
      } 
    }
    
    public function forget_username(Request $request)
    {
		
		   $validator = Validator::make($request->all(), [
          'mobile' => ['required', 'string', 'regex:/^\d{10}$/','exists:users,mobile'], // Ensure 10 digits
	        'new_username' => 'required|string|unique:users,username',
    ]);

	    $validator->stopOnFirstFailure();
	   
    if($validator->fails()){
		
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }
	   
			
	  $user = DB::table('users')->where('username',$request->new_username)
		  ->update([
		   'username'=>$request->new_username
		  ]);
	  
	   return response()->json([
	      'status'=>200,
		  'message'=>'Username update successfully.',
	   ]);
	   
	   
		
	}

    public function forget_pass(Request $request)
    {
		
		   $validator = Validator::make($request->all(), [
          'mobile' => ['required', 'string', 'regex:/^\d{10}$/','exists:users,mobile'], // Ensure 10 digits
	      'password' => 'required|string|min:8'
    ]);

	    $validator->stopOnFirstFailure();
	   
    if($validator->fails()){
		
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }
	   
			
	  $user = DB::table('users')->where('mobile',$request->mobile)
		  ->update([
		   'password'=>$request->password
		  ]);
	  
	   return response()->json([
	      'status'=>200,
		  'message'=>'Password reset successfully.',
	   ]);
	   
	   
		
	}

	public function profile(Request $request)
    {
    $uid = $request->id;

    if (empty($uid)) {
        return response()->json([
            'status' => 400,
            'message' => 'UID Required'
        ]);
    }

    // Fetch user data with admin settings using leftJoin
    $data = DB::table('users as u')
        ->select(
            'u.*',
            'a1.longtext as minimum_withdraw',
            'a2.longtext as maximum_withdraw'
        )
        ->leftJoin('admin_settings as a1', function ($join) {
            $join->on('a1.id', '=', DB::raw('15'));
        })
        ->leftJoin('admin_settings as a2', function ($join) {
            $join->on('a2.id', '=', DB::raw('16'));
        })
        ->where('u.id', $uid)
        ->first();

    if (!$data) {
        return response()->json([
            'status' => 400,
            'message' => 'No data found'
        ]);
    }

    // Fetch payment limit (id = 14)
    $paymentLimit = DB::table('payment_limits')->where('id', 14)->value('amount');

    // Check if user is active
    if ($data->status != 1) {
        return response()->json([
            'status' => 401,
            'message' => 'User blocked by admin'
        ]);
    }

    // Wallet calculations
    $main_wallet = $data->wallet ?? 0;
    $thirdparty_wallet = $data->third_party_wallet ?? 0;
    $total_wallet = $main_wallet + $thirdparty_wallet;


// =========================================================
    // ⭐ COMMISSION WITHDRAW CALCULATION (ADDED HERE)
    // =========================================================

    // Step 1: Get all campaigns of user
    $campaigns = DB::table('campaigns')->where('user_id', $uid)->get();
    $availableToWithdraw = 0;
    $totalCommission = 0;
    $withdrawnCommission = 0;

    if (!$campaigns->isEmpty()) {

        // Get unique referral codes
        $uniqueCodes = $campaigns->pluck('unique_code')->toArray();

        // Get users registered via those codes
        $registeredUsers = DB::table('users')
            ->whereIn('referral_code', $uniqueCodes)
            ->get();

        $userIds = $registeredUsers->pluck('id')->toArray();

        if (!empty($userIds)) {
            // Sum commission from referred users
            $totalCommission = DB::table('users')
                ->whereIn('id', $userIds)
                ->sum('commission');
        }

        // Withdrawn commission
        $withdrawnCommission = DB::table('withdraw_histories')
            ->where('user_id', $uid)
            ->where('status', 1)
            ->sum('amount');

        // Available balance (never negative)
        $availableToWithdraw = max(0, $totalCommission - $withdrawnCommission);
    }
    
    // bets table
        $betsExposure = DB::table('bets')
            ->where('userid', $uid)
            ->where('status', 0) // ✅ agar active bet ke liye status column hai
            ->sum('amount');
        
        // aviator_bet table
        $aviatorExposure = DB::table('aviator_bet')
            ->where('uid', $uid)
            ->where('status', 0)
            ->sum('amount');
        
        // chicken_bets table
        $chickenExposure = DB::table('chicken_bets')
            ->where('user_id', $uid)
            ->where('status', 0)
            ->sum('amount');
        
        // Total net exposure
        $netExposure = ($betsExposure ?? 0) 
                     + ($aviatorExposure ?? 0) 
                     + ($chickenExposure ?? 0);
            
            



    // =========================================================
    // END OF COMMISSION CALCULATION
    // =========================================================
    
    // Fetch WhatsApp deposit number (id = 17)
    $whatsappDepositNumber = DB::table('admin_settings')
        ->where('id', 17)
        ->value('longtext');
        
    $chat_on_whatsapp = DB::table('admin_settings')
        ->where('id', 24)
        ->value('longtext');

    // Prepare response
    $responseData = [
        'id' => $data->id,
        'mobile' => $data->mobile,
        'email' => $data->email,
        'username' => $data->username,
        'userimage' => $data->userimage,
        'recharge' => $data->recharge,
        'u_id' => $data->u_id,
        'login_token' => $data->login_token,
        'referral_code' => $data->referral_code,
        'wallet' => $main_wallet,
        //'third_party_wallet' => $thirdparty_wallet, //freecash amount 
        'free_cash' => $thirdparty_wallet, //freecash amount 
        'net_exposure' => (float) $netExposure,
       // 'net_exposure' => $thirdparty_wallet, //net exposure amount means current bet placed amunt  
        //'net_exposure' => 1000, //net exposure amount means current bet placed amunt  
        'total_wallet' => $total_wallet,
        'winning_amount' => $data->winning_wallet,
        'minimum_deposit' => $data->minimum_withdraw,
        'minimum_withdraw' => $data->minimum_withdraw,
        'maximum_deposit' => $data->maximum_withdraw,
        'maximum_withdraw' => $data->maximum_withdraw,
        'crypto_min_deposit' => 10,
        'crypto_mxn_deposit' => 1000,
        'crypto_min_withdraw' => 10,
        'crypto_max_withdraw' => 500,
        'last_login_time' => now()->format('Y-m-d H:i:s'),
        'apk_link' => "https://root.winbhai.in/winbhai.apk",
        'referral_code_url' => "https://winbhai.in/signup?campaign={$data->referral_code}",
        'aviator_link' => "https://foundercodetech.com",
        'aviator_event_name' => "gbaviator",
        'wingo_socket_url' => "https://aviatorudaan.com/",
        'wingo_socket_event_name' => "globalbet",
        'status' => "1",
        'type' => "0",
        'withdraw_conversion_rate' => $paymentLimit,
        'available_commission_to_withdraw' => (float)$availableToWithdraw,
        // 'available_commission_to_withdraw' => 1000,
        'whatsapp_deposit_number' => $whatsappDepositNumber,
        'chat_on_whatsapp' => $chat_on_whatsapp,
    ];

    return response()->json([
        'success' => 200,
        'message' => 'Data found',
        'data' => $responseData
    ]);
}

    // public function profile(Request $request) 
    // {
    //     $ldate = new DateTime('now');
      
    //     $uid = $request->id;
    
    //     if (empty($uid)) {
    //         return response()->json([
    //             'status' => 400,
    //             'message' => 'UID Required'
    //         ]);
    //     }
        
        
    
    //     // Fetch user data with the necessary join and data
    //     $data = DB::table('users as u')
    //         ->select('u.*', 'a1.longtext as minimum_withdraw', 'a2.longtext as maximum_withdraw')
    //         ->leftJoin('admin_settings as a1', function ($join) {
    //             $join->on('a1.id', '=', DB::raw(15));
    //         })
    //         ->leftJoin('admin_settings as a2', function ($join) {
    //             $join->on('a2.id', '=', DB::raw(16));
    //         })
    //         ->where('u.id', $uid)
    //         ->limit(1)
    //         ->first();
    
    //     if ($data === null) {
    //         return response()->json([
    //             'status' => 400,
    //             'message' => 'No data found'
    //         ]);
    //     }
        
    //       // ✅ Fetch payment limit (id = 14)
    //     $paymentLimit = DB::table('payment_limits')->where('id', 14)->first();
        
    
    //     // If the user is not blocked (status is 1)
    //     $status = $data->status;
    //     if ($status == 1) {
    
    //         // Process the wallets
    //         $thirdpartywallet = isset($data->third_party_wallet) ? $data->third_party_wallet : 0;
    //         $main_wallet = isset($data->wallet) ? $data->wallet : 0;
    //         $total_wallet = $thirdpartywallet + $main_wallet;
    
    //         // Create the response data array
    //         $responseData = [
    //             'id' => $data->id,
    //             'mobile' => $data->mobile,
    //             'email' => $data->email,
    //             'username' => $data->username,
    //             'userimage' => $data->userimage,
    //             'recharge' => $data->recharge,
    //             'u_id' => $data->u_id,
    //             'login_token' => $data->login_token,
    //             'referral_code' => $data->referral_code,
    //             'wallet' => $main_wallet,
    //             'third_party_wallet' => $thirdpartywallet,
    //             'total_wallet' => $total_wallet,
    //             'winning_amount' => $data->winning_wallet,
    //             'minimum_withdraw' => $data->minimum_withdraw,
    //             'maximum_withdraw' => $data->maximum_withdraw,
    //             'last_login_time' => $ldate->format('Y-m-d H:i:s'),
    //             'apk_link' => "https://root.winbhai.in/winbhai.apk",
    // 		    'referral_code_url' => "https://winbhai.in/signup?campaign=" . $data->referral_code,
    //             'aviator_link' => "https://foundercodetech.com",
    //             'aviator_event_name' => "gbaviator",
    //             'wingo_socket_url' => "https://aviatorudaan.com/",
    //             'wingo_socket_event_name' => "globalbet",
    //             'status' => "1",
    //             'type' => "0",
    //              'withdraw_conversion_rate' => $paymentLimit ? $paymentLimit->amount : null,
    //         ];
    
    //         return response()->json([
    //             'success' => 200,
    //             'message' => 'Data found',
    //             'data' => $responseData
    //         ]);
    //     } else {
    //         // If the user is blocked
    //         return response()->json([
    //             'status' => 401,
    //             'message' => 'User blocked by admin'
    //         ]);
    //     }
    // }

    public function Status_list()
    {
      
      $status= array(
           array(
        'id' => '0',
        'name' => 'All'
    ),
    array(
        'id' => '1',
        'name' => 'Processing'
    ),
    array(
        'id' => '2',
        'name' => 'Completed'
    ),
    array(
        'id' => '3',
        'name' => 'Reject'
    )
);
      
        //  $status = DB::select("SELECT `id`,`name` FROM `status` WHERE 1");
          if($status){
          $response =[ 'success'=>"200",'data'=>$status,'message'=>'Successfully'];return response ()->json ($response,200);
      }
      else{
       $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
      } 
    }

    public function deposit_history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            // 'type' is now optional
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
    
        // Extract parameters
        $user_id = $request->user_id;
        $status = $request->status;
        $type = $request->type;
        $date = $request->created_at;
    
        // Start building the query
        $query = DB::table('payins')
                    ->select('cash', 'type', 'status', 'order_id', 'typeimage', 'created_at')
                    ->orderByDesc('payins.id');
    
        // Apply filters based on parameters provided
        if (!empty($user_id)) {
            $query->where('payins.user_id', '=', $user_id);
        }
    
        if (!empty($status)) {
            $query->where('payins.status', '=', $status);
        }
    
        // Apply the 'type' filter only if it's provided
        if (isset($type)) {
            // If 'type' is provided, apply the filter
            if (is_numeric($type)) {
                $query->where('payins.type', '=', (int)$type);
            } else {
                // You can handle this in case 'type' is a string
                $query->where('payins.type', '=', $type);
            }
        }
    
        if (!empty($date)) {
            $newDateString = date("Y-m-d", strtotime($date));
            $query->whereDate('payins.created_at', '=', $newDateString);
        }
    
        // Execute the query
        $payin = $query->get();
    
        if ($payin->isNotEmpty()) {
            return response()->json([
                'message' => 'Successfully',
                'status' => 200,
                'data' => $payin
            ], 200);
        } else {
            return response()->json([
                'message' => 'No record found',
                'status' => 400,
                'data' => []
            ], 400);
        }
    }

    public function withdraw_history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'type' => 'nullable',
            'created_at' => 'nullable|date', // Ensure date is valid
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
    
        $user_id = $request->user_id;
        $status = $request->status;
        $type = $request->type;
        $created_at = $request->created_at;
        $where = [];
    
        // User ID condition
        if (!empty($user_id)) {
            $where[] = "withdraw_histories.`user_id` = '$user_id'";
        }
    
        // Status condition
        if (!empty($status)) {
            $where[] = "`withdraw_histories`.`status` = '$status'";
        }
    
        // Type condition including type = 0
        if ($type !== null && $type !== '') {
            $where[] = "`withdraw_histories`.`type` = '$type'";
        }
    
        // Date filter condition
        if (!empty($created_at)) {
            $newDateString = date("Y-m-d", strtotime($created_at));
            $where[] = "DATE(`withdraw_histories`.`created_at`) = '$newDateString'";
        }
    
        $query = "SELECT `id`, `user_id`, `amount`, `type`, `status`, `typeimage`, `order_id`, `created_at` FROM withdraw_histories";
    
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
    
        $query .= " ORDER BY withdraw_histories.id DESC";
    
        $payin = DB::select($query);
    
        if ($payin) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $payin
            ];
            return response()->json($response, 200);
        } else {
            return response()->json(['message' => 'No record found', 'status' => 200, 'data' => []], 400);
        }
    }

    public function notification()
    {
       

       $notification = DB::select("SELECT `name`,`disc` FROM `notifications` WHERE `status`=1
    ");



        if ($notification) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $notification
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }

    public function Privacy_Policy()
    {
      
       $privacyPolicy = Setting::where('id', 1)
          ->where('status', 1)
          ->select('name', 'description')
          ->first();



        if ($privacyPolicy) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $privacyPolicy
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
	
    public function about_us(Request $request)
    {
		  $validator = Validator::make($request->all(), [
        'type' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $type = $request->type;
 
		  
		  
        $about_us = DB::select("SELECT `name`,`description` FROM `settings` WHERE `type`=$type;
");

        if ($about_us) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $about_us
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found', 'status' => 400,
                'data' => []], 400);
        }
    }
	
	public function customer_service()
    {
    // सारे columns लाने के लिए * use करो
    $customer_service = DB::select("SELECT * FROM `customer_services` WHERE `status` = 1");

    if ($customer_service && count($customer_service) > 0) {
        // पहले 2 records
        $firstSection = array_slice($customer_service, 0, 2);

        // बाकी records (3rd और 4th)
        $secondSection = array_slice($customer_service, 2, 2);

        $response = [
            'message' => 'Successfully',
            'status'  => 200,
            'chat_section'  => $firstSection,
            'support_section' => $secondSection,
        ];

        return response()->json($response);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status'  => 400,
            'chat_section'  => [],
            'support_section' => []
        ], 400);
    }
}

    public function contactInfo(Request $request)
    {
        $type = $request->type;
    
        if (empty($type)) {
            return response()->json([
                'status' => 400,
                'message' => 'type is required'
            ]);
        }
    
        // ===============================
        // CONTACT US → MOBILE NUMBER
        // ===============================
        if ($type === 'contact') {
    
            $data = DB::table('contact_us')
                ->select('contact')
                ->first();
    
            if (!$data) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Contact details not found'
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'type' => 'contact',
                'data' => [
                    'mobile_number' => $data->contact
                ]
            ]);
        }
    
        // ===============================
        // CONTACT WITH US → SOCIAL LINKS
        // ===============================
        if ($type === 'social') {
    
            $data = DB::table('contact_with_us')
                ->select('whatsapp_link', 'telegram_link', 'instagram_link')
                ->first();
    
            if (!$data) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Social links not found'
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'type' => 'social',
                'data' => [
                    'whatsapp_link' => $data->whatsapp_link,
                    'telegram_link' => $data->telegram_link,
                    'instagram_link' => $data->instagram_link
                ]
            ]);
        }
    
        // ===============================
        // INVALID TYPE
        // ===============================
        return response()->json([
            'status' => 400,
            'message' => 'Invalid type'
        ]);
    }

	
    public function contact_us()
    {
        $contact = Setting::where('id', 4)
             ->where('status', 1)
             ->select('name', 'description', 'link')
             ->first();


        if ($contact) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $contact
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
    	
    public function support()
    {
            $support = Setting::where('id', 5)
                      ->where('status', 1)
                      ->select('name', 'link')
                      ->first();
    
    
            if ($support) {
                $response = [
                    'message' => 'Successfully',
                    'status' => 200,
                    'data' => $support
                ];
    
                return response()->json($response);
            } else {
                return response()->json(['message' => 'No record found','status' => 400,
                    'data' => []], 400);
            }
        }
    	
    public function update_profile(Request $request)
    {
         $validator = Validator::make($request->all(), [
        'id' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }
        
        $id = $request->id;
        
        $value = User::findOrFail($id);
        $status=$value->status;
        
        	if($status == 1)
        {
        if (!empty($request->username)) {
            $value->username = $request->username;
        }
        
        if (!empty($request->userimage) && $request->userimage != "null") {
            $value->userimage = $request->userimage;
        }
    
        // Save the changes
        $value->save();
    
        $response = [
            'status' => 200,
            'message' => "Successfully updated"
        ];
    
        return response()->json($response, 200);
        }else{
             $response['message'] = "User block by admin..!";
                    $response['status'] = "401";
                    return response()->json($response,401);
        }
    }
    
    public function main_wallet_transfer(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);
        
        $id = $request->id;
        
        $user = User::findOrFail($id);
        $status = $user->status;
        $main_wallet = $user->wallet;
        $thirdpartywallet = $user->third_party_wallet;
        $add_main_wallet = $main_wallet + $thirdpartywallet;
        
        if ($status == 1) {
            $user->wallet = $add_main_wallet;
            $user->third_party_wallet = 0;
            $user->save();
    
            $response = [
                'status' => 200,
                'message' => "Wallet transfer Successfully ....!"
            ];
    
            return response()->json($response, 200);
        } else {
            $response = [
                'status' => 401,
                'message' => "User blocked by admin..!"
            ];
            return response()->json($response, 401);
        }
    }
    
    public function total_bet_details(Request $request)
    {
    // Validate incoming data
    $validator = Validator::make($request->all(), [
        'userid' => 'required|exists:users,id',
        'type' => 'required|in:1,2,3,4'
		
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $userid = $request->userid;
    $type = $request->type;
	
	

    // Prepare the SQL query based on the 'type' parameter
    switch ($type) {
        case 1:
            // For today
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) = CURDATE()", [$userid]);
            break;

        case 2:
            // For yesterday
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) = CURDATE() - INTERVAL 1 DAY", [$userid]);
            break;

        case 3:
            // For the past week
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) >= CURDATE() - INTERVAL 1 WEEK", [$userid]);
            break;

        case 4:
            // For the past month
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) >= CURDATE() - INTERVAL 1 MONTH", [$userid]);
            break;

        default:
            return response()->json([
                'status' => 400,
                'message' => 'Invalid type provided'
            ], 200);
    }
	
	
	$grand_total=$betDetails[0]->total_bet_amount;

    // If no bets found, send response with 0 values
    if (empty($betDetails)) {
        return response()->json([
            'status' => 200,
            'message' => 'No bets found',
            'lottery_data' => [
                'total_bet_amount' => 0,
                'total_bet_count' => 0,
                'total_win_amount' => 0
            ]
        ], 200);
    }

    // Return the bet details
    return response()->json([
        'status' => 200,
        'message' => 'Bet details fetched successfully',
		'grand_total' => $grand_total,
        'lottery_data' => $betDetails[0] // Assuming only one record is returned
		
    ], 200);
}
    
    public function changePassword(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'userid' => 'required',
                'old_password' => 'required',
                'new_password' => 'required|min:8',
                'confirm_password' => 'required|same:new_password',
            ]);
    
            $validator->stopOnFirstFailure();
    	   
        if($validator->fails()){
    		
            return response()->json([
                'status' => 400,
                'msg' => $validator->errors()->first()
            ],400);
        }
            $userid = $request->input('userid');
            $oldPassword = $request->input('old_password');
            $newPassword = $request->input('new_password');
    
            $user = User::find($userid);
    
            if (!$user) {
                return response()->json([
                    'msg' => 'User not found',
                    'status' => 404
                ], 404);
            }
    
            if ($oldPassword != $user->password) {
                return response()->json([
                    'msg' => 'Incorrect old password',
                    'status' => 400
                ], 400);
            }
    
            $user->password = $newPassword;
            $user->save();
    
            return response()->json([
                'msg' => 'Password changed successfully!',
                'status' => 200
            ], 200);
        
    }
	
    public function slider_image_view()
    {
       

       $slider = DB::select("SELECT sliders.title as name,sliders.image as image,sliders.activity_image as activity_image FROM `sliders` WHERE `status`=1");
           
  //dd($slider);
        if ($slider) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $slider
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function attendance_List(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $list = DB::select("SELECT COALESCE(COUNT(at_claim.`userid`),0) AS attendances_consecutively , COALESCE(SUM(attendances.attendance_bonus),0) AS accumulated FROM `at_claim` LEFT JOIN attendances ON at_claim.attendance_id =attendances.id WHERE at_claim.userid=$userid");

    $day = $list[0]->attendances_consecutively;
    $bonus_amt = $list[0]->accumulated;


        $attendanceList = DB::select("
   SELECT a.`id` AS `id`, a.`accumulated_amount` as accumulated_amount ,a.`attendance_bonus` as attendance_bonus, COALESCE(c.`status`, '1') AS `status`, COALESCE(a.`created_at`, 'Not Found') AS `created_at` FROM `attendances` a LEFT JOIN `at_claim` c ON a.`id` = c.`attendance_id` AND c.`userid` =$userid  ORDER BY a.`id` ASC LIMIT 7
");
  

        if (!empty($attendanceList)) {
            $response = [
                'message' => 'Attendance List',
                'status' => 200,
                'attendances_consecutively' => $day,
                'accumulated' =>$bonus_amt,
                'data' => $attendanceList,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function attendance_history(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $list1 = DB::select("SELECT at_claim.id AS id,attendances.attendance_bonus AS attendance_bonus,at_claim.created_at FROM attendances LEFT JOIN at_claim ON at_claim.attendance_id=attendances.id WHERE at_claim.userid=$userid");

    
  

        if (!empty($list1)) {
            $response = [
                'message' => 'Attendance History',
                'status' => 200,
                'data' => $list1,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function attendance_claim(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
    
        $results = DB::select("SELECT a.`id` AS `id`, a.`accumulated_amount` AS accumulated_amount, a.`attendance_bonus` AS attendance_bonus, COALESCE(c.`status`, '1') AS `status`, COALESCE(a.`created_at`, 'Not Found') AS `created_at`, u.`wallet` FROM `attendances` a LEFT JOIN `at_claim` c ON a.`id` = c.`attendance_id` AND c.`userid` = $userid JOIN `users` u ON u.id = $userid WHERE COALESCE(c.`status`, '1') = '1' ORDER BY a.`id` ASC LIMIT 7");
    //dd($results);
        if (count($results) > 0) {
            $bonus = $results[0]->attendance_bonus;
            $id = $results[0]->id;
            $accumulated_amount =$results[0]->accumulated_amount;
            $wallet = $results[0]->wallet;
    if($wallet >= $accumulated_amount){
            $count = DB::select("SELECT COALESCE(COUNT(userid), 0) AS userid FROM `at_claim` WHERE userid = $userid AND DATE(created_at) = CURDATE()");
		//dd($count);
        
       // dd($count);
            $datetime = now();
            if ($count[0]->userid == 0) {
				//dd("hii");
                DB::table('at_claim')->insert([      
                    'userid' => $userid,
                    'attendance_id' => $id,   
                    'status' => '0',
                    'created_at' => $datetime,
                    'updated_at' => $datetime    
                ]);
    
                // Assuming you have `$datetime` defined somewhere
                // DB::table('users')->where('id', $userid)->increment('wallet', $bonus);
             DB::table('users')
    ->where('id', $userid)
    ->increment('wallet', $bonus);  // Increments wallet by $bonus

DB::table('users')
    ->where('id', $userid)
    ->increment('recharge', $bonus);  // Increments recharge by $bonus



                DB::table('wallet_history')->insert([
                    'userid' => $userid,
                    'amount' => $bonus,
                    'subtypeid' => 14,
                    'created_at' => $datetime,
                    'updated_at' => $datetime
                ]);
    
                $response = [
                    'message' => 'Today Claimed Successfully ...!',
                    'status' => 200,
                ];
                return response()->json($response, 200);
            } else {
                return response()->json(['message' => 'Today You Have Already Claimed', 'status' => 400], 400); 
            }
    }else{
      return response()->json(['message' => 'You can not claim due to insufficient Balance...!', 'status' => 400], 400);  
    }
            
        } else {
            return response()->json(['message' => 'User Not Found!', 'status' => 400], 400);
        }
    }
  
    public function activity_rewards(Request $request)
    {
    date_default_timezone_set('Asia/Kolkata');
    $date = now()->format('Y-m-d');

    $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    $userid = $request->userid;  

    // Calculate total bet amount for the user on the given date
    $bet_amount = DB::table('bets')
        ->where('userid', $userid)
        ->whereDate('created_at', '=', $date)
        ->sum('amount');

    // Retrieve activity rewards for the user
    $invite_bonus = DB::select("
        SELECT 
            a.id AS activity_id,
            a.amount,
            a.range_amount,
            a.name,
            COALESCE(c.status, '1') AS status,
            COALESCE(a.created_at, 'Not Found') AS created_at
        FROM 
            activity_rewards a
        LEFT JOIN 
            activity_rewards_claim c 
        ON 
            a.id = c.acyivity_reward_id 
        AND 
            c.userid = ?
        ORDER BY 
            a.id ASC
    ", [$userid]);

    if (!empty($invite_bonus)) {
        foreach ($invite_bonus as $bonus) {
            if ($bet_amount >= $bonus->range_amount) {
                // Check if already claimed but still status is 0
                $claim = DB::table('activity_rewards_claim')
                    ->where('userid', $userid)
                    ->where('acyivity_reward_id', $bonus->activity_id)
                    ->first();

                if ($claim && $claim->status == 0) {
                    $bonus->status = 0; // already eligible and pending claim
                } else {
                    $bonus->status = 2; // eligible to claim
                }
            }
        }

        return response()->json([
            'message' => 'Activity rewards list',
            'status' => 200,
            'bet_amount' => $bet_amount,
            'data' => $invite_bonus
        ]);
    } else {
        return response()->json([
            'message' => 'Not found..!',
            'status' => 400,
            'data' => []
        ], 400);
    }
}
    
    public function activity_rewards_history(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric',
         'subtypeid'=>'required',
         
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
     $subtypeid = $request->subtypeid;  
       // $userid = $request->input('userid');

       $act_reward_hist=DB::select("SELECT wallet_history.*,subtype.name as name FROM `wallet_history` LEFT JOIN subtype ON wallet_history.subtypeid=subtype.id WHERE wallet_history.userid=$userid && wallet_history.subtypeid=$subtypeid");
       
  

        if (!empty($act_reward_hist)) {
            $response = [
                'message' => 'activity rewards List',
                'status' => 200,
                'data' => $act_reward_hist,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function activity_rewards_claim(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric',
            'amount' => 'required',
            'activity_id'=>'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
        $amount = $request->amount;
        $activity_id=$request->activity_id;
        $bonusClaim = DB::table('activity_rewards_claim')
                    ->where('userid', $userid)
                    ->where('acyivity_reward_id', $activity_id)
                    ->get();
                    // dd($bonusClaim);
                    
    if($bonusClaim->isEmpty()){
    $user = DB::table('users')->where('id', $userid)->first();
    if (!empty($user)) {
       $usser= DB::table('users')->where('id', $userid)->update([
            'wallet' => $user->wallet + $amount, // Add amount to wallet
        ]);
    }else{
     return response()->json([
    				'message' => 'user not found ..!',
    				'status' => 400,
                    ], 400);
     }
     if (!empty($usser)) {
        // Insert into wallet_histories
        $bonuss=DB::table('wallet_history')->insert([
            'userid'     => $userid,
            'amount'      => $amount,
            'description' => 'Invitation Bonus',
            'subtypeid'     => 11, // Define type_id as 1 for bonus claim
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        
         $bonuss=DB::table('activity_rewards_claim')->insert([
            'userid'     => $userid,
            'acyivity_reward_id' => $activity_id,
            'status' => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
     }else{
     
     }
         if (!empty($bonuss)) {
                $response = [
                    'message' => 'invitation bonus claimed successfully!',
                    'status' => 200,
                ];
                return response()->json($response,200);
            } else {
                return response()->json([
    				'message' => 'Bonus not claimed ..!',
    				'status' => 400,
                    ], 400);
            }
            
           } else{
             return response()->json([
    				'message' => 'Already claimed ..!',
    				'status' => 400,
                    ], 400);  
           }
    	}
        
   	public function invitation_bonus_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
       $total_refer = DB::table('users')->where('referral_user_id', $userid)->count();
      
    // Fetch all users referred by the user with the given $userid
    $refer_users = DB::table('users')->where('referral_user_id', $userid)->get();
    
    $count_users = 0; // Initialize the count of users whose total deposit > 500
    
    // Loop through each referred user to calculate the total deposit sum
    foreach ($refer_users as $refer_user) {
        $user_id = $refer_user->id;
        
        // Calculate the total deposit amount for this user from the 'payins' table
        $deposit_count = DB::select("SELECT SUM(`cash`) as total_amount FROM `payins` WHERE `user_id` = :user_id", ['user_id' => $user_id]);
        
        // Get the total deposit amount for the user (or 0 if null or no rows)
        $total_deposit = $deposit_count[0]->total_amount ?? 0;
    
        // Only count users whose total deposit is greater than 500
        if ($total_deposit >= 500) {
            $count_users++; // Increment the count of users whose total deposit > 500
        }
    }
    
    
    $invite_bonus = DB::select("
        SELECT 
            a.id AS bonus_id,
            a.amount,
            a.claim_amount,
            a.no_of_user,
            CASE 
                WHEN c.userid = ? AND c.invite_id = a.id AND a.no_of_user <= ? THEN 0
                WHEN a.no_of_user <= ? THEN 2 
                ELSE COALESCE(c.status, '1') 
            END AS status,
            COALESCE(a.created_at, 'Not Found') AS created_at
        FROM 
            invite_bonus a
        LEFT JOIN 
            invite_bonus_claim c 
        ON 
            a.id = c.invite_id 
        AND 
            c.userid = ?
        ORDER BY 
            a.id ASC
    ", [$userid, $count_users, $count_users, $userid]);
    
    
    
        if (!empty($invite_bonus)) {
            $response = [
                'message' => 'invitation_bonus_list',
                'status' => 200,
                'data' => collect($invite_bonus)->map(function ($bonus) use ($total_refer, $count_users) {
                    return [
                        'bonus_id' => $bonus->bonus_id,
                        'amount' => $bonus->amount,
                        'claim_amount' => $bonus->claim_amount,
                        'no_of_user' => $bonus->no_of_user,
                        'status' => $bonus->status,
                        'created_at' => $bonus->created_at,
                        'no_of_invitees' => $total_refer,
                        'refer_invitees' => $count_users
                    ];
                })
            ];
            return response()->json($response);
        } else {
            return response()->json([
                'message' => 'Not found..!',
                'status' => 400,
                'data' => []
            ], 400);
        }
    }
    
    public function invitation_bonus_list_old(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
       $total_refer = DB::table('users')->where('referral_user_id', $userid)->count();
      
    // Fetch all users referred by the user with the given $userid
    $refer_users = DB::table('users')->where('referral_user_id', $userid)->get();
    
    $count_users = 0; // Initialize the count of users whose total deposit > 500
    
    // Loop through each referred user to calculate the total deposit sum
    foreach ($refer_users as $refer_user) {
        $user_id = $refer_user->id;
        
        // Calculate the total deposit amount for this user from the 'payins' table
        $deposit_count = DB::select("SELECT SUM(`cash`) as total_amount FROM `payins` WHERE `user_id` = :user_id", ['user_id' => $user_id]);
        
        // Get the total deposit amount for the user (or 0 if null or no rows)
        $total_deposit = $deposit_count[0]->total_amount ?? 0;
    
        // Only count users whose total deposit is greater than 500
        if ($total_deposit >= 500) {
            $count_users++; // Increment the count of users whose total deposit > 500
        }
    }
    
    
    $invite_bonus = DB::select("
        SELECT 
            a.id AS bonus_id,
            a.amount,
            a.claim_amount,
            a.no_of_user,
            CASE 
                WHEN c.userid = ? AND c.invite_id = a.id AND a.no_of_user <= ? THEN 0
                WHEN a.no_of_user <= ? THEN 2 
                ELSE COALESCE(c.status, '1') 
            END AS status,
            COALESCE(a.created_at, 'Not Found') AS created_at
        FROM 
            invite_bonus a
        LEFT JOIN 
            invite_bonus_claim c 
        ON 
            a.id = c.invite_id 
        AND 
            c.userid = ?
        ORDER BY 
            a.id ASC
    ", [$userid, $count_users, $count_users, $userid]);
    
    
    
        if (!empty($invite_bonus)) {
            $response = [
                'message' => 'invitation_bonus_list',
                'status' => 200,
                'data' => collect($invite_bonus)->map(function ($bonus) use ($total_refer, $count_users) {
                    return [
                        'bonus_id' => $bonus->bonus_id,
                        'amount' => $bonus->amount,
                        'claim_amount' => $bonus->claim_amount,
                        'no_of_user' => $bonus->no_of_user,
                        'status' => $bonus->status,
                        'created_at' => $bonus->created_at,
                        'no_of_invitees' => $total_refer,
                        'refer_invitees' => $count_users
                    ];
                })
            ];
            return response()->json($response);
        } else {
            return response()->json([
                'message' => 'Not found..!',
                'status' => 400,
                'data' => []
            ], 400);
        }
    }
    
    public function Invitation_reward_rule(Request $request)
    {
          

       $rule=DB::select("SELECT * FROM `invite_bonus`");
       
  

        if (!empty($rule)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $rule,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function Invitation_records(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;
 

       $records=DB::select("SELECT `username`,`u_id`,`first_recharge_amount`,`created_at` FROM `users` WHERE `referral_user_id`=$userid");
       
  

        if (!empty($records)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $records,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function extra_first_payin(Request $request)
    {
       
         $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'cash' => 'required',
            'type' => 'required',
        ]);
        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];

            return response()->json($response);
        }

        
        
	$cash = $request->cash;
    // $extra_amt = $request->extra_cash;
     $type = $request->type;
    $userid = $request->user_id;
	   //	$total_amt=$cash+$extra_amt+$bonus;
		 
               $date = date('YmdHis');
        $rand = rand(11111, 99999);
        $orderid = $date . $rand;

        $check_id = DB::table('users')->where('id',$userid)->first();
        if($type == 1){
        if ($check_id) {
            $redirect_url = env('APP_URL')."api/checkPayment?order_id=$orderid";
            //dd($redirect_url);
            $insert_payin = DB::table('payins')->insert([
                'user_id' => $request->user_id,
                'cash' => $request->cash,
                'type' => $request->type,
                'order_id' => $orderid,
                'redirect_url' => $redirect_url,
                'status' => 1 // Assuming initial status is 0
            ]);
         // dd($redirect_url);
            if (!$insert_payin) {
                return response()->json(['status' => 400, 'message' => 'Failed to store record in payin history!']);
            }
 
            $postParameter = [
                'merchantid' => "INDIANPAY00INDIANPAY0033",
                'orderid' => $orderid,
                'amount' => $request->cash,
                'name' => $check_id->u_id,
                'email' => "abc@gmail.com",
                'mobile' => $check_id->mobile,
                'remark' => 'payIn',
                'type'=>$request->cash,
                'redirect_url' => env('APP_URL')."api/checkPayment?order_id=$orderid"
               // 'redirect_url' => config('app.base_url') ."/api/checkPayment?order_id=$orderid"
            ];


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://indianpay.co.in/admin/paynow',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0, 
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postParameter),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Cookie: ci_session=1ef91dbbd8079592f9061d5df3107fd55bd7fb83'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
             
			echo $response;
		//	dd($response);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Internal error!'
            ]);
        }
            
        }else{
           return response()->json([
                'status' => 400,
                'message' => 'USDT is Not Supported ....!'
            ]); 
        }
    }

    public function checkPayment1(Request $request)
    {
       // dd($request);
        $orderid = $request->input('order_id');
	//dd($orderid);
     //bonus = gift_cash
        if ($orderid == "") {
            return response()->json(['status' => 400, 'message' => 'Order Id is required']);
        } else {
            $match_order = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->first();
//dd($match_order);
            if ($match_order) {
                $uid = $match_order->user_id;
            
                $cash = $match_order->cash;
                $type = $match_order->type;
               
                $orderid = $match_order->order_id;
                
                $datetime=now();
               // dd("UPDATE payins SET status = 2 WHERE order_id = $orderid AND status = 1 AND user_id = $uid");

              $update_payin = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->where('user_id', $uid)->update(['status' => 2]);
    
                if ($update_payin) {
                    
                    // $wallet = $cash + $bonus + $extra_cash;
                    // $bonusToAdd = $bonus;
                    //dd($uid);
    $referid=DB::select("SELECT referral_user_id,first_recharge FROM `users` WHERE id=$uid");
    //dd($referid);
    $first_recharge=$referid[0]->first_recharge;
    $referuserid=$referid[0]->referral_user_id;
   // dd($first_recharge);
if($first_recharge == 0){
    
    $extra=DB::select("SELECT * FROM `extra_first_deposit_bonus` WHERE `first_deposit_ammount`=$cash"); 
    $id=$extra[0]->id;
    $first_deposit_ammount=$extra[0]->first_deposit_ammount;
    $bonus=$extra[0]->bonus;
    
    $amount=$bonus+$first_deposit_ammount;

    DB::INSERT("INSERT INTO `extra_first_deposit_bonus_claim`( `userid`, `extra_fdb_id`, `amount`, `bonus`, `status`, `created_at`, `updated_at`) VALUES ('$uid','$id','$first_deposit_ammount','$bonus','0','$datetime','$datetime')");
   
                    $updateUser =DB::update("UPDATE users 
    SET 
    wallet = wallet + $amount,
    first_recharge = first_recharge + $cash,
    first_recharge_amount = first_recharge_amount + $cash,
    recharge = recharge + $cash,
    total_payin = total_payin + $cash,
    no_of_payin = no_of_payin + 1,
    deposit_balance = deposit_balance + $cash
    WHERE id = $uid;
    ");
    //dd("hiii");
    // dd("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1,yesterday_first_deposit = yesterday_first_deposit + $cash WHERE id=$referuserid");
    //dd($referuserid);
    DB::UPDATE("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1,yesterday_first_deposit = yesterday_first_deposit + $cash WHERE id=$referuserid");
     return redirect()->away(env('APP_URL').'uploads/payment_success.php');
}else{
    
      $updateUser =DB::update("UPDATE users 
    SET 
    wallet = wallet + $cash,
    recharge = recharge + $cash,
    total_payin = total_payin + $cash,
    no_of_payin = no_of_payin + 1,
    deposit_balance = deposit_balance + $cash
    WHERE id = $uid;
    ");
    
    //dd("hello");
     //dd($referuserid);
    DB::select("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1 WHERE id=$referuserid");
     return redirect()->away(env('APP_URL').'uploads/payment_success.php');
}

     
    
                    if ($updateUser) {
                        // Redirect to success page
                        //dd("hello");
                        return redirect()->away(env('APP_URL').'uploads/payment_success.php');
                    } else {
                        return response()->json(['status' => 400, 'message' => 'User balance update failed!']);
                    }
                } else {
                    return response()->json(['status' => 400, 'message' => 'Failed to update payment status!']);
                }
            } else {
                return response()->json(['status' => 400, 'message' => 'Order id not found or already processed']);
            }
        }
    }
	
    public function extra_first_deposit_bonus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
     
    
            
            $rowCount = DB::table('extra_first_deposit_bonus_claim')->where('userid', $userid)->count();
            
            if ($rowCount > 0) {
                $checkDate = DB::select("SELECT extra_first_deposit_bonus.first_deposit_ammount as first_deposit_ammount, extra_first_deposit_bonus.bonus as bonus, extra_first_deposit_bonus.bonus + extra_first_deposit_bonus.bonus as totalamount, COALESCE(extra_first_deposit_bonus_claim.status, 1) as status FROM extra_first_deposit_bonus LEFT JOIN extra_first_deposit_bonus_claim ON extra_first_deposit_bonus.first_deposit_ammount = extra_first_deposit_bonus_claim.amount AND extra_first_deposit_bonus_claim.userid = ? ORDER BY COALESCE(extra_first_deposit_bonus_claim.status, 1) DESC", [$userid]); 
               
                if (!empty($checkDate)) {
                    return response()->json([
                        'msg' => 'Successfully...!',
                        'status' => '200',
                        'data' => $checkDate
                    ]);
                } else {
                    return response()->json([
                        'msg' => 'Internal error...',
                        'status' => '400'
                    ]);
                }
            } else {
               
                $checkDate = DB::table('extra_first_deposit_bonus')->select('first_deposit_ammount', 'bonus', DB::raw('first_deposit_ammount + bonus as totalamount'), 'status','created_at')->get(); 
               
                
                if (!empty($checkDate)) {
                    return response()->json([
                        'msg' => 'Successfully...!',
                        'status' => '200',
                        'data' => $checkDate
                    ]);
                }
            }
        
    }
    
    public function  level_getuserbyrefid(Request $request)
    {
    
        
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        date_default_timezone_set('Asia/Kolkata');
        $datetime = date('Y-m-d H:i:s');
    
        $userId = $request->input('id');
        $refer_code = User::where('id', $userId)->value('referral_code');
        $user_data = User::select('id','username', 'today_turnover', 'total_payin', 'no_of_payin', 'referral_user_id', 'yesterday_payin','yesterday_register','referral_code','yesterday_first_deposit','yesterday_no_of_payin','deposit_balance','yesterday_total_commission','u_id','totalbet','first_recharge','turnover')->get()->toArray();
        $mlm_level_data = DB::table('mlm_levels')->get()->toArray();
    
        $alldata = [];
        $lastlevelname = 'Tier 6';
        foreach ($mlm_level_data as $mlm_level) {
            $name = $mlm_level->name;
            $commission = $mlm_level->commission;
            $usermlm = [];
    
            if ($name == 'Tier 1') {
                $usermlm[] = $userId;
            } else {
                $data = $mlm_level_data[array_search($mlm_level, $mlm_level_data) - 1]->name;
                foreach ($alldata[$data] as $itemss) {
                    $usermlm[] = $itemss['user_id'];
                }
            }
    
            $filtered_users = array_filter($user_data, function($item) use ($usermlm) {
                return in_array($item['referral_user_id'], $usermlm);
            });
    
            $level = [];
            foreach ($filtered_users as $item) {
                $todays = $item['today_turnover'] * $commission * 0.01 ;
                $level[] = [
                    "user_id" => $item['id'],
                     "u_id" => $item['u_id'],
                     'totalbet'=> $item['totalbet'],
                    "username" => $item['username'],
                    "first_recharge"=>$item['first_recharge'],
                     "deposit_amount" => $item['deposit_balance'],
                    "turnover" => $item['turnover'],
                    'today_turnover'=>$item['today_turnover'],
                    "commission" => number_format((float)$todays, 2, '.', ''),
                    'total_payin'=> $item['total_payin'],
                    'no_of_payin'=>$item['no_of_payin'],
                    'yesterday_payin'=>$item['yesterday_payin'],
                    'yesterday_register'=>$item['yesterday_register'],
                    'yesterday_no_of_payin'=>$item['yesterday_no_of_payin'],
                    'yesterday_first_deposit'=>$item['yesterday_first_deposit']
                ];
            }
    
            $alldata[$name] = $level;
            $lastlevelname = $name;
        }
    
        $totalcommission = 0;
        $totaluser = 0;
        $datalevelcome = [];
        $indirectTeam = 0;
        $numofpayindirect = 0;
        $numofpayteam = 0;
        $payinAmountDirect = 0;
        $payinAmountTeam = 0;
        $noUserDirect = 0;
        $noUserTeam = 0;
        $noOfFristPayinDirect = 0;
        $noOfFristPayinTeam = 0;
        
        $yesterday_total_commission = 0;
        
        $yesterday_payin_direct = 0;
        $yesterday_register_direct = 0;
        $yesterday_no_of_payin_direct = 0;
        $yesterday_first_deposit_direct = 0;
    
        $yesterday_payin_team = 0;
        $yesterday_register_team = 0;
        $yesterday_no_of_payin_team = 0;
        $yesterday_first_deposit_team = 0;
    
       
            $deposit_number_all=0;
            $deposit_amount_all=0;
            $first_recharge_all=0;
            $no_of_firstrechage_all=0;
            $total_bet_all=0;
            $total_bet_amount_all=0;   
       
       
    
        foreach ($mlm_level_data as $mlm_level) {
            $name = $mlm_level->name;
            $levelcom = 0;
            $deposit_number=0;
            $deposit_amount=0;
            $first_recharge=0;
            $no_of_firstrechage=0;
            $total_bet=0;
            $total_bet_amount=0;
    
            foreach ($alldata[$name] as $obj) {
                $totalcommission += $obj['commission'];
                $deposit_number_all+=$obj['total_payin'];
            $deposit_amount_all+=$obj['no_of_payin'];
            $first_recharge_all+=$obj['first_recharge'];
            $no_of_firstrechage_all+=$no_of_firstrechage;
            $total_bet_all+=$total_bet;
            $total_bet_amount_all+=$total_bet_amount; 
            
            
            
                $totaluser++;
                $levelcom += $obj['commission'];
                if ($name == 'Tier 1') {
                    $payinAmountDirect += $obj['total_payin'];
                    $noUserDirect++;
                    if ($obj['yesterday_payin'] != '0') {
                         $numofpayindirect++;
                        $noOfFristPayinDirect++;
                    }
                    if ($obj['no_of_payin'] != '0') {
                      //  $numofpayindirect++;
                    }
                    
                    $yesterday_payin_direct += $obj['yesterday_payin'];
                    $yesterday_register_direct = $obj['yesterday_register'];
                   // $yesterday_no_of_payin_direct += $obj['yesterday_no_of_payin'];
                    $yesterday_first_deposit_direct += $obj['yesterday_first_deposit'];
    
                } else {
                    $payinAmountTeam += $obj['total_payin'];
                    $noUserTeam++;
                    $indirectTeam++;
                    if ($obj['total_payin'] != '0') {
                        $noOfFristPayinTeam++;
                    }
                    if ($obj['no_of_payin'] != '0') {
                        $numofpayteam++;
                    }
                    if ($name != $lastlevelname) {
                        if($obj['first_recharge'] > 0){
                            
                       $first_recharge += $obj['first_recharge'];
    
                           $no_of_firstrechage++;
                        }
                        $total_bet_amount += $obj['today_turnover']+$obj['turnover'];
                        $total_bet += $obj['totalbet'];
                        
                        
                        
                        $deposit_number += $obj['no_of_payin'];
                        $deposit_amount +=$obj['total_payin'];
                        $yesterday_payin_team += $obj['yesterday_payin'];
                        $yesterday_register_team += $obj['yesterday_register'];
                        $yesterday_no_of_payin_team += $obj['yesterday_no_of_payin'];
                        $yesterday_first_deposit_team += $obj['yesterday_first_deposit'];
                    }
                }
            }
    
            $datalevelcome[] = [
                'count' => count($alldata[$name]),
                'name' => $name,
                'commission' => number_format($levelcom, 2, '.', ''),
                'total_payin'=>$deposit_amount,
                'no_of_payin' =>$deposit_number,
                'first_recharge' =>$first_recharge,
                'no_of_people'=>$no_of_firstrechage,
                'totalbet'=>$total_bet,
                'total_bet_amount'=>$total_bet_amount
                
            ];
          
        }
      $datalevelcome[]=[
            'count' => $totaluser,
            'name' => "all",
            'commission' => number_format($totalcommission, 2, '.', ''),
            'total_payin'=>$deposit_number_all,
            'no_of_payin' =>$deposit_amount_all,
            'first_recharge' =>$first_recharge_all,
            'no_of_people'=>$no_of_firstrechage_all,
            'totalbet'=>$total_bet_all,
            'total_bet_amount'=>$total_bet_amount_all
                ];
        return response()->json([
            'direct_user_count' => $yesterday_register_direct ?? 0,
            'numofpayindirect' => $yesterday_no_of_payin_direct ?? 0,
            'noUserDirect' => $yesterday_register_direct ?? 0,
            'noOfFristPayinDirect' => $numofpayindirect ?? 0,
            'payinAmountDirect' => $yesterday_payin_direct ?? 0,
            'indirect_user_count' => $yesterday_register_team ?? 0,
            'numofpayteam' => $yesterday_no_of_payin_team ?? 0,
            'payinAmountTeam' => $yesterday_payin_team ?? 0,
            'noUserTeam' => $yesterday_register_team ?? 0,
            'noOfFristPayinTeam' => $yesterday_first_deposit_team ?? 0,
            'total_payin_direct'=> $payinAmountDirect ?? 0,
            'total_register_direct'=>$noUserDirect ?? 0,
            'total_no_of_payin_direct'=>$numofpayindirect ?? 0,
            'total_first_deposit_direct'=>$noOfFristPayinDirect ?? 0,
            'total_payin_team'=>$payinAmountTeam ?? 0,
            'total_register_team'=>$noUserTeam ?? 0,
            'total_no_of_payin_team'=>$numofpayteam ?? 0,
            'total_first_deposit_team'=>$noOfFristPayinTeam ?? 0,      
            'totaluser' => "$totaluser" ?? 0,
            'totalcommission' => number_format($totalcommission, 2, '.', ''),
            'yesterday_totalcommission' => number_format($yesterday_total_commission, 2, '.', ''),
            'user_refer_code' => $refer_code,
            'levelwisecommission' => $datalevelcome ?? 0,
            'user_id' => $userId ?? 0,
            'userdata' => $alldata ?? 0,
            ///
            // 'all_total_payin'=>$deposit_number_all,
            // 'all_no_of_payin' =>$deposit_amount_all,
            // 'all_first_recharge' =>$first_recharge_all,
            // 'all_no_of_people'=>$no_of_firstrechage_all,
            // 'all_totalbet'=>$total_bet_all,
            // 'all_total_bet_amount'=>$total_bet_amount_all
        ]);
    }
      
    public function commission_details(Request $request)
    {
             $validator = Validator::make($request->all(), [
            'userid' => 'required|integer',
            'subtypeid'=>'required|integer',
            'date'=>'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
             $userid = $request->userid;
             $subtypeid= $request->subtypeid;
             $date=$request->created_at;
    
           $commission=DB::select("SELECT * FROM `wallet_history` WHERE `userid`=$userid && `subtypeid`=$subtypeid &&`created_at` LIKE '%$date%'");
           
          $data=[];
    foreach ($commission as $item){
        
           
           $amount=$item->amount;
           $description=$item->description;
           $description2=$item->description_2;
           $created_at=$item->created_at;
           $updated_at=$item->updated_at;
        }
        
        
         $data[] = [
             'number_of_bettors'=>$description2,
             'bet_amount'=>$description,
             'commission_payout'=>$amount,
             'date'=>$created_at,    
             'settlement_date'=>$updated_at       
             ];
              
    
            if (!empty($data)) {
                $response = [
                    'message' => 'commission_details',
                    'status' => 200,
                    'data' => $data,
                ];
                return response()->json($response);
            } else {
                return response()->json(['message' => 'Not found..!','status' => 400,
                    'data' => []], 400);
            }
        }
    
    public function all_rules(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'type' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $type = $request->type;
 

      $records=DB::select("SELECT name,list FROM `rules` WHERE `type`=$type");
       
 
        if (!empty($records)) {
            $response = [
                'message' => 'rules list',
                'status' => 200,
                'data' =>$records,
            ];
            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function subordinate_userlist(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'id' => 'required|numeric',
        'type' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->id;
    $type = $request->type;
 
if($type == 1){
       $list=DB::select("SELECT `u_id` AS user_name, `mobile` AS mobile, `created_at` AS datetime 
FROM `users` 
WHERE referral_user_id =$userid  
AND DATE(`created_at`) = CURDATE();
");
}elseif ($type == 2) {
    $list=DB::select("SELECT `u_id` AS user_name, `mobile` AS mobile, `created_at` AS datetime 
FROM `users` 
WHERE referral_user_id = $userid 
AND DATE(`created_at`) = DATE_SUB(CURDATE(), INTERVAL 1 DAY);
");
}else{
    $list=DB::select("SELECT `u_id` AS user_name, `mobile` AS mobile, `created_at` AS datetime 
FROM `users` 
WHERE referral_user_id = $userid 
AND `created_at` BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE();
");
}
  

        if (!empty($list)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $list,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function commission_distribution()
    {
        $datetime = now();
        $user_data = User::select('id', 'today_turnover', 'referral_user_id', 'u_id', 'first_recharge', 'turnover')
            ->where('first_recharge', '!=', 0)
            ->get()
            ->toArray();
       
        $mlm_level_data = DB::table('mlm_levels')->get()->toArray();
        
        $userIds = [];
        $bonusWalletIncrement = 0;
    
        $inserts = [];
    
        foreach ($user_data as $item) {
            $user_id = $item['id'];
    
            $commission = $this->commission_distribute_mlm($mlm_level_data, $user_data, $user_id);
            $no_of_bet = $commission['no_of_bet'];
            $betamount = $commission['betamount'];
            $commissions = $commission['commission'];
    
            // Collect user IDs for bulk update
            $userIds[] = $user_id;
    
            // Build insert query
            if($commissions>0){
            $inserts[] = [
                'userid' => $user_id,
                'amount' => $commissions,
                'subtypeid'=>23,
                'description' => $betamount,
                'description_2' => $no_of_bet,
                'created_at' => $datetime
            ];
            }
            // Increment bonus wallet
            $bonusWalletIncrement += $commissions;
        }
    //dd($userIds);
        // Bulk update
        if (!empty($userIds)) {
            $userIdsString = implode(',', $userIds);
            
            $updateQuery = "
                UPDATE users 
                SET 
                    turnover = CASE 
                        " . implode(' ', array_map(function ($userId) {
                            return "WHEN $userId THEN turnover + today_turnover";
                        }, $userIds)) . "
                        ELSE today_turnover 
                    END,
                    today_turnover = 0,
                    bonus_wallet = bonus_wallet + $bonusWalletIncrement,
                    yesterday_payin = 0,
                    today_turnover=0,
                    yesterday_no_of_payin = 0,
                    yesterday_first_deposit = 0,
                    yesterday_total_commission = 0,
                    yesterday_register = 0,
                    recharge = recharge + $bonusWalletIncrement 
                WHERE 
                    id IN ($userIdsString)
            ";
            //dd($updateQuery);
    
            // Execute update query
            DB::statement($updateQuery);
        }
    
        // Bulk insert
        if (!empty($inserts)) {
            DB::table('wallet_history')->insert($inserts);
        }
    }
    	
    private function commission_distribute_mlm($mlm_level_data,$user_data,$user_id)
    {
          $all_data = [];
        $last_level_name = 'Tier 6';  
         $total_commission = 0;
        $user_id = $user_id;
        $no_of_bet=0;
        $betamount=0;
        
    
        foreach ($mlm_level_data as $mlm_level) {
            $name = $mlm_level->name;
            $commission = $mlm_level->commission;
            $user_mlm = [];
    
            if ($name == 'Tier 1') {
                $user_mlm[] = $user_id;
             }
            // else {
            //     $data = $mlm_level_data[array_search($mlm_level, $mlm_level_data) - 1]->name;
            //     foreach ($all_data[$data] as $item) {
            //         $user_mlm[] = $item['user_id'];
            //     }
            // }
            
            $index = array_search($mlm_level, $mlm_level_data);
    if ($index !== false && $index > 0) {
        $data = $mlm_level_data[$index - 1]->name;
        foreach ($all_data[$data] as $item) {
            $user_mlm[] = $item['user_id'];
        }
    }
    
            // Filter users based on MLM structure
            $filtered_users = array_filter($user_data, function ($item) use ($user_mlm) {
                return in_array($item['referral_user_id'], $user_mlm);
            });
    
            // Calculate commission for each user at this level
            $level = [];
            foreach ($filtered_users as $item) {
                if($item['today_turnover']){
                    $no_of_bet++;
                    $betamount+=$item['today_turnover'];
                }
                $todays = $item['today_turnover'] * $commission * 0.01;
              
                $level[] = [
                    "user_id" => $item['id'],
                    "turnover" => $item['turnover'],
                    'today_turnover' => $item['today_turnover'],
                    "commission" => number_format((float)$todays, 2, '.', ''),
                ];
            }
    
            // Store commission data for this level
            $all_data[$name] = $level;
            $last_level_name = $name;
        }
    
        foreach ($mlm_level_data as $mlm_level) {
            $name = $mlm_level->name;
            foreach ($all_data[$name] as $obj) {
                $total_commission += $obj['commission'];
              
            }
        }
          $user_id = $user_id;
        $no_of_bet=0;
        $betamount=0;
        $finaldatas=array(
            'id'=>$user_id,
            'no_of_bet'=>$no_of_bet,
           'betamount'=> $betamount,
           'commission'=>$total_commission
            );
        return $finaldatas;
    }
    
    public function betting_rebate(){
        
        $currentDate = date('Y-m-d');
    		 
    		 $a = DB::select("SELECT sum(amount) as betamount, userid FROM bets WHERE created_at like '$currentDate %' AND status= '2' GROUP BY userid;");
    
    		
    		//$a = DB::select("SELECT `today_turnover` FROM `users` WHERE `id`=$userid ");
    		
    		foreach($a as $item){
    		
    		   $betamount = $item->betamount;
    		   $userid = $item->userid;
    			
    			DB::select("UPDATE users SET wallet = wallet + $betamount * 0.01 WHERE id = $userid");
    		$rebate_rate=0.01;
    		  $insert= DB::table('wallet_history')->insert([
            'userid' => $userid,
            'amount' => $betamount*$rebate_rate,
            'description'=>$betamount,
            'description_2'=>$rebate_rate,
            'subtypeid' => 25,
    		'created_at'=> now(),
            'updated_at' => now()
    		
            ]);
    		
    	   }
    		
    	}		
    	
    	
    public function betting_rebate_history(Request $request)
    {
             
             $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric',
            'subtypeid' => 'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
        $subtypeid = $request->subtypeid;
        
        $value=DB::select("SELECT 
        COALESCE(SUM(amount), 0) as total_rebet,
        COALESCE(SUM(description), 0) as total_amount,
        COALESCE(SUM(CASE WHEN DATE(CURDATE()) = CURDATE() THEN amount ELSE 0 END), 0) as today_rebet 
    FROM 
        wallet_history 
    WHERE 
        userid = $userid && subtypeid =$subtypeid");
        
        $records=DB::select("SELECT 
        `amount` as rebate_amount,description_2 as rebate_rate,created_at as datetime,
        COALESCE((SELECT SUM(description) FROM wallet_history WHERE `userid` = $userid AND subtypeid = $subtypeid), 0) as betting_rebate 
    FROM 
        `wallet_history` 
    WHERE 
        `userid` = $userid AND subtypeid = $subtypeid;");
    
    
           
     
            if (!empty($records)) {
                $response = [
                    'message' => 'Betting Rebet List',
                    'status' => 200,
                    'data1' =>$records,
                    'data' =>$value,
                ];
                return response()->json($response,200);
            } else {
                return response()->json(['message' => 'Not found..!','status' => 400,
                    'data' => []], 400);
            }
     
    
        }	
	
	public function versionApkLink(Request $request)
    {
        
            $data = DB::SELECT("SELECT * FROM `versions` WHERE `id`=1"); // Assuming you have a Version model with 'id' field

            if ($data) {
                
                $response = [
                 'msg' => 'Successfully',
                    'status' => 200,
                    'version' => $data[0]->version,
                    'link' => $data[0]->link
            ];
            return response()->json($response,200);
                
            } else {
                // If no data is found, set an appropriate response
                return response()->json([
                    'msg' => 'No record found',
                    'status' => 400
                ], 400);
            }
        
    }
	
    public function sendSMS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
        $mobile = $request->mobile;
    
        $apikey = 'Vml2ZWtvdHA6OFljNEhDeHo=';
        $type = 'TEXT';
        $sender = 'NSLSMS';
        $entityId = '1201164562188563646';
        $templateId = '1207170323851947619';
    
        $otp = rand(1000, 9999);
    
        $message = "Dear user, the OTP for $otp for Money Makers NSL LIFE";
    
        $existingOTP = DB::table('otp_sms')->where('mobile', $mobile)->first();
    
        if ($existingOTP) {
           
            DB::table('otp_sms')
                ->where('mobile', $mobile)
                ->update([
                    'otp' => $otp,
                    'status' => 1, 
                    'datetime' => now(),
                ]);
        } else {
            // Insert a new record into otp_sms table
            DB::table('otp_sms')->insert([
                'mobile' => $mobile,
                'otp' => $otp,
                'status' => 1, // Assuming 1 for successful status
                'datetime' => now(),
            ]);
        }
    
        // Make the API call
        $response = Http::get('http://login.swarajinfotech.com/domestic/sendsms/bulksms_v2.php', [
            'apikey' => $apikey,
            'type' => $type,
            'sender' => $sender,
            'entityId' => $entityId,
            'templateId' => $templateId,
            'mobile' => $mobile,
            'message' => $message,
        ]);
    
        // Validate the response
        if ($response->successful()) {
            return response()->json(['status' => 200,'message' => 'OTP sent successfully'], 200);
        } else {
            return response()->json(['status' => 400,'message' => 'Failed to send OTP'], 400);
        }
    }
    
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'otp' => 'required|numeric'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $mobile = $request->mobile;
        $otp = $request->otp;
    
        $existingOTP = DB::table('otp_sms')->where('mobile', $mobile)->first();
    
        if ($existingOTP) {
            
            if ($existingOTP->otp == $otp) {
             
              
    
                return response()->json(['status' => 200, 'message' => 'OTP verified successfully'], 200);
            } else {
                return response()->json(['status' => 400, 'message' => 'Invalid OTP'], 400);
            }
        } else {
            return response()->json(['status' => 400, 'message' => 'No OTP found for the provided mobile number'], 400);
        }
    }
    	
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
         
            'mobile' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $user = DB::table('users')
                    ->where('mobile', $request->mobile)
                    ->first();
    
        if (!$user) {
            return response()->json(['status' => 400,'message' => 'User not found'], 400);
        }
    
        $updated = DB::table('users')
                    ->where('mobile', $request->mobile)
                    ->update([
                        'password' => $request->password 
                    ]);
    
            return response()->json(['status' => 200,'message' => 'Password updated successfully'], 200);
       
    }
    
    public function invitation_bonus_claim(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric',
            'amount' => 'required',
            'invite_id'=>'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
        $amount = $request->amount;
        $invite_id=$request->invite_id;
        $bonusClaim = DB::table('invite_bonus_claim')
                    ->where('userid', $userid)
                    ->where('invite_id', $invite_id)
                    ->get();
                    // dd($bonusClaim);
                    
    if($bonusClaim->isEmpty()){
    $user = DB::table('users')->where('id', $userid)->first();
    if (!empty($user)) {
       $usser= DB::table('users')->where('id', $userid)->update([
            'wallet' => $user->wallet + $amount, // Add amount to wallet
        ]);
    }else{
     return response()->json([
    				'message' => 'user not found ..!',
    				'status' => 400,
                    ], 400);
     }
     if (!empty($usser)) {
        // Insert into wallet_histories
        $bonuss=DB::table('wallet_history')->insert([
            'userid'     => $userid,
            'amount'      => $amount,
            'description' => 'Invitation Bonus',
            'subtypeid'     => 26, // Define type_id as 1 for bonus claim
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        
         $bonuss=DB::table('invite_bonus_claim')->insert([
            'userid'     => $userid,
            'invite_id' => $invite_id,
            'status' => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
     }else{
     
     }
         if (!empty($bonuss)) {
                $response = [
                    'message' => 'invitation bonus claimed successfully!',
                    'status' => 200,
                ];
                return response()->json($response,200);
            } else {
                return response()->json([
    				'message' => 'Bonus not claimed ..!',
    				'status' => 400,
                    ], 400);
            }
            
           } else{
             return response()->json([
    				'message' => 'Already claimed ..!',
    				'status' => 400,
                    ], 400);  
           }
    	}
	
	public function getPaymentLimits()
    {
    $details = DB::select("SELECT `name`, `amount` FROM `payment_limits` WHERE 1");
    //dd($details);

    if ($details) {
        $formattedData = [];
        foreach ($details as $detail) {
            $formattedData[$detail->name] = $detail->amount;
        }
        //dd($formattedData);
//return $formattedData;
        return response()->json([
            'status' => 200,
            'message' => 'Data found',
            'data' => $formattedData
        ]);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status' => 400,
            'data' => []
        ], 400);
    }
}

	public function crypto(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|gt:0',
            'type' => 'required|in:0',
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
        }
    
        // Get input data
        $user_id = $request->user_id;
        $amount = $request->amount;
        $type = $request->type;
        $inr_amt = $amount * 94;
        
        // Get client IP address
       // $clientIp = $request->ip();
    
        // Dump and die to see IP address
        //dd('Client IP Address:', $clientIp); // Here, you can see the IP
    
        $email = 'winnbhai@gmail.com'; 
        $token = '74321855996707381194362685321790'; // Replace with a secure token or config value
        $apiUrl = "https://cryptofit.biz/Payment/coinpayments_api_call";
        $coin = 'USDT.BEP20';
    
        // Generate unique order ID
        do {
            $orderId = str_pad(mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (DB::table('payins')->where('order_id', $orderId)->exists());
    
        // User validation
        $user_exist = DB::table('users')->where('id', $user_id)->first();
    
        // Prepare API data
        $formData = [
            'txtamount' => $amount,
            'coin' => $coin,
            'UserID' => $email,
            'Token' => $token,
            'TransactionID' => $orderId,
        ];
    
        try {
            // Make API request
            $response = Http::asForm()->post($apiUrl, $formData);
    
            Log::info('PayIn API Response:', ['response' => $response->body()]);
            Log::info('PayIn API Status Code:', ['status' => $response->status()]);
    
            // Decode the response
            $apiResponse = json_decode($response->body());
            //dd($apiResponse); // You can dump API response here
    
            // Check if the API response is successful
            if ($response->successful() && isset($apiResponse->error) && $apiResponse->error === 'ok') {
                // Insert data into payins table
                $inserted_id = DB::table('payins')->insertGetId([
                    'user_id' => $user_id,
                    'status' => 1,
                    'order_id' => $orderId,
                    'cash' => $inr_amt,
                    'usdt_amount' => $amount,
                    'type' => $type,
                ]);
    
                return response()->json([
                    'status' => 200,
                    'message' => 'Payment initiated successfully.',
                    'data' => $apiResponse,
                ], 200);
            }
    
            return response()->json([
                'status' => 400,
                'message' => 'Failed to initiate payment.'
            ], 400);
        } catch (\Exception $e) {
            Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
            return response()->json(['status' => 400, 'message' => 'Internal Server Error'], 400);
        }
    }

    public function payin_call_back(Request $request)
    {
    // Validation
    $validator = Validator::make($request->all(), [
        'invoice' => 'required',
        'status_text' => 'required',
        'amount' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }

    // Get input data
    $invoice = $request->invoice;
    $status_text = $request->status_text;
    $amount = $request->amount;

    // Get client IP address
    $clientIp = $request->ip();

    // Dump and die to see IP address
    dd('Client IP Address:', $clientIp); // Here, you can see the IP

    if ($status_text == 'complete') {
        // Update payment status
        $a = DB::table('payins')->where('order_id', $invoice)->update(['status' => 2]);

        if ($a) {
            // Get user details
            $user_detail = Payin::where('order_id', $invoice)
                ->where('status', 2)
                ->first();

            $user_id = $user_detail->user_id;
            $amount1 = $user_detail->cash;

            // Update wallet balance
            $update = User::where('id', $user_id)->update(['wallet' => $amount1]);

            return response()->json(['status' => 200, 'message' => 'Payment successful.'], 200);
        } else {
            return response()->json(['status' => 400, 'message' => 'Failed to update!'], 400);
        }
    } else {
        return response()->json(['status' => 400, 'message' => 'Something went wrong!'], 400);
    }
}

	public function getUrlIp()
    {
        $url = 'https://root.winbhai.in/'; // Aapko full URL ke bajaye sirf domain name use karna hoga
    
        // Get the IPv4 address of the URL using gethostbyname
        $ipv4_address = gethostbyname($url);
    
        // Dump the IPv4 address to check
        dd('The IPv4 address of ' . $url . ' is: ' . $ipv4_address);
    }

}