<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class jiliApiController extends Controller
{
    public function getSelectedBrands()
    {
    $url = "https://softapi.gt.tc/";

    $html = @file_get_contents($url);
    if (!$html) {
        return response()->json([
            'status' => 400,
            'message' => 'Unable to fetch brands page',
            'data' => []
        ]);
    }

    // Static game names with static IDs
    $desired = [
        ['id' => 112, 'name' => "InOut"],
        ['id' => 49, 'name' => "JILI"],
        ['id' => 52, 'name' => "CQ9"],
        ['id' => 50, 'name' => "JDB"],
        ['id' => 123, 'name' => "PgsGaming"],
        ['id' => 59, 'name' => "EVOLUTION"],
        ['id' => 57, 'name' => "SPRIBE"],
        ['id' => 107, 'name' => "Smartsoft"],
        ['id' => 104, 'name' => "Mini"],
        ['id' => 89, 'name' => "SA GAMING"],
        ['id' => 82, 'name' => "Astar"],
        ['id' => 72, 'name' => "Playtech"],
        ['id' => 46, 'name' => "SABA SPORTS"],
        ['id' => 100, 'name' => "Turbogames Asia"],
        ['id' => 78, 'name' => "Ezugi"],
        ['id' => 51, 'name' => "TADAGaming"],
        ['id' => 53, 'name' => "PragmaticPlay-EU"],
        ['id' => 65, 'name' => "Bgaming"],
        ['id' => 80, 'name' => "T1"],
        ['id' => 88, 'name' => "Sexy"],
    ];

    $normalize = function ($s) {
        return strtoupper(preg_replace('/\s+/', '', $s));
    };

    libxml_use_internal_errors(true);
    $dom = new \DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $links = $dom->getElementsByTagName('a');
    $pageBrands = [];
    foreach ($links as $link) {
        $text = trim($link->nodeValue);
        if ($text !== '') {
            $pageBrands[$normalize($text)] = $text;
        }
    }

    $result = [];
    foreach ($desired as $item) {
        $norm = $normalize($item['name']);
        $result[] = [
            'id' => $item['id'],
            'name' => isset($pageBrands[$norm]) ? $pageBrands[$norm] : $item['name'],
        ];
    }

    return response()->json([
        'status' => 200,
        'message' => 'Filtered brands with static IDs',
        'data' => $result
    ]);
}
    
    public function getGames($brand_id)
    {
        // Direct external URL
        $url = "https://softapi.gt.tc/brands.php?brand_id=" . urlencode($brand_id);
    
        // Just return the direct URL for the frontend to fetch
        return response()->json([
            'status' => 200,
            'message' => 'Fetch games from this URL',
            'brand' => $brand_id,
            'fetchUrl' => $url
        ]);
    }
    
    public function openGame(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'amount'  => 'required',
            'game_id' => 'required',
            'game_name' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
        }
    
        $userid  = $request->user_id;
        $amount  = $request->amount;
        $game_id = $request->game_id;
        $game_name = $request->game_name;
    
        // Fetch user mobile
        $mobile_no = DB::table('users')->where('id', $userid)->value('mobile');
        if (!$mobile_no) {
            return response()->json(['status' => 404, 'message' => 'User not found']);
        }
    
        $TOKEN  = '9cf215d00828f1f2ae6663d3e0d3ca5e';
        $SECRET = 'a1e5c1441441d4ea763b0de4be8dfebe';
        //$SERVER_URL = 'https://softapi1.online/client/'; 
        $SERVER_URL = 'https://igamingapis.live/api/v1'; 
        $RETURN_URL = 'https://winbhai.in';
        $CALLBACK_URL = 'https://winbhai.in/api.php';
    
        // Payload dynamically from request
        $PAYLOAD = [
            'user_id'   => $mobile_no,
            'balance'   => (int)$amount,
            'game_uid'  => $game_id,
            'token'     => $TOKEN,
            'timestamp' => round(microtime(true) * 1000),
            'return'    => $RETURN_URL,
            'callback'  => $CALLBACK_URL
        ];
    
        // Encryption function
        $ENCRYPT_PAYLOAD_ECB = function(array $DATA, string $KEY): string {
            $JSON = json_encode($DATA);
            $ENC  = openssl_encrypt($JSON, 'AES-256-ECB', $KEY, OPENSSL_RAW_DATA);
            return base64_encode($ENC);
        };
    
        $ENCRYPTED = $ENCRYPT_PAYLOAD_ECB($PAYLOAD, $SECRET);
        $URL = $SERVER_URL . '?payload=' . urlencode($ENCRYPTED) . '&token=' . urlencode($TOKEN);
        
         // 1️⃣ STORE GAME SESSION FOR CALLBACK
        DB::table('active_game_sessions')->insert([
            'game_uid'    => $game_id,
            'game_id'     => $game_id,
            'game_name'   => $game_name,
            'user_mobile' => $mobile_no,
            'created_at'  => now()
        ]);
    
        // Hit API
        $response = file_get_contents($URL);
    
        return response()->json([
            'status'   => 200,
            'message'  => 'Game Launched',
            'gameid'   => $game_id,
            'game_name'=> $game_name,
            'userPhone'=> $mobile_no,
            'gameUrl'  => $URL,
            'apiResponse' => json_decode($response, true)
        ]);
    }

    public function openGame_old(Request $request)
    {
       $TOKEN  = '9cf215d00828f1f2ae6663d3e0d3ca5e'; // Enter Your Token Here 
        $SECRET = 'a1e5c1441441d4ea763b0de4be8dfebe'; // Your Key Here From Panel 
        
        $SERVER_URL = 'https://api.igamingapi.online/client/';
        
        $RETURN_URL = 'https://google.com/return';
        $CALLBACK_URL = 'https://darkslategray-seal-181851.hostingersite.com/callback.php';
        
        // Data to send
        $PAYLOAD = [
            'user_id' => "23211",
            'balance' => '10',
            'game_uid' => '473',
            'token' => $TOKEN,
            'timestamp' => round(microtime(true) * 1000),
            'return' => $RETURN_URL,
            'callback' => $CALLBACK_URL
        ];
        
        // Encryption function using AES-256-ECB
        function ENCRYPT_PAYLOAD_ECB(array $DATA, string $KEY): string {
            $JSON = json_encode($DATA);
            $ENC  = openssl_encrypt($JSON, 'AES-256-ECB', $KEY, OPENSSL_RAW_DATA);
            return base64_encode($ENC);
        }
        
        // Encrypt payload
        $ENCRYPTED = ENCRYPT_PAYLOAD_ECB($PAYLOAD, $SECRET);
        
        // Prepare full URL with payload and token
        $URL = $SERVER_URL . '?payload=' . urlencode($ENCRYPTED) . '&token=' . urlencode($TOKEN);
        
        // Send request to API
         echo  $response = file_get_contents($URL);
        
        // Show API response
        print_r(json_decode($response, true));
    }
    
    // public function getGames($brand_id)
    // {
    //     $url = "https://softapi.gt.tc/brands.php?brand_id=" . urlencode($brand_id);
    
    //     $curl = curl_init();
    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_TIMEOUT => 15,
    //         CURLOPT_SSL_VERIFYPEER => false, // Optional, if SSL errors
    //     ]);
    
    //     $response = curl_exec($curl);
    //     curl_close($curl);
    // //dd($response);
    //     if ($response === false) {
    //         return response()->json([
    //             'status' => 500,
    //             'message' => 'API request failed',
    //             'data' => []
    //         ]);
    //     }
    
    //     $get_games = json_decode($response, true);
    
    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         return response()->json([
    //             'status' => 400,
    //             'message' => 'Invalid JSON response',
    //             'data' => []
    //         ]);
    //     }
    
    //     $games = [];
    //     foreach ($get_games as $game) {
    //         $games[] = [
    //             'gameNameEn' => $game['gameNameEn'] ?? null,
    //             'gameId'     => $game['gameCode'] ?? null,
    //             'imgUrl'     => $game['imgUrl'] ?? null,
    //             'category'   => $game['category'] ?? null
    //         ];
    //     }
    
    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Success',
    //         'brand' => $brand_id,
    //         'data' => $games
    //     ]);
    // }
    
////anur sir fun
    public function openGame_anu(Request $request)
	{
	
         $validator = Validator::make($request->all(), [
					'user_id' => 'required',
					'amount' => 'required',
			 		'game_id' => 'required'
			 
				]);
				$validator->stopOnFirstFailure();
				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				}  
		$userid=$request->user_id;
		$amount=$request->amount;
		$game_id=$request->game_id;
		
		$mobile_no=DB::select("SELECT `mobile` FROM `users` WHERE `id`=$userid");
		
		$mobile=$mobile_no[0]->mobile;
		//$account_type=$mobile_no[0]->account_type;
		
// 		 if ($account_type != 0) {
// 			return response()->json(['status' => 400, 'message' => 'Game is not available for demo accounts.'], 200);
// 		}

		
		
		$secret_id = "9cf215d00828f1f2ae6663d3e0d3ca5e";
		$secret_key = "a1e5c1441441d4ea763b0de4be8dfebe";

		$payload = [
		  "user_id" => $mobile,
		  "balance" => (int)$amount,
		  "game_uid" => "$game_id",
		  "token" => "9cf215d00828f1f2ae6663d3e0d3ca5e",
		  "timestamp" => round(microtime(true) * 1000)
		];

		$payload_json = json_encode($payload);
		$encrypted = openssl_encrypt($payload_json, "AES-256-ECB", $secret_key, OPENSSL_RAW_DATA);
		$encoded = base64_encode($encrypted);

		$url = "https://api.igamingapi.online/client/?payload=" . urlencode($encoded) . "&secret_id=" . $secret_id;
		$resp=['gameid'=>$game_id,'userPhone'=>$mobile,'gameUrl'=>$url];
		
		echo json_encode($resp);

				

		
	}


//   public function openGame_ollllllddd(Request $request)
//     {
       
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required',
//         'amount'  => 'required',
//         'game_id' => 'required'
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
//     }

//     $userid  = $request->user_id;
//     $amount  = $request->amount;
//      $game_id = $request->game_id;
//     // Fetch user mobile
//     $mobile_no = DB::table('users')->where('id', $userid)->value('mobile');
//     if (!$mobile_no) {
//         return response()->json(['status' => 404, 'message' => 'User not found']);
//     }

//     $TOKEN  = '9cf215d00828f1f2ae6663d3e0d3ca5e';
//     $SECRET = 'a1e5c1441441d4ea763b0de4be8dfebe';
//     $SERVER_URL = 'https://api.softapi2.shop/client/';
//     $RETURN_URL = 'https://winbhai.in';
//     $CALLBACK_URL = 'https://winbhai.in/api.php';

//     // Payload dynamically from request
//     $PAYLOAD = [
//         'user_id'   => $mobile_no,
//         'balance'   => (int)$amount,
//         'game_uid'  => $game_id,
//         'token'     => $TOKEN,
//         'timestamp' => round(microtime(true) * 1000),
//         'return'    => $RETURN_URL,
//         'callback'  => $CALLBACK_URL
//     ];

//     // Encryption function
//     $ENCRYPT_PAYLOAD_ECB = function(array $DATA, string $KEY): string {
//         $JSON = json_encode($DATA);
//         $ENC  = openssl_encrypt($JSON, 'AES-256-ECB', $KEY, OPENSSL_RAW_DATA);
//         return base64_encode($ENC);
//     };
// echo $ENCRYPT_PAYLOAD_ECB;die;
//     $ENCRYPTED = $ENCRYPT_PAYLOAD_ECB($PAYLOAD, $SECRET);
//     $URL = $SERVER_URL . '?payload=' . urlencode($ENCRYPTED) . '&token=' . urlencode($TOKEN);
// // echo $URL;die;
//     // Hit API
//     $response = file_get_contents($URL);

//     return response()->json([
//         'status'   => 200,
//         'message'  => 'Game Launched',
//         'gameid'   => $game_id,
//         'userPhone'=> $mobile_no,
//         'gameUrl'  => $URL,
//         'apiResponse' => json_decode($response, true)
//     ]);
// }


public function userGameHistory($user_id)
{
    $history = DB::table('game_history')
        ->where('user_id', $user_id)
        ->orderBy('id', 'desc')
        ->get();

    return response()->json([
        'status' => 200,
        'data' => $history
    ]);
}


public function filterGameHistory(Request $request)
{
    $request->validate([
        'user_id' => 'required'
    ]);

    $history = DB::table('game_history')
        ->where('user_id', $request->user_id)
        ->when($request->game_id, function($q) use ($request){
            return $q->where('game_id', $request->game_id);
        })
        ->orderBy('id', 'desc')
        ->get();

    return response()->json([
        'status' => 200,
        'data' => $history
    ]);
}






}