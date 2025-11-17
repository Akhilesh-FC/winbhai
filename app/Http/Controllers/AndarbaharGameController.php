<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use DB;
use Carbon\Carbon;

class AndarbaharGameController extends Controller
{
//   public function colour_prediction_create($gameid)
//     {
//         $bets = DB::select("SELECT betlogs.*,game_settings.winning_percentage AS parsantage ,game_settings.id AS id FROM `betlogs` LEFT JOIN game_settings ON betlogs.game_id=game_settings.id where betlogs.game_id=$gameid Limit 10;");

//         return view('colour_prediction.index')->with('bets', $bets)->with('gameid', $gameid);
//     }

public function ab_create($gameid)
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
    
     // 🔽🔽🔽 Start: Profit Summary Logic 🔽🔽🔽
    $today = \Carbon\Carbon::today();
     // Total profit (all time)
   $total = DB::table('bets')
    ->where('game_id', 13)
    ->selectRaw('SUM(amount) as total_amount, SUM(win_amount) as total_win_amount')
    ->first();


    $total_admin_profit = $total->total_amount - $total->total_win_amount;
    $total_user_profit = $total->total_win_amount;  // Just sum of win_amount

    // Today's profit
  $todayData = DB::table('bets')
    ->whereDate('created_at', $today)
    ->where('game_id', 13)
    ->selectRaw('SUM(amount) as today_amount, SUM(win_amount) as today_win_amount')
    ->first();


    $today_admin_profit = $todayData->today_amount - $todayData->today_win_amount;
    $today_user_profit = $todayData->today_win_amount;  // Just sum of today's win_amount

  // 🔹 Get latest period number
    $period_no = DB::table('betlogs')
               ->where('game_id', 13)
               ->orderBy('id', 'desc')
               ->value('games_no');


 // ✅ Total Users Playing in current period
   $total_users_playing = DB::table('bets')
    ->where('games_no', $period_no)
    ->distinct('userid')
    ->count('userid');
// dd($total_users_playing,$period_no);\



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





    // 🔹 Future Predictions
        
        $futurePredictions = DB::table('admin_winner_results as fpr')
    ->select(
        'fpr.id',
        'fpr.gamesno',
        'fpr.number as predicted_number',
        DB::raw('IFNULL(fr.number, "pending") as result_number'),
        'fpr.created_at',
        'fpr.updated_at'
    )
    ->leftJoin('bet_results as fr', 'fr.games_no', '=', 'fpr.gamesno')  // Fixed join
    ->where('fpr.gameId', 13) // Added filter
    ->orderByDesc('fpr.id')
    ->paginate(10);


        
        $userBets = DB::table('bets')
        ->orderBy('id', 'desc')
        ->paginate(10); // Pagination here

  

        return view('andarbahar.index', compact(
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

    public function ab_fetchData($gameid)
    {
        $bets = DB::select("SELECT betlogs.*,game_settings.winning_percentage AS parsantage ,game_settings.id AS id FROM `betlogs` LEFT JOIN game_settings ON betlogs.game_id=game_settings.id where betlogs.game_id=$gameid Limit 10;");

        return response()->json(['bets' => $bets, 'gameid' => $gameid]);
    }
	
	//future result store//
 public function ab_future_result_store(Request $request)
    {
         //dd($request->all());
       

        DB::table('admin_winner_results')->insert([
            'gamesno' => $request->game_no,
            'gameId'  =>13,
            'number'   => $request->number,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Future result scheduled successfully.');
    }
	public function ab_store(Request $request)
	{

// 	$datetime=now();
	  //$gamesno=$request->gamesno+1;
      $gameid=$request->game_id;
		 $gamesno=$request->game_no;
         $number=$request->number;
	
		 
        DB::insert("INSERT INTO `admin_winner_results`( `gamesno`, `gameId`, `number`, `status`) VALUES ('$gamesno','$gameid','$number','1')");
         
        
             return redirect()->back(); 
	}
  

// public function update(Request $request)
//       {
// 	   //dd($request);

// 	   $gamid=$request->id;
	
//         $parsantage=$request->parsantage;
//               $data= DB::select("UPDATE `game_settings` SET `winning_percentage` = '$parsantage' WHERE `id` ='$gamid'");
	         
         
//              return redirect()->back();
          
//       }

public function ab_update(Request $request)
      {
	   
	   $gamid=$request->id;
	
        $parsantage=$request->parsantage;
               $data= DB::select("UPDATE `game_settings` SET `winning_percentage` = '$parsantage' WHERE `id` ='$gamid'");
	         
         
             return redirect()->back();
          
      }
   
      
	public function ab_scheduleFutureResult(Request $request)
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
