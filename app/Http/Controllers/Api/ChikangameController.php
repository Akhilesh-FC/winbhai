<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
//use Carbon\Carbon;

class ChikangameController extends Controller
{

    public function autoLossChicken30Sec()
    {
        $now = Carbon::now();
    
        // 🔥 30 sec se zyada pending bets uthao
        $expiredBets = DB::table('chicken_bets')
            ->where('status', 0) // pending
            ->whereRaw("TIMESTAMPDIFF(SECOND, created_at, ?) >= 30", [$now])
            ->get();
    
        foreach ($expiredBets as $bet) {
    
            DB::table('chicken_bets')
                ->where('id', $bet->id)
                ->update([
                    'status' => 2,          // LOSS
                    'cashout_status' => 0,
                    'win_amount' => 0,
                    'updated_at' => $now
                ]);
        }
    
        return 'Auto-loss applied for expired bets';
    }


//======================BET===========================//

    public function Bet(Request $request)
    {
        $kolkataTime = Carbon::now('Asia/Kolkata')->toDateTimeString();
    
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'game_id' => 'required',
            'amount'  => 'required|numeric|min:1|max:17000',
        ])->stopOnFirstFailure();
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }
    
        $userId = $request->user_id;
        $gameId = $request->game_id;
        $amount = $request->amount;
    
        // ✅ Get wallet balances
        $user = DB::table('users')
            ->where('id', $userId)
            ->first(['wallet', 'third_party_wallet']);
    
        if (!$user || $user->wallet < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance'
            ], 200);
        }
    
        // ✅ Insert Bet
        DB::table('chicken_bets')->insert([
            'user_id'    => $userId,
            'game_id'    => $gameId,
            'amount'     => $amount,
            'status'     => 0,
            'created_at' => $kolkataTime,
            'updated_at' => $kolkataTime,
        ]);
    
        // ✅ 70–30 calculation
        $walletDeduct     = round($amount * 70 / 100, 2);
        $thirdPartyDeduct = round($amount * 30 / 100, 2);
    
        // ✅ Deduction logic
        if ($user->third_party_wallet >= $thirdPartyDeduct) {
    
            // ✅ 70% wallet + 30% third party
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'wallet'              => DB::raw("wallet - $walletDeduct"),
                    'third_party_wallet'  => DB::raw("third_party_wallet - $thirdPartyDeduct"),
                ]);
    
        } else {
    
            // ✅ Third party insufficient → full wallet
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'wallet' => DB::raw("wallet - $amount"),
                ]);
        }
    
        /* ✅ OPTIONAL: betlogs logic (as it is) */
        /*
        $multiplier = DB::table('game_settings')
            ->where('game_id', $gameId)
            ->value('multiplier');
    
        $betLogs = DB::table('betlogs')->get();
        foreach ($betLogs as $row) {
            $gameIdArray = json_decode($row->game_id, true);
            if (is_array($gameIdArray) && in_array($gameId, $gameIdArray)) {
                DB::table('betlogs')
                    ->where('number', $row->number)
                    ->update([
                        'amount' => DB::raw("amount + " . ($amount * $multiplier))
                    ]);
            }
        }
        */
    
        return response()->json([
            'success' => true,
            'message' => 'Bet Accepted Successfully!'
        ], 200);
}

//==============================BET HISTORY==================================//

    public function BetHistory(Request $request)
    {
        // Validate inputs: user_id required integer, limit optional integer >=1, offset optional integer >=0
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'limit' => 'sometimes|integer|min:1',
            'offset' => 'sometimes|integer|min:0',
        ])->stopOnFirstFailure();
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }
    
        $userId = $request->user_id;
        $limit = $request->input('limit', 10);     // Default limit 10 agar na mile request mein
        $offset = $request->input('offset', 0);    // Default offset 0 agar na mile request mein
    
        // Base query banaye user ke bets ke liye
        $betQuery = DB::table('chicken_bets')
            ->where('user_id', $userId)
            ->select('amount', 'win_amount', 'multiplier', 'created_at')
            ->orderByDesc('id');
        
        // $betQuery = DB::table('chicken_bets')
        //     ->where('user_id', $userId)
        //     ->where('account_type', 0) // <-- Ye line add ki gayi hai
        //     ->select('amount', 'win_amount', 'multiplier', 'created_at')
        //     ->orderByDesc('id');
    
    
        // Total records count (without limit/offset)
        $totalCount = $betQuery->count();
    
        // Paginated results laiye
        $betHistory = $betQuery->skip($offset)->take($limit)->get();
    
        if ($betHistory->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data found',
                'total_count' => $totalCount,
                'data' => $betHistory
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No record found',
                'data' => []
            ]);
        }
}

//===========================Cashout================================//

    public function cashout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'multiplier_id' => 'nullable',
            'game_id' => 'required',
            'userid' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }
    
        // 🔹 Get multiplier info
        $cashout_info = DB::table('multiplier')
            ->where('multiplier', $request->multiplier_id)
            ->first();
    
        if (!$cashout_info) {
            return response()->json([
                'status' => false,
                'message' => 'Multiplier not found.'
            ], 200);
        }
    
        $cashout_value = $cashout_info->multiplier;
    
        // 🔹 Get ONLY pending bet (MOST IMPORTANT FIX)
        $bet = DB::table('chicken_bets')
            ->where('game_id', $request->game_id)
            ->where('user_id', $request->userid)
            ->where('status', 0) // only pending bet
            ->orderBy('id', 'desc')
            ->first();
    
        if (!$bet) {
            return response()->json([
                'status' => false,
                'message' => 'No active bet found.'
            ], 200);
        }
    
        // ================= AUTO LOSS TIME CHECK =================
        // ⏱ expiry time (10 sec — change if needed)
        $betTime = \Carbon\Carbon::parse($bet->created_at);
    
        if (\Carbon\Carbon::now()->diffInSeconds($betTime) > 10) {
    
            DB::table('chicken_bets')
                ->where('id', $bet->id)
                ->update([
                    'status' => 2, // LOSS
                    'cashout_status' => 0,
                    'win_amount' => 0,
                    'updated_at' => now()
                ]);
    
            return response()->json([
                'status' => false,
                'message' => 'Bet expired. Loss applied.'
            ], 200);
        }
        // ========================================================
    
        // 🔒 Double cashout protection
        if ($bet->cashout_status == 1 || $bet->status != 0) {
            return response()->json([
                'status' => false,
                'message' => 'Bet already settled.'
            ], 200);
        }
    
        $multipliedAmount = $bet->amount * $cashout_value;
    
        // 🔹 Check user
        $user = DB::table('users')->where('id', $request->userid)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 200);
        }
    
        // ================= CASHOUT PROCESS =================
        DB::beginTransaction();
        try {
    
            DB::table('chicken_bets')
                ->where('id', $bet->id)
                ->update([
                    'win_amount' => $multipliedAmount,
                    'multiplier' => $cashout_value,
                    'cashout_status' => 1,
                    'status' => 1, // WIN
                    'updated_at' => now()
                ]);
    
            DB::table('users')
                ->where('id', $request->userid)
                ->increment('wallet', $multipliedAmount);
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Cashout processed successfully.',
                'win_amount' => $multipliedAmount
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong during cashout.',
                'error' => $e->getMessage()
            ], 200);
        }
    }


    // public function cashout(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'multiplier_id' => 'nullable',
    //         'game_id' => 'required',
    //         'userid' => 'required',
    //     ]);
    
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()->first()
    //         ], 200);
    //     }
    
    //     $cashout_info = DB::table('multiplier')
    //         ->where('multiplier', $request->multiplier_id)
    //         ->first();
    
    //     if (!$cashout_info) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Multiplier not found.'
    //         ], 200);
    //     }
    
    //     $cashout_value = $cashout_info->multiplier;
    
    //     // Get latest bet for the user in this game
    //     $bet = DB::table('chicken_bets')
    //         ->where('game_id', $request->game_id)
    //         ->where('user_id', $request->userid)
    //         ->orderBy('id', 'desc')
    //         ->first();
    
    //     if (!$bet) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Bet not found for this user and game.'
    //         ], 200);
    //     }
    
    //     // Check if already cashed out
    //     $alreadyCashedOut = false;
    
    //     if ($bet && $bet->multiplier == $cashout_value && $bet->cashout_status == 1) {
    //         $alreadyCashedOut = true;
    //     }
    
    //     if ($alreadyCashedOut) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Cashout already done on this multiplier.'
    //         ], 200);
    //     }
    
    //     $multipliedAmount = $bet->amount * $cashout_value;
    
    //     // Check user
    //     $user = DB::table('users')->where('id', $request->userid)->first();
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not found.'
    //         ], 200);
    //     }
    
    //     // // Optional: Check if user has enough balance
    //     // if ($user->wallet < $bet->amount) {
    //     //     return response()->json([
    //     //         'status' => false,
    //     //         'message' => 'Insufficient wallet balance.'
    //     //     ], 200);
    //     // }
    
    //     // Do the cashout process
    //     DB::beginTransaction();
    //     try {
    //         DB::table('chicken_bets')
    //             ->where('id', $bet->id)
    //             ->update([
    //                 'win_amount' => $multipliedAmount,
    //                 'multiplier' => $cashout_value,
    //                 'cashout_status' => 1,
    //                 'status' => 1,
    //                 'updated_at' => now()
    //             ]);
    
    //         DB::table('users')
    //             ->where('id', $request->userid)
    //             ->increment('wallet', $multipliedAmount);
    
    //         DB::commit();
    
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Cashout processed successfully.',
    //             'win_amount' => $multipliedAmount
    //         ]);
    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Something went wrong during cashout.',
    //                 'error' => $e->getMessage()
    //             ], 200);
    //         }
    // }

    public function bet_values()
    {
        $betValues = DB::table('bet_values')
                        ->where('status', 1)
                        ->select('id','value')
                        ->orderBy('value', 'ASC')
                        ->get();

        return response()->json([
            'message'=> 'data fetch success',
            'status' => 200,
            'data' => $betValues
        ]);
    }
    
    public function getGameRules()
    {
    $rules = DB::table('game_rules')->get();

    $formatted = [];

    foreach ($rules as $rule) {
        $formatted[$rule->name] = $rule->value;
    }

    return response()->json([
        'success'=>true,
        'data' => $formatted
    ]);
}

    public function multiplier(Request $request)
    {
        $typeFilter = $request->input('type'); // optional type filter
        $userId     = $request->input('user_id'); // optional user id
    
        $query = DB::table('multiplier')
            ->whereIn('type', [1, 2, 3, 4]);
    
        if (!empty($typeFilter)) {
            $query->where('type', $typeFilter);
        }
    
        $records = $query->get();
        $grouped = [];
    
        // Step 2: Group multipliers by type
        foreach ($records as $record) {
            $type = $record->type;
            $multipliers = array_map('floatval', explode(';', $record->multiplier));
            sort($multipliers);
    
            if (!isset($grouped[$type])) {
                $grouped[$type] = [
                    'id' => $record->id,
                    'type' => $type,
                    'frequency' => $record->frequency,
                    'roast_multiplier' => null,
                    'multiplier' => $multipliers
                ];
            } else {
                $grouped[$type]['multiplier'] = array_merge($grouped[$type]['multiplier'], $multipliers);
                $grouped[$type]['multiplier'] = array_map('floatval', $grouped[$type]['multiplier']);
                sort($grouped[$type]['multiplier']);
            }
        }
    
        // Step 3: Get roast multipliers set by admin
        $roastControls = DB::table('roast_control')
            ->whereIn('types', array_keys($grouped))
            ->pluck('roast_multiplier', 'types');
    
        // Step 4: Profit/Loss check agar user_id diya hai
        $isProfit = false;
        if (!empty($userId)) {
            $bets = DB::table('bets')
                ->where('userid', $userId)
                ->selectRaw('COALESCE(SUM(win_amount),0) as total_win, COALESCE(SUM(amount),0) as total_bet')
                ->first();
    
            $profitLoss = $bets->total_win - $bets->total_bet;
    
            if ($profitLoss > 0) {
                // user profit me hai
                $isProfit = true;
            }
        }
    
        // Step 5: Apply roast_multiplier logic
        foreach ($grouped as $type => &$group) {
            $multipliers = $group['multiplier'];
    
            if ($isProfit) {
                // Profit case → first (smallest) value fix karo
                $group['roast_multiplier'] = min($multipliers);
                continue;
            }
    
            if (isset($roastControls[$type]) && $roastControls[$type] > 0) {
                $adminValue = (float) $roastControls[$type];
    
                $eligibleValues = array_filter($multipliers, fn($v) => $v <= $adminValue);
                $eligibleValues = array_values($eligibleValues);
    
                if (!empty($eligibleValues)) {
                    $group['roast_multiplier'] = $eligibleValues[array_rand($eligibleValues)];
                } else {
                    $group['roast_multiplier'] = min($multipliers);
                }
            } else {
                $firstTwo = array_slice($multipliers, 0, 2);
                $group['roast_multiplier'] = $firstTwo[array_rand($firstTwo)];
            }
        }
    
        // Step 6: Prepare final response
        $finalData = array_values($grouped);
    
        return response()->json([
            'success' => count($finalData) > 0,
            'data' => $finalData
        ]);
}


}
