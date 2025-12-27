<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PayinController extends Controller
{
    
    public function getAllQrCodes(Request $request)
    {
        try {
            $qrCodes = DB::table('qr_codes')->where('status', 1)->get();

            if ($qrCodes->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No QR codes found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'message' => 'QR codes fetched successfully',
                'data' => $qrCodes,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function manual_payin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'        => 'required|exists:users,id',
            'cash'           => 'required|numeric',
            'transaction_id' => 'nullable|integer',
            'screenshot'     => 'required|string',
            'coupon_id'      => 'nullable|exists:coupons,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ]);
        }
    
        $inr            = $request->cash;   // ✅ ORIGINAL CASH
        $image          = $request->screenshot;
        $transaction_id = $request->transaction_id;
        $coupon_id      = $request->coupon_id;
        $userid         = $request->user_id;
        $datetime       = now();
        $orderid        = date('YmdHis') . rand(11111, 99999);
        $bonus          = 0;                // ✅ BONUS SEPARATE
    
        if (empty($image) || $image === '0' || $image === 'null') {
            return response()->json([
                'status' => 400,
                'message' => 'Please Select Image'
            ]);
        }
    
        // ✅ base64 clean
        $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $image);
        $imageData  = base64_decode($base64Data);
    
        if ($imageData === false) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid base64 image'
            ]);
        }
    
        // ✅ upload dir
        $uploadDir = public_path('uploads/payinqr');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    
        $fileName = 'screenshot_' . time() . '_' . rand(1000, 9999) . '.png';
        $filePath = $uploadDir . '/' . $fileName;
    
        if (!file_put_contents($filePath, $imageData)) {
            return response()->json([
                'status' => 400,
                'message' => 'Failed to save image'
            ]);
        }
    
        // ✅ full URL
        $baseUrl = 'https://root.winbhai.in/public/';
        $fullUrl = $baseUrl . 'uploads/payinqr/' . $fileName;
    
        /* ===================================================
            ✅ COUPON LOGIC
        =================================================== */
        if (!empty($coupon_id)) {
    
            $coupon = DB::table('coupons')
                ->where('id', $coupon_id)
                ->where('status', 1)
                ->first();
    
            if (!$coupon) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Invalid or expired coupon'
                ]);
            }
    
            $used = DB::table('coupon_history')
                ->where('user_id', $userid)
                ->where('coupon_id', $coupon_id)
                ->exists();
    
            if ($used) {
                return response()->json([
                    'status' => 400,
                    'message' => 'You have already used this coupon'
                ]);
            }
    
            // ✅ BONUS ONLY
            $bonus = ($inr * $coupon->percentage) / 100;
    
            DB::table('coupon_history')->insert([
                'user_id'   => $userid,
                'coupon_id' => $coupon_id,
                'used_at'   => now()
            ]);
        }
    
        /* ===================================================
            ✅ PAYINS INSERT (FIXED)
        =================================================== */
        DB::table('payins')->insert([
            'user_id'        => $userid,
            'cash'           => $inr,       // ✅ ORIGINAL CASH
            'bonus'          => $bonus,     // ✅ BONUS HERE
            'transaction_id' => $transaction_id,
            'type'           => '2',
            'typeimage'      => $fullUrl,
            'order_id'       => $orderid,
            'status'         => 1,
            'created_at'     => $datetime,
            'updated_at'     => $datetime
        ]);
    
        return response()->json([
            'status' => 200,
            'message' => 'Manual Payment Request sent successfully. Please wait for admin approval.'
        ]);
    }
    
    public function qr_view() 
    {
    
           $show_qr = DB::select("SELECT* FROM `usdt_qr`");
           //$show_qr = DB::select("SELECT `name`, `qr_code` FROM `usdt_qr`");
    
            if ($show_qr) {
                $response = [
                    'message' => 'Successfully',
                    'status' => 200,
                    'data' => $show_qr
                ];
    
                return response()->json($response,200);
            } else {
                return response()->json(['message' => 'No record found','status' => 400,
                    'data' => []], 400);
            }
        }
        
    public function bappa_venture(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'user_id'   => 'required|exists:users,id',
        'cash'      => 'required|numeric',
        'type'      => 'required',
        'coupon_id' => 'nullable|exists:coupons,id',
    ])->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status'  => 400,
            'message' => $validator->errors()->first()
        ]);
    }

    $cash      = $request->cash;        // ✅ ORIGINAL CASH
    $type      = $request->type;
    $userid    = $request->user_id;
    $coupon_id = $request->coupon_id;
    $bonus     = 0;                     // ✅ BONUS SEPARATE

    $orderid  = date('YmdHis') . rand(11111, 99999);
    $datetime = now();

    $user = DB::table('users')->where('id', $userid)->first();
    if (!$user) {
        return response()->json([
            'status'  => 400,
            'message' => 'User not found'
        ]);
    }

    $merchantid = DB::table('admin_settings')
        ->where('id', 12)
        ->value('longtext');

    /* ===================================================
        ✅ COUPON LOGIC (SAME)
    =================================================== */
    if (!empty($coupon_id)) {

        $coupon = DB::table('coupons')
            ->where('id', $coupon_id)
            ->where('status', 1)
            ->first();

        if (!$coupon) {
            return response()->json([
                'status'  => 400,
                'message' => 'Invalid or expired coupon'
            ]);
        }

        $used = DB::table('coupon_history')
            ->where('user_id', $userid)
            ->where('coupon_id', $coupon_id)
            ->exists();

        if ($used) {
            return response()->json([
                'status'  => 400,
                'message' => 'You have already used this coupon'
            ]);
        }

        // ✅ BONUS CALCULATION
        $bonus = ($cash * $coupon->percentage) / 100;

        DB::table('coupon_history')->insert([
            'user_id'   => $userid,
            'coupon_id' => $coupon_id,
            'used_at'   => now()
        ]);
    }

    /* ===================================================
        ✅ PAYIN INSERT (FIXED)
    =================================================== */
    if ($type == 1) {

        $redirect_url = env('APP_URL') . "api/checkPayment?order_id=$orderid";

        $insert = DB::table('payins')->insert([
            'user_id'      => $userid,
            'cash'         => $cash,      // ✅ ONLY CASH
            'bonus'        => $bonus,     // ✅ BONUS
            'type'         => $type,
            'order_id'     => $orderid,
            'redirect_url' => $redirect_url,
            'status'       => 1,
            'typeimage'    => "https://root.winbhai.in/uploads/fastpay_image.png",
            'created_at'   => $datetime,
            'updated_at'   => $datetime
        ]);

        if (!$insert) {
            return response()->json([
                'status'  => 400,
                'message' => 'Failed to store record in payin history!'
            ]);
        }

        /* ===================================================
            ✅ PAYMENT GATEWAY REQUEST (UNCHANGED)
        =================================================== */
        $postParameter = [
            'merchantid'   => $merchantid,
            'orderid'      => $orderid,
            'amount'       => $cash,
            'name'         => $user->u_id,
            'email'        => 'abc@gmail.com',
            'mobile'       => $user->mobile,
            'remark'       => 'payIn',
            'type'         => $cash,
            'redirect_url' => "https://root.winbhai.in/api/checkPayment?order_id=$orderid"
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://bappaventures.com/api/paynow',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($postParameter),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        echo $response;

    } else {
        return response()->json([
            'status'  => 400,
            'message' => 'Something went wrong!'
        ]);
    }
}


    public function checkPayment(Request $request)
    {
    $orderid = $request->input('order_id');
	
    if ($orderid == "") {
        return response()->json(['status' => 400, 'message' => 'Order Id is required']);
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://bappaventures.com/api/payinstatus?order_id=$orderid",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Cookie: ci_session=uvkdvmvc3n03msqrd4bfiudbgk658uif'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $decodedValue = json_decode($response, true);
    $match_orders = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->first();
		 $uids = $match_orders->user_id;
    if (isset($decodedValue['status']) && $decodedValue['status'] == "success") {

        $match_order = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->first();
        if ($match_order) {

            $uid = $match_order->user_id;
            $cash = $match_order->cash;
            $orderid = $match_order->order_id;
            $datetime = now();

            $update_payin = DB::table('payins')
                ->where('order_id', $orderid)
                ->where('status', 1)
                ->where('user_id', $uid)
                ->update(['status' => 2]);

            if ($update_payin) {

                // user data
                $user = DB::table('users')->where('id', $uid)->first();
                $referuserid = $user->referral_user_id;
                $first_recharge = $user->first_recharge;

                // FIRST RECHARGE BONUS CALCULATION (10%)
                $bonus = 0;
                if ($first_recharge == 0) {
                    $bonus = ($cash * 10) / 100;  // 10% EXTRA BONUS
                }

                // Insert in extra_first_deposit_bonus_claim only if first recharge
                if ($first_recharge == 0) {
                    DB::insert("INSERT INTO extra_first_deposit_bonus_claim 
                        (`userid`, `extra_fdb_id`, `bonus`, `status`, `created_at`, `updated_at`)
                        VALUES ('$uid','1','$bonus','0','$datetime','$datetime')");
                }

                // Update User Wallet
                DB::update("UPDATE users 
                    SET 
                        wallet = wallet + $cash + $bonus,
                        recharge = recharge + $cash,
                        total_payin = total_payin + $cash,
                        no_of_payin = no_of_payin + 1,
                        deposit_balance = deposit_balance + $cash,
                        first_recharge = IF(first_recharge = 0, 1, first_recharge),
                        first_recharge_amount = IF(first_recharge = 0, $cash, first_recharge_amount)
                    WHERE id = $uid");

                // Update referral user yesterday stats
                if ($referuserid != 0) {
                    DB::update("UPDATE users SET 
                        yesterday_payin = yesterday_payin + $cash,
                        yesterday_no_of_payin = yesterday_no_of_payin + 1,
                        yesterday_first_deposit = yesterday_first_deposit + IF($first_recharge = 0, $cash, 0)
                    WHERE id = $referuserid");
                }

                return redirect()->away('https://root.winbhai.in/uploads/payment_success.php');
            } 
            else {
                return response()->json(['status' => 400, 'message' => 'Failed to update payment status!']);
            }

        } else {
            return response()->json(['status' => 400, 'message' => 'Order id not found or already processed']);
        }

    } else {
		 $update_payin = DB::table('payins')
                ->where('order_id', $orderid)
                ->where('status', 1)
                ->where('user_id', $uids)
                ->update(['status' => 3]);

        return redirect()->away('https://root.winbhai.in/uploads/failed.php');
		
    }
}


    
//     public function bappa_venture(Request $request) /// bappaventures
//     {
       
//          $validator = Validator::make($request->all(), [
//             'user_id' => 'required|exists:users,id',
//             'cash' => 'required',
//             'type' => 'required',
//             'coupon_id' => 'nullable|exists:coupons,id',  // ⭐ new coupon parameter
//         ]);
//         $validator->stopOnFirstFailure();

//         if ($validator->fails()) {
//             $response = [
//                 'status' => 400,
//                 'message' => $validator->errors()->first()
//             ];

//             return response()->json($response);
//         }

        
        
//     	$cash = $request->cash;
//         // $extra_amt = $request->extra_cash;
//          $type = $request->type;
//         $userid = $request->user_id;
//          $coupon_id = $request->coupon_id;
//           $bonus = 0;
// 	   //	$total_amt=$cash+$extra_amt+$bonus;
		 
//               $date = date('YmdHis');
//         $rand = rand(11111, 99999);
//         $orderid = $date . $rand;
//         $datetime=now();
//         $check_id = DB::table('users')->where('id',$userid)->first();
//         $merchantid =DB::table('admin_settings')->where('id',12)->value('longtext');
        
        
        
//         //  1️⃣  COUPON LOGIC STARTS  
//     // ======================================================
//     if (!empty($coupon_id)) {

//         // fetch coupon
//         $coupon = DB::table('coupons')
//             ->where('id', $coupon_id)
//             ->where('status', 1)
//             ->first();

//         if (!$coupon) {
//             return response()->json(['status' => 400, 'message' => 'Invalid or expired coupon']);
//         }

//         // check if user already used this coupon
//         $used = DB::table('coupon_history')
//             ->where('user_id', $userid)
//             ->where('coupon_id', $coupon_id)
//             ->exists();

//         if ($used) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'You have already used this coupon'
//             ]);
//         }

//         // calculate bonus
//         $bonus = ($cash * $coupon->percentage) / 100;

//         // store coupon usage
//         DB::table('coupon_history')->insert([
//             'user_id' => $userid,
//             'coupon_id' => $coupon_id,
//             'used_at' => now()
//         ]);
//     }

//     // TOTAL AMOUNT = cash + bonus
//     $totalAmount = $cash + $bonus;
        
        
        
//         if($type == 1){
//         if ($check_id) {
//             $redirect_url = env('APP_URL')."api/checkPayment?order_id=$orderid";
//             //dd($redirect_url);
//             $insert_payin = DB::table('payins')->insert([
//                 'user_id' => $request->user_id,
//               'cash' => $totalAmount,      // ⭐ TOTAL AMOUNT save
//                 'extra_cash' => $bonus,
//                 'type' => $request->type,
//                 'order_id' => $orderid,
//                 'redirect_url' => $redirect_url,
//                 'status' => 1, // Assuming initial status is 0
// 				'typeimage'=>"https://root.winbhai.in/uploads/fastpay_image.png",
//                 'created_at'=>$datetime,
//                 'updated_at'=>$datetime
//             ]);
//          // dd($redirect_url);
//             if (!$insert_payin) {
//                 return response()->json(['status' => 400, 'message' => 'Failed to store record in payin history!']);
//             }
 
//             $postParameter = [
//                 'merchantid' =>$merchantid,
//                 'orderid' => $orderid,
//                 'amount' => $request->cash,
//                 'name' => $check_id->u_id,
//                 'email' => "abc@gmail.com",
//                 'mobile' => $check_id->mobile,
//                 'remark' => 'payIn',
//                 'type'=>$request->cash,
//                 'redirect_url' => "https://root.winbhai.in/api/checkPayment?order_id=$orderid"
//               // 'redirect_url' => config('app.base_url') ."/api/checkPayment?order_id=$orderid"
//             ];
// //echo json_encode($postParameter);die;

//             $curl = curl_init();

//             curl_setopt_array($curl, array(
//                 CURLOPT_URL => 'https://bappaventures.com/api/paynow',
//                 CURLOPT_RETURNTRANSFER => true,
//                 CURLOPT_ENCODING => '',
//                 CURLOPT_MAXREDIRS => 10,
//                 CURLOPT_TIMEOUT => 0, 
//                 CURLOPT_FOLLOWLOCATION => true,
//                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                 CURLOPT_CUSTOMREQUEST => 'POST',
//                 CURLOPT_POSTFIELDS => json_encode($postParameter),
//                 CURLOPT_HTTPHEADER => array(
//                     'Content-Type: application/json',
//                     'Cookie: ci_session=1ef91dbbd8079592f9061d5df3107fd55bd7fb83'
//                 ),
//             ));

//             $response = curl_exec($curl);
//             curl_close($curl);
             
// 			echo $response;
			
//         } else {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Internal error!'
//             ]);
//         }
            
//         }else{
//           return response()->json([
//                 'status' => 400,
//                 'message' => 'Something went is wrong ....!'
//             ]); 
//         }
//     }
    
	public function payzaaar(Request $request)
    {
    $validator = Validator::make($request->all(), [
    'user_id' => 'required|exists:users,id',
    'cash'    => 'required',
    'type'    => 'required|in:0',
    ], [
    'type.in' => 'The selected payment type is invalid. Only type 0 is allowed.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 400,
            'message' => $validator->errors()->first()
        ]);
    }

    $cash     = $request->cash;
    $userid   = $request->user_id;
    $orderid  = 'ORD'.rand(111111,999999);
	$username = DB::table('users')->where('id', $userid)->value('username');
    
    
    $merchantId = "USER000107";
    $apiToken   = "919f2825814d8fe0ea5ccf4a9e74b180";
    $username   = "$username";
    $email      = "johndoe@gmail.com"; 
    $phone      = "9876543210";
    $orderId    = "$orderid";
    $amount     = "$cash"; 
    $remark     = "Payment for order #$orderId";
    
    $payload = [
    'data' => [
        'merchantid' => $merchantId,
        'apitoken'   => $apiToken,
        'username'   => $username,
        'email'      => $email,
        'phone'      => $phone,
        'orderid'    => $orderId,
        'remark'     => $remark,
        'amount'     => $amount
    ],
    'apiToken' => $apiToken
    ];
    

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://payzaaar.com/dashboard/api/encodeData',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>json_encode($payload),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Cookie: ci_session=8l74oama3etp16m9o9mv819p7m1ebufk'
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $encoded = json_decode($response, true);
    $encryptedData = $encoded['data'];
    
    
    $payloadforpayin=[
        "data"=>"$encryptedData",
        "apitoken"=>"919f2825814d8fe0ea5ccf4a9e74b180"
        ];
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://payzaaar.com/dashboard/api/paynow',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>json_encode($payloadforpayin),
      CURLOPT_HTTPHEADER => array(
        'X-Merchant-ID: USER000107',
        'X-Api-Key: 919f2825814d8fe0ea5ccf4a9e74b180',
        'Content-Type: application/json',
        'Cookie: ci_session=8l74oama3etp16m9o9mv819p7m1ebufk'
      ),
    ));
    
    $responses = curl_exec($curl);

    if ($responses === false) {
        return response()->json([
            'status' => 500,
            'message' => 'CURL error in paynow request: ' . curl_error($curl),
        ]);
    }

    curl_close($curl);
    
    $encodedd = json_decode($responses, true);
    
    // Check if decoding failed or data is missing
    if (!isset($encodedd['data'])) {
        return response()->json([
            'status' => 500,
            'message' => 'Invalid paynow API response',
            'raw_response' => $responses,  // to help debug the response format
        ]);
    }

    $encryptedData = $encodedd['data'];

    $payloadForDecode=[
        "encodedData"=>"$encryptedData",
        "apiToken"=>"919f2825814d8fe0ea5ccf4a9e74b180"
        
        ]; 
     

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://payzaaar.com/dashboard/api/decodeData',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>json_encode($payloadForDecode),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Cookie: ci_session=8l74oama3etp16m9o9mv819p7m1ebufk'
      ),
    ));
    
    $responsess = curl_exec($curl);
    
    curl_close($curl);
    //echo $responsess;
    
   // die;
		
		  $responsess = json_decode($responsess, true);
    if (!isset($responsess['data']['status'])) {
        return response()->json([
            'status'       => 500,
            'message'      => 'Invalid decode response format',
            'raw_response' => $responsess
        ]);
    }

    // Step 6: Check transaction status
    if ($responsess['data']['status'] != 'success') {
        return response()->json([
            'status'         => 400,
            'message'        => $responsess['data']['msg'] ?? 'Transaction failed',
            'orderid'        => $responsess['data']['orderid'] ?? $orderid,
            'payment_status' => $responsess['data']['status']
        ]);
    }

    // Step 7: Save transaction
    DB::table('payins')->insert([
        'user_id'      => $userid,
        'cash'         => $cash,
        'type'         => 0,
        'order_id'     => $orderid,
        'redirect_url' => "https://root.skywinner.live/uploads/payment_success.php",
        'status'       => 1,
        'typeimage'    => "https://root.skywinner.live/public/uploads/payzaar.jpg",
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    // Step 8: Clean payment link (remove spaces)
    // if (isset($decodedResponse['data']['paymentlink'])) {
    //     $decodedResponse['data']['paymentlink'] = preg_replace('/\s+/', '', $decodedResponse['data']['paymentlink']);
    // }
    
        $responsess['data']['paymentlink'] = preg_replace('/\s+/', '', $responsess['data']['paymentlink']);
    		$intent_link=$responsess['data']['paymentlink'];
    		$qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?amp;size=200x200&data=' . urlencode($intent_link);
    		$responsess['data']['Qr_Link'] =$qr_code_url;
    
        // Step 9: Return response
        return response()->json([
            'status'  => 200,
            'message' => 'Transaction successful',
            'data'    => $responsess['data']
        ]);
    }
    
    public function payzaaarCallback(Request $request)
    {
    $data = $request->all(); // Get all POST data

    // Log callback for reference/debugging
    DB::table('payzaar_callback')->insert([
        'data'     => json_encode($data),
        'datetime' => now()
    ]);

    // Validate required fields
    $orderId = $data['orderid'] ?? null;
    $amount  = $data['amount'] ?? null;
    $status  = $data['status'] ?? null;
    $utr     = $data['utr'] ?? null;

    if (!$orderId || !$amount || !$status) {
        return response()->json(['status' => 400, 'message' => 'Missing required callback data']);
    }

    if (strtolower($status) === 'success') {

        $payin = DB::table('payins')->where('order_id', $orderId)->first();

        if ($payin && $payin->status != 2) {
            // Mark payin as successful
            DB::table('payins')->where('order_id', $orderId)->update(['status' => 2, 'updated_at' => now()]);

            $userId = $payin->user_id;
            $cash   = $payin->cash;

            $user = DB::table('users')->where('id', $userId)->first();

            if ($user) {
                // Update user wallet balances
                DB::table('users')->where('id', $userId)->update([
                    'wallet'          => DB::raw("wallet + $cash"),
                    'recharge'        => DB::raw("recharge + $cash"),
                    'total_payin'     => DB::raw("total_payin + $cash"),
                    'no_of_payin'     => DB::raw("no_of_payin + 1"),
                    'deposit_balance' => DB::raw("deposit_balance + $cash")
                ]);

                // First recharge check
                if ($user->first_recharge == 0) {
                    DB::table('users')->where('id', $userId)->update([
                        'first_recharge'        => $cash,
                        'first_recharge_amount' => $cash
                    ]);

                    // Referral update
                    if ($user->referral_user_id) {
                        DB::table('users')->where('id', $user->referral_user_id)->update([
                            'yesterday_payin'         => DB::raw("yesterday_payin + $cash"),
                            'yesterday_no_of_payin'   => DB::raw("yesterday_no_of_payin + 1"),
                            'yesterday_first_deposit' => DB::raw("yesterday_first_deposit + $cash")
                        ]);
                    }
                }
            }
        }
    }

    return response()->json(['status' => 200, 'message' => 'Callback processed']);
}

    public function checkPayzaaarPayment(Request $request)
    {
    $orderid = $request->input('order_id');

    if (empty($orderid)) {
        return response()->json(['status' => 400, 'message' => 'Order ID is required']);
    }

    $match_order = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->first();

    if (!$match_order) {
        return response()->json(['status' => 400, 'message' => 'Order ID not found or already processed']);
    }

    $uid      = $match_order->user_id;
    $cash     = $match_order->cash;
    $type     = $match_order->type;
    $datetime = now();

    $update_payin = DB::table('payins')
        ->where('order_id', $orderid)
        ->where('status', 1)
        ->where('user_id', $uid)
        ->update(['status' => 2]);

    if (!$update_payin) {
        return response()->json(['status' => 400, 'message' => 'Failed to update payment status']);
    }

    // Check if it's user's first recharge
    $referData = DB::table('users')->select('referral_user_id', 'first_recharge')->where('id', $uid)->first();
    $referuserid     = $referData->referral_user_id;
    $first_recharge  = $referData->first_recharge;

    if ($first_recharge == 0) {
        $extra = DB::table('extra_first_deposit_bonus')
            ->where('first_deposit_ammount', '<=', $cash)
            ->where('max_amount', '>=', $cash)
            ->first();

        if ($extra) {
            $bonus  = $extra->bonus;
            $amount = $cash + $bonus;

            DB::table('extra_first_deposit_bonus_claim')->insert([
                'userid'         => $uid,
                'extra_fdb_id'   => $extra->id,
                'amount'         => $cash,
                'bonus'          => $bonus,
                'status'         => 0,
                'created_at'     => $datetime,
                'updated_at'     => $datetime,
            ]);

            DB::update("UPDATE users 
                SET 
                    wallet = wallet + $amount,
                    first_recharge = 1,
                    first_recharge_amount = first_recharge_amount + $amount,
                    recharge = recharge + $amount,
                    total_payin = total_payin + $amount,
                    no_of_payin = no_of_payin + 1,
                    deposit_balance = deposit_balance + $amount
                WHERE id = ?", [$uid]);

        } else {
            // No extra bonus matched
            DB::update("UPDATE users 
                SET 
                    wallet = wallet + $cash,
                    first_recharge = 1,
                    first_recharge_amount = first_recharge_amount + $cash,
                    recharge = recharge + $cash,
                    total_payin = total_payin + $cash,
                    no_of_payin = no_of_payin + 1,
                    deposit_balance = deposit_balance + $cash
                WHERE id = ?", [$uid]);
        }

        if (!empty($referuserid)) {
            DB::update("UPDATE users 
                SET 
                    yesterday_payin = yesterday_payin + $cash,
                    yesterday_no_of_payin = yesterday_no_of_payin + 1,
                    yesterday_first_deposit = yesterday_first_deposit + $cash,
                    created_at = ?
                WHERE id = ?", [$datetime, $referuserid]);
        }

    } else {
        // Not first recharge
        DB::update("UPDATE users 
            SET 
                wallet = wallet + $cash,
                recharge = recharge + $cash,
                total_payin = total_payin + $cash,
                no_of_payin = no_of_payin + 1,
                deposit_balance = deposit_balance + $cash
            WHERE id = ?", [$uid]);

        if (!empty($referuserid)) {
            DB::update("UPDATE users 
                SET 
                    yesterday_payin = yesterday_payin + $cash,
                    yesterday_no_of_payin = yesterday_no_of_payin + 1
                WHERE id = ?", [$referuserid]);
        }
    }

    // ✅ Redirect to success page
    return redirect()->away('https://root.jupitergames.world/uploads/payment_success.php');
}
	
    public function withdraw_request(Request $request)
    {
    
    		  $date = date('Ymd');
            $rand = rand(1111111, 9999999);
            $transaction_id = $date . $rand;
    	
    		 $userid=$request->userid;
    		 $amount=$request->amount;
    		   $validator=validator ::make($request->all(),
            [
                'userid'=>'required',
    			'amount'=>'required',
    			
            ]);
            $date=date('Y-m-d h:i:s');
            if($validator ->fails()){
                $response=[
                    'success'=>"400",
                    'message'=>$validator ->errors()
                ];                                                   
                
                return response()->json($response,400);
            }
          
    		 $datetime = date('Y-m-d H:i:s');
    		 
             $user = DB::select("SELECT * FROM `users` where `id` =$userid");
    		 $account_id=$user[0]->accountno_id;
    		 $mobile=$user[0]->mobile;
    		 $wallet=$user[0]->wallet;
    // 		 dd($wallet);
    		 $accountlist=DB::select("SELECT * FROM `bank_details` WHERE `id`=$account_id");
    		 
    		 $insert= DB::table('transaction_history')->insert([
            'userid' => $userid,
            'amount' => $amount,
            'mobile' => $mobile,
    		  'account_id'=>$account_id,
            'status' => 0,
    			 'type'=>1,
            'date' => $datetime,
    		  'transaction_id' => $transaction_id,
        ]);
    		  DB::select("UPDATE `users` SET `wallet`=`wallet`-$amount,`winning_wallet`=`winning_wallet`-$amount  WHERE `id`=$userid");
              if($insert){
              $response =[ 'success'=>"200",'data'=>$insert,'message'=>'Successfully'];return response ()->json ($response,200);
          }
          else{
           $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
          } 
        }

    public function redirect_success()
    {
        return view('success');
    }
    
    public function usdt_payin(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'cash' => 'required|numeric',
        'type' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }

    $usdt = $request->cash;
    $image = $request->screenshot;
    $type = $request->type;
    $userid = $request->user_id;
    $inr = $usdt;
    $datetime = now();
    $orderid = date('YmdHis') . rand(11111, 99999);

    // Validate image input
    if (empty($image) || $image === '0' || $image === 'null' || $image === null || $image === '' || $image === 0) {
        return response()->json([
            'status' => 400,
            'message' => 'Please Select Image'
        ]);
    }

    // Handle image saving
    $path = '';
    if (!empty($image)) {
        $imageData = base64_decode($image);
        if ($imageData === false) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid base64 encoded image'
            ]);
        }

        // Save image to /public/usdt_images directory
        $newName = Str::random(6) . '.png';
        $relativePath = 'usdt_images/' . $newName;

        // Ensure directory exists
        if (!file_exists(public_path('usdt_images'))) {
            mkdir(public_path('usdt_images'), 0775, true);
        }

        // Save the image file
        if (!file_put_contents(public_path($relativePath), $imageData)) {
            return response()->json([
                'status' => 400,
                'message' => 'Failed to save image'
            ]);
        }

        // Generate URL to store in DB
        $path = asset('usdt_images/' . $newName);
    }

    // Handle type == 0 (payin logic)
    if ($type == 1) {
        $insert_usdt = DB::table('payins')->insert([
            'user_id' => $userid,
            'cash' => $usdt * 90,
            'usdt_amount' => $inr,
            'type' => '1',
            'typeimage' => $path,
            'order_id' => $orderid,
            'status' => 1,
            'created_at' => $datetime,
            'updated_at' => $datetime
        ]);

        if ($insert_usdt) {
            return response()->json([
                'status' => 200,
                'message' => 'USDT Payment Request sent successfully. Please wait for admin approval.'
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to insert USDT Payment'
            ]);
        }
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'Invalid Type'
        ]);
    }
}

}
