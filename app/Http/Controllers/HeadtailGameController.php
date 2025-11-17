<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use DB;
use Carbon\Carbon;

class HeadtailGameController extends Controller
{


public function ht_create($gameid)
{
    // 🔹 Get bets for the current game ID
    $bets = DB::table('betlogs')
        ->select('betlogs.*', 'game_settings.winning_percentage AS parsantage', 'game_settings.id AS id')
        ->leftJoin('game_settings', 'betlogs.game_id', '=', 'game_settings.id')
        ->where('betlogs.game_id', $gameid)
        ->orderByDesc('betlogs.id')
        ->limit(10)
        ->get();

    // 🔹 Get latest period number
    $current_game_no = optional($bets->first())->games_no;

    // 🔽 Total Profit Summary
    $today = Carbon::today();

    // All-time profit
    $total = DB::table('bets')
        ->where('game_id', 14)
        ->selectRaw('SUM(amount) as total_amount, SUM(win_amount) as total_win_amount')
        ->first();

    $total_admin_profit = $total->total_amount - $total->total_win_amount;
    $total_user_profit = $total->total_win_amount;

    // Today's profit
    $todayData = DB::table('bets')
        ->whereDate('created_at', $today)
        ->where('game_id', 14)
        ->selectRaw('SUM(amount) as today_amount, SUM(win_amount) as today_win_amount')
        ->first();

    $today_admin_profit = $todayData->today_amount - $todayData->today_win_amount;
    $today_user_profit = $todayData->today_win_amount;

    // ✅ Latest period number
    $period_no = DB::table('betlogs')
        ->where('game_id', 14)
        ->orderBy('id', 'desc')
        ->value('games_no');

    // ✅ Total Users Playing in current period
    $total_users_playing = DB::table('bets')
        ->where('games_no', $period_no)
        ->distinct('userid')
        ->count('userid');
        
        
        // // 🔹 Get current period number
$currentPeriod = DB::table('bets')
    ->where('game_id', $gameid)
    ->orderByDesc('id')
    ->value('games_no');

// 🔹 Bet Amount Summary for this period
$betSummary = DB::table('bets')
    ->select('number', DB::raw('SUM(amount) as total_amount'))
    ->where('game_id', $gameid)
    ->where('games_no', $currentPeriod) // 👈 only current period
    ->groupBy('number')
    ->pluck('total_amount', 'number')
    ->toArray();

// 🔹 Win Amount Summary for this period
$winSummary = DB::table('bets')
    ->select('number', DB::raw('SUM(win_amount) as total_win_amount'))
    ->where('game_id', $gameid)
    ->where('games_no', $currentPeriod) // 👈 only current period
    ->groupBy('number')
    ->pluck('total_win_amount', 'number')
    ->toArray();



        

    // ✅ Future Predictions (fixed: no duplicates, proper pending logic)
  $futurePredictions = DB::table('admin_winner_results as fpr')
    ->select(
        'fpr.id',
        'fpr.gamesno',
        'fpr.number as predicted_number',
        DB::raw('IFNULL(fr.number, "pending") as result_number'),
        'fpr.created_at',
        'fpr.updated_at'
    )
    ->leftJoin(DB::raw('(
        SELECT games_no, game_id, MAX(number) as number
        FROM bet_results
        WHERE game_id = 14
        GROUP BY games_no, game_id
    ) as fr'), function($join) {
        $join->on('fr.games_no', '=', 'fpr.gamesno')
             ->where('fr.game_id', '=', 14);
    })
    ->where('fpr.gameId', 14)
    ->orderByDesc('fpr.id')
    ->paginate(10);


    // ✅ User Bets Table
    $userBets = DB::table('bets')
        ->orderBy('id', 'desc')
        ->paginate(10);

    return view('head_tail.index', compact(
        'bets',
        'gameid',
        'total_admin_profit',
        'total_user_profit',
        'today_admin_profit',
        'today_user_profit',
        'futurePredictions',
        'userBets',
        'total_users_playing',
        'betSummary',
    'winSummary'
    ));
}
    public function ht_fetchData($gameid)
    {
        $bets = DB::select("SELECT betlogs.*,game_settings.winning_percentage AS parsantage ,game_settings.id AS id FROM `betlogs` LEFT JOIN game_settings ON betlogs.game_id=game_settings.id where betlogs.game_id=$gameid Limit 10;");

        return response()->json(['bets' => $bets, 'gameid' => $gameid]);
    }
	
	//future result store//
 public function ht_future_result_store(Request $request)
{
    $exists = DB::table('admin_winner_results')
        ->where('gamesno', $request->game_no)
        ->where('gameId', 14)
        ->exists();

    if ($exists) {
        return redirect()->back()->with('error', 'This period already has a prediction.');
    }

    DB::table('admin_winner_results')->insert([
        'gamesno'     => $request->game_no,
        'gameId'      => 14,
        'number'      => $request->number,
        'status'      => 1,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return redirect()->back()->with('success', 'Future result scheduled successfully.');
}

// 	public function ht_store(Request $request)
// 	{

// // 	$datetime=now();
// 	  //$gamesno=$request->gamesno+1;
//       $gameid=$request->game_id;
// 		 $gamesno=$request->game_no;
//          $number=$request->number;
	
		 
//         DB::insert("INSERT INTO `admin_winner_results`( `gamesno`, `gameId`, `number`, `status`) VALUES ('$gamesno','$gameid','$number','1')");
         
        
//              return redirect()->back(); 
// 	}
  
  public function ht_store(Request $request)
{
    $gameid = $request->game_id;
    $gamesno = $request->game_no;
    $number = $request->number;

    // ✅ Check if already inserted
    $exists = DB::table('admin_winner_results')
        ->where('gamesno', $gamesno)
        ->where('gameId', $gameid)
        ->exists();

    if ($exists) {
        return redirect()->back()->with('error', 'This result already exists for the given period and game.');
    }

    // ✅ Safe to insert
    DB::table('admin_winner_results')->insert([
        'gamesno' => $gamesno,
        'gameId'  => $gameid,
        'number'  => $number,
        'status'  => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Result submitted successfully.');
}



public function ht_update(Request $request)
      {
	   
	   $gamid=$request->id;
	
        $parsantage=$request->parsantage;
               $data= DB::select("UPDATE `game_settings` SET `winning_percentage` = '$parsantage' WHERE `id` ='$gamid'");
	         
         
             return redirect()->back();
          
      }
   
      
	public function ht_scheduleFutureResult(Request $request)
{
		//dd($request->all());
		
    $request->validate([
        'gameid'      => 'required|integer',
        'period_num'  => 'required',
        'card_number' => 'required'
    ]);

    $check = DB::table('admin_winner_results')
                ->where('gameid', $request->gameid)
                ->where('gamesno', $request->period_num)
                ->first();

    if ($check) {
        return redirect()->back()->with('error', 'Result already scheduled for this period!');
    }

    $insert = DB::table('admin_winner_results')->insert([
        'gameid'   => $request->gameid,
        'gamesno'  => $request->period_num,
        'number'   => $request->card_number,
        'created_at' => now()
    ]);

    if ($insert) {
        return redirect()->back()->with('success', 'Future result scheduled successfully.');
    } else {
        return redirect()->back()->with('error', 'Something went wrong!');
    }
}


}
