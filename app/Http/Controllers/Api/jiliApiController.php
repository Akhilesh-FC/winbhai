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
use Illuminate\Support\Facades\Cache;



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
    
    
//   public function openGame(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required',
//         'amount'  => 'required',
//         'game_id' => 'required',
//         'game_name' => 'required'
//     ]);

//     if ($validator->fails()) {
//         return response("<h3 style='color:red;'>Error: ".$validator->errors()->first()."</h3>");
//     }

//     $userid  = $request->user_id;
//     $game_id = (string)$request->game_id;
//     $game_name = $request->game_name;

//     $user = DB::table('users')->where('id', $userid)->first();
//     if (!$user) {
//         return response("<h3 style='color:red;'>Error: User not found</h3>");
//     }

//     $mobile_no = trim($user->mobile);
//     $wallet_balance = (int)$user->wallet;

//     // API Keys
//     $TOKEN  = "9cf215d00828f1f2ae6663d3e0d3ca5e";
//     $SECRET = "a1e5c1441441d4ea763b0de4be8dfebe";

//     $SERVER_URL   = "https://igamingapis.live/api/v1";
//     $RETURN_URL   = "https://winbhai.in";
//     $CALLBACK_URL = "https://winbhai.in/api.php";

//     // ✔ Correct Payload
//     $PAYLOAD = [
//         "user_id"        => (string)$userid,    
//         "suffix"         => $mobile_no,          
//         "balance"        => $wallet_balance,
//         "game_uid"       => $game_id,
//         "token"          => $TOKEN,
//         "timestamp"      => round(microtime(true) * 1000),
//         "return"         => $RETURN_URL,
//         "callback"       => $CALLBACK_URL,
//         "currency_code"  => "INR",
//         "language"       => "en"
//     ];

//     // ✔ Encryption
//     $encrypt = function(array $data, string $key): string {
//         $key = substr($key, 0, 32);
//         if (strlen($key) < 32) {
//             $key = str_pad($key, 32, "\0");
//         }
//         $json = json_encode($data, JSON_UNESCAPED_UNICODE);
//         $enc  = openssl_encrypt($json, "AES-256-ECB", $key, OPENSSL_RAW_DATA);
//         return base64_encode($enc);
//     };

//     $ENCRYPTED = $encrypt($PAYLOAD, $SECRET);

//     // ✔ POST Request
//     $body = json_encode(["payload" => $ENCRYPTED, "token" => $TOKEN]);

//     $ch = curl_init($SERVER_URL);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

//     $response = curl_exec($ch);
//     curl_close($ch);

//     $apiResponse = json_decode($response, true);

//     $realUrl = $apiResponse["data"]["url"] ?? null;

//     // ❤️ If game URL missing → show pretty error
//     if (!$realUrl) {
//         $error = $apiResponse["msg"] ?? "Unable to launch game. Please try again.";
//         return response("
//             <h2 style='color:red; text-align:center;'>❌ Game Launch Failed</h2>
//             <p style='text-align:center;'>Reason: <b>$error</b></p>
//         ");
//     }

//     // ❤️ Working iframe + fallback script
//     $html = "
//         <html>
//         <head>
//             <title>Game Loaded</title>
//             <style>
//                 body { margin:0; padding:0; background:#000; }
//                 #errorBox {
//                     display:none;
//                     color:#fff;
//                     background:#d9534f;
//                     padding:20px;
//                     text-align:center;
//                     font-size:20px;
//                 }
//             </style>
//         </head>
//         <body>

//             <div id='errorBox'>
//                 ❌ Unable to load game.  
//                 <br>Authentication error or session expired.
//                 <br>Please try again.
//             </div>

//             <iframe id='gameFrame'
//                 src='$realUrl'
//                 style='width:100%; height:100vh; border:none; display:block;'
//                 allowfullscreen>
//             </iframe>

//             <script>
//                 var frame = document.getElementById('gameFrame');

//                 // If iframe fails to load within 5 seconds → show error
//                 setTimeout(function() {
//                     if (!frame.contentWindow || frame.contentWindow.length === 0) {
//                         frame.style.display = 'none';
//                         document.getElementById('errorBox').style.display = 'block';
//                     }
//                 }, 5000);
//             </script>

//         </body>
//         </html>
//     ";

//     return response($html);
// }


    
    // ------------akhilesh sir------------------ working
//   public function openGame(Request $request)
// {
//     // 🔹 Validate request
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required',
//         'amount'  => 'required',
//         'game_id' => 'required',
//         'game_name' => 'required'
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
//     }

//     $userid  = $request->user_id;
//     $amount  = $request->amount;
//     $game_id = (string)$request->game_id;  // IMPORTANT: provider requires string
//     $game_name = $request->game_name;

//     // 🔹 Fetch user
//     $user = DB::table('users')->where('id', $userid)->first();
//     if (!$user) {
//         return response()->json(['status' => 404, 'message' => 'User not found']);
//     }

//     $mobile_no = trim($user->mobile);
//     $wallet_balance = (int)$user->wallet;

//     // 🔹 API Credentials
//     $TOKEN  = "9cf215d00828f1f2ae6663d3e0d3ca5e";
//     $SECRET = "a1e5c1441441d4ea763b0de4be8dfebe";

//     $SERVER_URL   = "https://igamingapis.live/api/v1";
//     $RETURN_URL   = "https://winbhai.in";
//     $CALLBACK_URL = "https://winbhai.in/api.php";
//     $CURRENCY     = "INR";

//   $PAYLOAD = [
//     "user_id"        => (string)$userid,     // FIXED
//     "suffix"         => $mobile_no,          // mobile goes here if needed
//     "balance"        => $wallet_balance,
//     "game_uid"       => $game_id,
//     "token"          => $TOKEN,
//     "timestamp"      => round(microtime(true) * 1000),
//     "return"         => $RETURN_URL,
//     "callback"       => $CALLBACK_URL,
//     "currency_code"  => "INR",
//     "language"       => "en"
// ];


//     // 🔹 AES Encryption
//     $encrypt = function(array $data, string $key): string {
//         $key = substr($key, 0, 32);
//         if (strlen($key) < 32) {
//             $key = str_pad($key, 32, "\0");
//         }
//         $json = json_encode($data, JSON_UNESCAPED_UNICODE);
//         $encrypted = openssl_encrypt($json, "AES-256-ECB", $key, OPENSSL_RAW_DATA);
//         return base64_encode($encrypted);
//     };

//     $ENCRYPTED = $encrypt($PAYLOAD, $SECRET);

//     // 🔹 POST Request (THIS FIXES EV.5)
//     $body = json_encode([
//         "payload" => $ENCRYPTED,
//         "token"   => $TOKEN
//     ]);

//     $ch = curl_init($SERVER_URL);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

//     $response = curl_exec($ch);
//     curl_close($ch);

//     $apiResponse = json_decode($response, true);

//     // 🔹 Extract Game URL
//      $realUrl = $apiResponse["data"]["url"] ?? null;
     
//      $newOn=base64_encode($realUrl);

//     if (!$realUrl) {
//         return response()->json([
//             "status" => 400,
//             "message" => $apiResponse["msg"] ?? "Failed to launch game",
//             "apiResponse" => $apiResponse
//         ]);
//     }

//     // 🔹 Create secure redirect token
//     $token = Str::uuid()->toString();
//     Cache::put("GAME_URL_" . $token, $realUrl, 60);

//     // 🔹 Store active session for callback
//     DB::table('active_game_sessions')->insert([
//         "game_uid"    => $game_id,
//         "game_id"     => $game_id,
//         "game_name"   => $game_name,
//         "user_mobile" => $mobile_no,
//         "created_at"  => now()
//     ]);

//     // 🔹 Final response (Same structure your frontend uses)
//     return response()->json([
//         'status'     => 200,
//         'message'    => 'Game Launched',
//         'gameid'     => $game_id,
//         'game_name'  => $game_name,
//         'userPhone'  => $mobile_no,
//         'gameUrl'    => url("/play/$token"),
//         'apiResponse'=> $apiResponse,
//         'newUrl'=> $newOn
//     ]);
// }



    // ============================================
    // STEP 1 — OPEN GAME (Frontend calls this API)
    // ============================================

    // public function openGame(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required',
    //         'game_id' => 'required',
    //         'game_name' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    //     }

    //     $token = Str::uuid()->toString();

    //     Cache::put("PENDING_GAME_" . $token, [
    //         "user_id"   => $request->user_id,
    //         "game_id"   => $request->game_id,
    //         "game_name" => $request->game_name
    //     ], 120);

    //     return response()->json([
    //         "status" => 200,
    //         "launchUrl" => url("/play/" . $token)
    //     ]);
    // }
    
    public function openGame(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required',
            'game_id'   => 'required',
            'game_name' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ]);
        }
    
        $user = DB::table('users')->where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json(['status' => 400, 'message' => 'User not found']);
        }
    
        // 🔥 ONE TIME UID
        $game_uid = uniqid('GAME_');
        $token    = Str::uuid()->toString();
    
        // ✅ STORE GAME SESSION (IMPORTANT)
        DB::table('active_game_sessions')->insert([
            'game_uid'    => $request->game_id,
            'game_id'     => $request->game_id,
            'game_name'   => $request->game_name,
            'user_mobile' => $user->mobile,
            'created_at'  => now()
        ]);
    
        Cache::put("PENDING_GAME_" . $token, [
            'user_id'  => $request->user_id,
            'game_id'  => $request->game_id,
            'game_uid' => $request->game_id,
        ], 120);
    
        return response()->json([
            'status'    => 200,
            'launchUrl' => url('/play/' . $token)
        ]);
    }




    // ====================================================
    // STEP 2 — PLAY GAME (Browser/iframe opens this URL)
    // ====================================================

    // public function launchGame($token)
    // {
    //     $data = Cache::get("PENDING_GAME_" . $token);

    //     if (!$data) {
    //         return "Session expired! Please relaunch the game.";
    //     }

    //     $providerUrl = $this->getFreshGameUrl($data['user_id'], $data['game_id']);

    //     if (!$providerUrl) {
    //         Log::error("Fresh game URL FAILED for token: " . $token);
    //         return "Unable to launch game at the moment.";
    //     }

    //     return redirect()->away($providerUrl);
    // }

    public function launchGame($token)
    {
        $data = Cache::get("PENDING_GAME_" . $token);
    
        if (!$data) {
            return "Session expired! Please relaunch the game.";
        }
    
        $providerUrl = $this->getFreshGameUrl(
            $data['user_id'],
            $data['game_uid']
        );
    
        if (!$providerUrl) {
            return "Unable to launch game.";
        }
    
        return redirect()->away($providerUrl);
    }



    // ====================================================
    // STEP 3 — GENERATE FRESH PROVIDER GAME URL
    // ====================================================

    private function getFreshGameUrl($user_id, $game_uid)
    {
        $user = DB::table('users')->where('id', $user_id)->first();

        if (!$user) return null;

        $TOKEN  = "9cf215d00828f1f2ae6663d3e0d3ca5e";
        $SECRET = "a1e5c1441441d4ea763b0de4be8dfebe";

        $payload = [
            "user_id" => (string)$user->mobile,
            "suffix"         => $user->mobile,
            "balance"        => (int)$user->wallet,
            "game_uid"       => (string)$game_uid,
            "token"          => $TOKEN,
            "timestamp"      => round(microtime(true) * 1000),
            "return"         => "https://winbhai.in",
          // "callback"       => "https://winbhai.in/api/game-callback",
            "callback"       => "https://winbhai.in/api.php",

            "currency_code"  => "INR",
            "language"       => "en"
        ];

        $key = substr($SECRET, 0, 32);

        $encrypted = base64_encode(openssl_encrypt(
            json_encode($payload),
            "AES-256-ECB",
            $key,
            OPENSSL_RAW_DATA
        ));

        $body = json_encode([
            "payload" => $encrypted,
            "token"   => $TOKEN
        ]);

        $ch = curl_init("https://igamingapis.live/api/v1");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        Log::info("Provider freshCall response: " . $response);

        $api = json_decode($response, true);

        return $api["data"]["url"] ?? null;
    }
    
//     private function getFreshGameUrl($user_id, $game_uid)
// {
//     $user = DB::table('users')->where('id', $user_id)->first();
//     if (!$user) return null;

//   $TOKEN  = "9cf215d00828f1f2ae6663d3e0d3ca5e";
//     $SECRET = "a1e5c1441441d4ea763b0de4be8dfebe";

//     $payload = [
//         "user_id"   => (string)$user->mobile,
//         "suffix"    => $user->mobile,
//         "balance"   => (int)$user->wallet,
//         "game_uid"  => $game_uid,   // 🔥 SAME UID
//         "token"     => $TOKEN,
//         "timestamp" => round(microtime(true) * 1000),
//         "callback"  => "https://winbhai.in/api.php",
//         "currency_code" => "INR",
//         "language" => "en"
//     ];

//     $key = substr($SECRET, 0, 32);

//     $encrypted = base64_encode(openssl_encrypt(
//         json_encode($payload),
//         "AES-256-ECB",
//         $key,
//         OPENSSL_RAW_DATA
//     ));

//     $body = json_encode([
//         "payload" => $encrypted,
//         "token"   => $TOKEN
//     ]);

//     $ch = curl_init("https://igamingapis.live/api/v1");
//     curl_setopt_array($ch, [
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_POST           => true,
//         CURLOPT_POSTFIELDS     => $body,
//         CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
//         CURLOPT_SSL_VERIFYPEER => false
//     ]);

//     $response = curl_exec($ch);
//     curl_close($ch);

//     $api = json_decode($response, true);
//     return $api['data']['url'] ?? null;
// }





    // ====================================================
    // STEP 4 — CALLBACK HANDLER (Provider sends debit/credit)
    // ====================================================

    public function gameCallback(Request $request)
    {
        Log::info("GAME CALLBACK RECEIVED: " . json_encode($request->all()));

        $userId = $request->user_id;
        $amount = $request->amount;
        $type   = $request->type;   // debit or credit

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            Log::error("Callback user NOT found: " . $userId);
            return response()->json(['status' => 404]);
        }

        // =============================
        //   DEBIT (Game Entry / Bet)
        // =============================
        if ($type == "debit") 
        {
            DB::table('users')->where('id', $userId)
                ->update(['wallet' => $user->wallet - $amount]);

            Log::info("WALLET DEBIT: User {$userId} -{$amount}");
        }

        // =============================
        //   CREDIT (Win Amount)
        // =============================
        if ($type == "credit") 
        {
            DB::table('users')->where('id', $userId)
                ->update(['wallet' => $user->wallet + $amount]);

            Log::info("WALLET CREDIT: User {$userId} +{$amount}");
        }

        return response()->json(['status' => 200, 'message' => 'Wallet updated']);
    }







    // -------------------vishal----------------------
    // public function openGame(Request $request)
    // {
    //     // Validate
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required',
    //         'amount'  => 'required',
    //         'game_id' => 'required',
    //         'game_name' => 'required'
    //     ]);
    
    //     if ($validator->fails()) {
    //         return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    //     }
    
    //     $userid     = $request->user_id;
    //     $amount     = $request->amount;
    //     $game_id    = (string)$request->game_id;
    //     $game_name  = $request->game_name;
    
    //     // Fetch user
    //     $user = DB::table('users')->where('id', $userid)->first();
    //     if (!$user) {
    //         return response()->json(['status' => 404, 'message' => 'User not found']);
    //     }
    
    //     $mobile_no      = trim($user->mobile);
    //     $wallet_balance = (int)$user->wallet;
    
    //     // API Credentials
    //     $TOKEN  = "9cf215d00828f1f2ae6663d3e0d3ca5e";
    //     $SECRET = "a1e5c1441441d4ea763b0de4be8dfebe";
    
    //     $SERVER_URL   = "https://igamingapis.live/api/v1";
    //     $RETURN_URL   = "https://winbhai.in";
    //     $CALLBACK_URL = "https://winbhai.in/api.php";
    
    //     // Payload
    //     $PAYLOAD = [
    //         "user_id"        => (string)$userid,
    //         "suffix"         => $mobile_no,
    //         "balance"        => $wallet_balance,
    //         "game_uid"       => $game_id,
    //         "token"          => $TOKEN,
    //         "timestamp"      => round(microtime(true) * 1000),
    //         "return"         => $RETURN_URL,
    //         "callback"       => $CALLBACK_URL,
    //         "currency_code"  => "INR",
    //         "language"       => "en"
    //     ];
    
    //     // AES Encryption
    //     $encrypt = function (array $data, string $key): string {
    //         $key = substr($key, 0, 32);
    //         if (strlen($key) < 32) {
    //             $key = str_pad($key, 32, "\0");
    //         }
    //         $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    //         $encrypted = openssl_encrypt($json, "AES-256-ECB", $key, OPENSSL_RAW_DATA);
    //         return base64_encode($encrypted);
    //     };
    
    //     $ENCRYPTED = $encrypt($PAYLOAD, $SECRET);
    
    //     $body = json_encode([
    //         "payload" => $ENCRYPTED,
    //         "token"   => $TOKEN
    //     ]);
    
    //     // ================
    //     // 🔥 FIX: CURL with Retry Logic
    //     // ================
    //     $response = null;
    //     $attempt  = 0;
    
    //     while ($attempt < 8) {
    //         $attempt++;
    
    //         $ch = curl_init($SERVER_URL);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    
    //         // Important fixes
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //         curl_setopt($ch, CURLOPT_TIMEOUT, 80);   // ⬅ Increased timeout
    //         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    //         $response = curl_exec($ch);
    
    //         if ($response !== false) {
    //             break; // success, break loop
    //         }
    
    //         Log::error("Game API Attempt #$attempt failed: " . curl_error($ch));
    //         curl_close($ch);
    //         sleep(1); // wait before retry
    //     }
    
    //     if (!$response) {
    //         return response()->json([
    //             "status" => 500,
    //             "message" => "Provider API not responding",
    //             "api_error" => curl_error($ch)
    //         ]);
    //     }
    
    //     curl_close($ch);
    
    //     $apiResponse = json_decode($response, true);
    
    //     // Extract URL
    //     $realUrl = $apiResponse["data"]["url"] ?? null;
    //     $newOn = base64_encode($realUrl);
    
    //     if (!$realUrl) {
    //         return response()->json([
    //             "status" => 400,
    //             "message" => $apiResponse["msg"] ?? "Failed to launch game",
    //             "apiResponse" => $apiResponse
    //         ]);
    //     }
    
    //     // Create secure token
    //     $token = Str::uuid()->toString();
    //     Cache::put("GAME_URL_" . $token, $realUrl, 160);
    
    //     // Save active session
    //     DB::table('active_game_sessions')->insert([
    //         "game_uid"    => $game_id,
    //         "game_id"     => $game_id,
    //         "game_name"   => $game_name,
    //         "user_mobile" => $mobile_no,
    //         "created_at"  => now()
    //     ]);
    
    //     return response()->json([
    //         'status'     => 200,
    //         'message'    => 'Game Launched1',
    //         'gameid'     => $game_id,
    //         'game_name'  => $game_name,
    //         'userPhone'  => $mobile_no,
    //         'gameUrl'    => url("/play/$token"),
    //         'apiResponse'=> $apiResponse,
    //         'newUrl'     => $newOn
    //     ]);
    // }

//   public function openGame(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required',
//         'amount'  => 'required',
//         'game_id' => 'required',
//         'game_name' => 'required'
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
//     }

//     $userid  = $request->user_id;
//     $amount  = $request->amount;
//     $game_id = $request->game_id;
//     $game_name = $request->game_name;

//     // Fetch user mobile
//     $user = DB::table('users')->where('id', $userid)->first();
//     if (!$user) {
//         return response()->json(['status' => 404, 'message' => 'User not found']);
//     }

//     $mobile_no = $user->mobile;
//     $wallet_balance = $user->wallet;

//     // === SAME KEYS AS WORKING HTML GAME LAUNCHER ===
//     $TOKEN  = "9cf215d00828f1f2ae6663d3e0d3ca5e";
//     $SECRET = "a1e5c1441441d4ea763b0de4be8dfebe";

//     $SERVER_URL   = "https://igamingapis.live/api/v1";
//     $RETURN_URL   = "https://winbhai.in";
//     $CALLBACK_URL = "https://winbhai.in/api.php";
//     $CURRENCY     = "INR";  // default as game launcher


//     // === PAYLOAD (EXACT MATCH TO HTML LAUNCHER) ===
//     $PAYLOAD = [
//         "user_id"        => (string)$mobile_no,
//         "balance"        => (float)$wallet_balance,
//         "game_uid"       => (int)$game_id,
//         "token"          => $TOKEN,
//         "timestamp"      => round(microtime(true) * 1000),
//         "return"         => $RETURN_URL,
//         "callback"       => $CALLBACK_URL,
//         "currency_code"  => $CURRENCY,
//         "language"       => "en"
//     ];

//     // === ENCRYPT (Same as launcher) ===
//     $encrypt = function(array $data, string $key): string {
//         $key = substr($key, 0, 32);
//         if (strlen($key) < 32) {
//             $key = str_pad($key, 32, "\0");
//         }
//         $json = json_encode($data, JSON_UNESCAPED_UNICODE);
//         $enc  = openssl_encrypt($json, 'AES-256-ECB', $key, OPENSSL_RAW_DATA);
//         return base64_encode($enc);
//     };

//     $ENCRYPTED = $encrypt($PAYLOAD, $SECRET);

//     // === POST Request (Same as launcher) ===
//     $body = json_encode([
//         "payload" => $ENCRYPTED,
//         "token"   => $TOKEN
//     ]);

//     $ch = curl_init($SERVER_URL);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

//     $response = curl_exec($ch);
//     curl_close($ch);

//     $apiResponse = json_decode($response, true);

//     // extract game URL
//     $gameUrl = $apiResponse["data"]["url"] ?? null;


//     // === 1️⃣ STORE GAME SESSION FOR CALLBACK ===
//     DB::table('active_game_sessions')->insert([
//         'game_uid'    => $game_id,
//         'game_id'     => $game_id,
//         'game_name'   => $game_name,
//         'user_mobile' => $mobile_no,
//         'created_at'  => now()
//     ]);

//     // === FINAL RESPONSE (SAME AS BEFORE) ===
//     return response()->json([
//         'status'     => 200,
//         'message'    => 'Game Launched',
//         'gameid'     => $game_id,
//         'game_name'  => $game_name,
//         'userPhone'  => $mobile_no,
//         'gameUrl'    => $gameUrl,               // SAME KEY
//         'apiResponse'=> $apiResponse            // SAME KEY
//     ]);
// }

    // public function openGame(Request $request)
    // {
    //     //dd($request);
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required',
    //         'amount'  => 'required',
    //         'game_id' => 'required',
    //         'game_name' => 'required'
    //     ]);
    
    //     if ($validator->fails()) {
    //         return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    //     }
    
    //     $userid  = $request->user_id;
    //     $amount  = $request->amount;
    //     $game_id = $request->game_id;
    //     $game_name = $request->game_name;
    
    //     // Fetch user mobile
    //     $mobile_no = DB::table('users')->where('id', $userid)->value('mobile');
    //     if (!$mobile_no) {
    //         return response()->json(['status' => 404, 'message' => 'User not found']);
    //     }
    
    //     $TOKEN  = '9cf215d00828f1f2ae6663d3e0d3ca5e';
    //     $SECRET = 'a1e5c1441441d4ea763b0de4be8dfebe';
    //     //$SERVER_URL = 'https://softapi1.online/client/'; 
    //     $SERVER_URL = 'https://igamingapis.live/api/v1'; 
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
    // //dd($PAYLOAD);
    //     // Encryption function
    //     $ENCRYPT_PAYLOAD_ECB = function(array $DATA, string $KEY): string {
    //         $JSON = json_encode($DATA);
    //         $ENC  = openssl_encrypt($JSON, 'AES-256-ECB', $KEY, OPENSSL_RAW_DATA);
    //         return base64_encode($ENC);
    //     };
    
    //     $ENCRYPTED = $ENCRYPT_PAYLOAD_ECB($PAYLOAD, $SECRET);
    //     $URL = $SERVER_URL . '?payload=' . urlencode($ENCRYPTED) . '&token=' . urlencode($TOKEN);
        
    //     //dd($URL);
    //      // 1️⃣ STORE GAME SESSION FOR CALLBACK
    //     DB::table('active_game_sessions')->insert([
    //         'game_uid'    => $game_id,
    //         'game_id'     => $game_id,
    //         'game_name'   => $game_name,
    //         'user_mobile' => $mobile_no,
    //         'created_at'  => now()
    //     ]);
    
    //     // Hit API
    //   // $response = file_get_contents($URL);
       
    //     $ch = curl_init();
        
    //     curl_setopt($ch, CURLOPT_URL, $URL);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
    //     $response = curl_exec($ch);
        
    //     curl_close($ch);
            
    //     return response()->json([
    //         'status'   => 200,
    //         'message'  => 'Game Launched',
    //         'gameid'   => $game_id,
    //         'game_name'=> $game_name,
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