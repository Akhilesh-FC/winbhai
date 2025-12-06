<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;

class PlayerController extends Controller
{
    
    public function storePlayer(Request $request)
    {
    //dd($request->all());
    try {
        //dd($request);
        // ✅ Validate input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
            'password' => 'required|min:6',
            'name'     => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        // ✅ Generate required data
        $randomReferralCode = 'ZUP' . strtoupper(Str::random(6));
        $baseUrl = URL::to('/');
        $uid = 'U' . rand(100000, 999999); // your uid function ka short version

        // ✅ Referral id (jis agent/admin ne create kiya)
        $referralUserId = session('id');  

        // ✅ Prepare user data
        $data = [
            'username'       => $request->username,
            'name'           => $request->name,
            'u_id'           => $uid,
            'password'       => $request->password,  // ⚠ Plain text (same as aapke register function me tha)
            'userimage'      => $baseUrl . "/image/download.png",
            'status'         => 1,
            'referral_code'  => $randomReferralCode,
            'wallet'         => 0,
            'referral_user_id' => $referralUserId,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

//dd($data);
        // ✅ Insert user
        $userId = DB::table('users')->insertGetId($data);
//dd($userId);
        return redirect()->back()->with('success', 'Player created successfully (ID: '.$userId.')');

    } catch (\Throwable $e) {
        Log::error('Create Player Error:', ['error' => $e->getMessage()]);
        return redirect()->back()->with('error', 'Something went wrong: '.$e->getMessage());
    }
}



public function player_index(Request $request)
{
    $permissions = DB::table('permissions')->select('id', 'name')->get();

    $roleId = session('role_id');
    $userId = session('user_id');

    // ✅ Agar role 1 hai -> sab data
    if ($roleId == 1) {
        $users = User::where('role_id', 5)->latest()->paginate(10);
        $agents = User::where('role_id', 4)->orderBy('id', 'desc')->get();
    } else {
        // ✅ Sabhi children IDs nikal lo (multi-level)
        $allChildIds = $this->getAllChildrenIds($userId);

        // Player list
        $users = User::where('role_id', 5)
            ->whereIn('parent_id', $allChildIds)
            ->latest()
            ->paginate(10);

        // Agents list
        $agents = User::where('role_id', 4)
            ->whereIn('parent_id', $allChildIds)
            ->orderBy('id', 'desc')
            ->get();
    }

    return view('player.index')
        ->with('user', $users)
        ->with('permissions', $permissions)
        ->with('agents', $agents);
}

/**
 * ✅ Recursive function: sabhi children (multi-level) nikalne ke liye
 */
private function getAllChildrenIds($parentId)
{
    $childIds = User::where('parent_id', $parentId)->pluck('id')->toArray();

    $allIds = [$parentId]; // apni id bhi include kar li
    foreach ($childIds as $childId) {
        $allIds = array_merge($allIds, $this->getAllChildrenIds($childId));
    }

    return $allIds;
}

public function PlayerStore(Request $request)
{
    // ✅ Parent ID ab request se aayegi
    $parentId = $request->parent_id;

    // ✅ Sirf username check kare
    $existingUser = User::where('username', $request->username)->first();

    if ($existingUser) {
        return redirect()
               ->back()
               ->withInput()
               ->with('error', 'This username is already in use.');
    }

    // If no duplicates, create the new user
    try {
        User::create([
            'username'  => $request->username,
            'password'  => $request->password, // ✅ Password hashing recommended hai
            'name'      => $request->name,
            'revenue'   => $request->revenue,
            'parent_id' => $parentId, // ✅ Ab request se aayi value
            'role_id'   => 5,
        ]);

        return redirect()->back()->with('success', 'Player created successfully!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to create player: ' . $e->getMessage());
    }
}


public function player_activity_info($id) {
    // Lucky12 bets
    $lucky12_bets = DB::table('bets')
        ->where('userid', $id)
        ->orderByDesc('created_at')
        ->get();

    // Luckycard bets
    $luckycard_bets = DB::table('aviator_bet')
        ->where('uid', $id)
        ->orderByDesc('created_at')
        ->get();

    // Triplechance bets
    $triplechance_bets = DB::table('chicken_bets')
        ->where('user_id', $id)
        ->orderByDesc('created_at')
        ->get();

    // Wallet history
    $wallet_history = DB::table('wallet_history')
        ->where('userid', $id)
        ->orderByDesc('created_at')
        ->get();

    return view('player.player_activity_info', compact(
        'lucky12_bets',
        'luckycard_bets',
        'triplechance_bets',
        'wallet_history'
    ));
}

    
public function PlayerUpdate(Request $request){
         
         $validated = $request->validate([
        'password' => 'required', 
        'revenue'=>'required',
        'user_id' => 'required|exists:users,id', 
    ]);
    
    
    $superStokez = User::where('id',$request->user_id)->update([
                    "password" => $request->password , "revenue"=> $request->revenue
                   
        ]);
    
    if($superStokez){
        return redirect()->back()->with('success', 'Changes save successfully..!');
    }

    }

    
public function playerStatus(Request $request){
       
         $validated = $request->validate([
        'status' => 'required|in:1,0', 
        'id' => 'required|exists:users,id', 
        ]);
        
        try{
            
            $user = User::where('id',$request->id)->first();
            $user->status = $request->status;
            $user->save();
            
            return redirect()->back();
            
        }catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }
    

public function wallet_store(Request $request, $id)
{
    $loggedInUser = Auth::user();
    $Auth_role = $loggedInUser->role_id;
    $Auth_id = $loggedInUser->id;

    $walletAmount = $request->wallet;

    if ($walletAmount <= 0) {
        return redirect()->back()->with('error', 'Invalid wallet amount.');
    }

    $now = Carbon::now('Asia/Kolkata');

    // Admin Role (role_id == 1) - Directly add amount
    if ($Auth_role == 1) {
        $updated = DB::table('users')
            ->where('id', $id)
            ->increment('wallet', $walletAmount);

        if ($updated) {
            DB::table('wallet_history')->insert([
                "user_id" => $id,
                "action" => $Auth_id,
                "amount" => $walletAmount,
                "description" => "Added by Admin",
                "type" => 1,
                "created_at" => $now,
                "updated_at" => $now, 
            ]);
            return redirect()->back()->with('success', 'Amount added successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to update wallet.');
        }
    }

    // Non-admin (Agent/User) - Check own wallet balance first
    $senderWallet = DB::table('users')->where('id', $Auth_id)->value('wallet');

    if ($senderWallet < $walletAmount) {
        return redirect()->back()->with('error', 'Insufficient wallet balance.');
    }

    // Transaction start
    DB::beginTransaction();

    try {
        // Deduct from sender (logged in user)
        DB::table('users')->where('id', $Auth_id)->decrement('wallet', $walletAmount);

        // Add to receiver (target user)
        DB::table('users')->where('id', $id)->increment('wallet', $walletAmount);

        // Wallet history for receiver
        DB::table('wallet_history')->insert([
            "user_id" => $id,
            "action" => $Auth_id,
            "amount" => $walletAmount,
            "description" => "Transferred by User ID: $Auth_id",
            "type" => 1,
            "created_at" => $now,
            "updated_at" => $now, 
        ]);

        // Wallet history for sender
        DB::table('wallet_history')->insert([
            "user_id" => $Auth_id,
            "action" => $Auth_id,
            "amount" => -$walletAmount,
            "description" => "Transferred to User ID: $id",
            "type" => 2,
            "created_at" => $now,
            "updated_at" => $now, 
        ]);

        DB::commit();
        return redirect()->back()->with('success', 'Amount transferred successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Transaction failed. Please try again.');
    }
}


public function wallet_subtract(Request $request, $id)
{
    $amountToSubtract = $request->wallet;
    $now = Carbon::now('Asia/Kolkata'); 

    if ($amountToSubtract <= 0) {
        return redirect()->back()->with('error', 'Invalid amount to subtract.');
    }

    // Target user (जिसका balance घटाना है)
    $user = DB::table('users')->where('id', $id)->first();
    
    if (!$user) {
        return redirect()->back()->with('error', 'User not found.');
    }

    if ($user->wallet < $amountToSubtract) {
        return redirect()->back()->with('error', 'Insufficient wallet balance.');
    }

    // Logged-in user (admin)
    $loggedInUser = Auth::user();

    DB::beginTransaction();
    try {
        // 1. Target user se wallet subtract
        DB::table('users')
            ->where('id', $id)
            ->decrement('wallet', $amountToSubtract);

        DB::table('wallet_history')->insert([
            "user_id"    => $id,
            "amount"     => $amountToSubtract,
            "description"=> "Subtracted by admin ID {$loggedInUser->id}",
            "type"       => 2, // debit
            "created_at" => $now,
            "updated_at" => $now,
        ]);

        // 2. Logged-in admin ke wallet me add
        DB::table('users')
            ->where('id', $loggedInUser->id)
            ->increment('wallet', $amountToSubtract);

        DB::table('wallet_history')->insert([
            "user_id"    => $loggedInUser->id,
            "amount"     => $amountToSubtract,
            "description"=> "Added from user ID $id",
            "type"       => 1, // credit
            "created_at" => $now,
            "updated_at" => $now,
        ]);

        DB::commit();

        return redirect()->back()->with('success', 'Amount transferred successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Transaction failed: ' . $e->getMessage());
    }
}

public function update_status(string $id,string $status)
{ 
         $user=DB::table('users')
                ->where('id', $id) 
                ->update(['status' => $status]);
                 return redirect('player-index'); 
    } 
}





