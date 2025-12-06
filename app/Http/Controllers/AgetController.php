<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AgetController extends Controller
{
    
    public function agentPlayers()
    {
        $agent_id = auth()->id();  // logged in agent ID
    
        // Get all players created by agent
        $players = DB::table('users')
                    ->where('agent_id', $agent_id)
                    ->get();
    
        return view('agent.agent_player', compact('players'));
    }

    public function agentPlayerStore(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'mobile'   => 'required|unique:users,mobile',
            'password' => 'required|min:6',
        ]);
    
        $agent_id = auth()->id(); // logged in agent
    
        $u_id = 'WB' . rand(10000, 99999);
        $referral_code = 'WB' . strtoupper(Str::random(8));
    
        DB::table('users')->insert([
            'username'          => $request->username,
            'email'             => $request->email,
            'password'          => $request->password,
            'mobile'            => $request->mobile,
            'agent_id'          => $agent_id,      // ⭐ Agent ID
            'referral_user_id'  => $agent_id,      // ⭐ Referral to Agent
            'country_code'      => '+91',
            'role_id'           => 0,  // ⭐ Player role
            'status'            => 1,
            'u_id'              => $u_id,
            'referral_code'     => $referral_code,
            'userimage'         => "https://root.winbhai.in/uploads/profileimage/1.png",
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    
        return redirect()->back()->with('success', 'Player Added Successfully!');
    }

    public function agentUserDetails(Request $request, $agent_id)
    {
      
    
        // Date Filter
        $from = $request->from_date;
        $to   = $request->to_date;
    
        // MLM Users Fetch
        $allUsers = collect();
        $currentLevelIds = collect([$agent_id]);
    
        while (true) {
            $nextUsers = DB::table('users')
                ->whereIn('referral_user_id', $currentLevelIds)
                ->select('id','username','email','mobile','created_at','referral_user_id')
                ->get();
    
            if ($nextUsers->isEmpty()) break;
    
            $allUsers = $allUsers->merge($nextUsers);
            $currentLevelIds = $nextUsers->pluck('id');
        }
    
        $users = $allUsers;
        $userIds = $users->pluck('id');
    
        // Today Users Count
        $today_users = $users->filter(function ($u) {
            return Carbon::parse($u->created_at)->isToday();
        })->count();
    
    
        // DATE RANGE FILTER FUNCTION
        $applyDate = function($query) use ($from, $to) {
            if ($from != "" && $to != "") {
                return $query->whereBetween('created_at', [
                    $from . " 00:00:00",
                    $to . " 23:59:59"
                ]);
            } elseif ($from != "" && $to == "") {
                return $query->whereDate('created_at', $from);
            } elseif ($from == "" && $to != "") {
                return $query->whereDate('created_at', $to);
            }
            return $query;
        };
    
        // DEPOSITS
        $deposits = $applyDate(
            DB::table('payins')->whereIn('user_id', $userIds)
        )->orderBy('id','desc')->get();
    
        $today_deposits = DB::table('payins')
            ->whereIn('user_id', $userIds)
            ->where('status', 2)
            ->whereDate('created_at', Carbon::today())
            ->sum('cash');
    
    
        // WITHDRAW
        $withdraws = $applyDate(
            DB::table('withdraw_histories')->whereIn('user_id', $userIds)
        )->orderBy('id','desc')->get();
    
        $today_withdraws = DB::table('withdraw_histories')
            ->whereIn('user_id', $userIds)
            ->where('status', 2)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');
    
    
        // WINGO
        $wingo_history = $applyDate(
            DB::table('bets')
                ->whereIn('userid', $userIds)
                ->whereIn('game_id', [1,2,3,4])
        )->orderBy('id','desc')->get();
    
        // CHICKEN ROAD
        $dragon_history = $applyDate(
            DB::table('chicken_bets')
                ->whereIn('user_id', $userIds)
                ->where('game_id', 19)
        )->orderBy('id','desc')->get();
    
        // AVIATOR
        $aviator_history = $applyDate(
            DB::table('aviator_bet')
                ->whereIn('uid', $userIds)
        )->orderBy('id','desc')->get();
    
    
        // SUMMARY (FINAL FIXED)
        $summary = [
            'total_users'     => $users->count(),
            'today_users'     => $today_users,
    
            'total_deposit'   => DB::table('payins')
                                   ->whereIn('user_id', $userIds)
                                   ->where('status', 2)
                                   ->sum('cash'),
    
            'today_deposit'   => $today_deposits,
    
            'total_withdraw'  => DB::table('withdraw_histories')
                                   ->whereIn('user_id', $userIds)
                                   ->where('status', 2)
                                   ->sum('amount'),
    
            'today_withdraw'  => $today_withdraws,
    
            'total_wingo_bet' => DB::table('bets')
                                   ->whereIn('userid', $userIds)
                                   ->whereIn('game_id', [1,2,3,4])
                                   ->sum('amount'),
    
            'total_dragon_bet'=> DB::table('chicken_bets')
                                   ->whereIn('user_id', $userIds)
                                   ->where('game_id', 19)
                                   ->sum('amount'),
    
            'total_aviator_bet'=> DB::table('aviator_bet')
                                    ->whereIn('uid', $userIds)
                                    ->sum('amount'),
        ];
    
        return view('user.agent_user_details', compact(
            'users',
            'today_users',
            'deposits',
            'today_deposits',
            'withdraws',
            'today_withdraws',
            'summary',
            'wingo_history',
            'dragon_history',
            'aviator_history'
        ));
    }

    // public function agentUserDetails($agent_id)
    // {
    // // MLM multi-level users
    //     $allUsers = collect();
    //     $currentLevelIds = collect([$agent_id]);
    
    //     while (true) {
    //         $nextUsers = DB::table('users')
    //             ->whereIn('referral_user_id', $currentLevelIds)
    //             ->select('id','username','email','mobile','created_at','referral_user_id')
    //             ->get();
    
    //         if ($nextUsers->isEmpty()) break;
    
    //         $allUsers = $allUsers->merge($nextUsers);
    //         $currentLevelIds = $nextUsers->pluck('id');
    //     }
    
    //     $users = $allUsers;
    //     $userIds = $users->pluck('id');
    
    //     // Deposit History
    //     $deposits = DB::table('payins')
    //         ->whereIn('user_id', $userIds)
    //         ->orderBy('id', 'desc')
    //         ->get();
            
    //     $today_deposits = DB::table('payins')
    //         ->whereIn('user_id', $userIds)
    //         ->whereDate('created_at', Carbon::today())   // ⭐ सिर्फ आज की तारीख
    //         ->orderBy('id', 'desc')
    //         ->get();
    
    //     // Withdraw History
    //     $withdraws = DB::table('withdraw_histories')
    //         ->whereIn('user_id', $userIds)
    //         ->orderBy('id', 'desc')
    //         ->get();
            
    //          // Withdraw History
    //     $today_withdraws = DB::table('withdraw_histories')
    //         ->whereIn('user_id', $userIds)
    //         ->whereDate('created_at', Carbon::today())   // ⭐ सिर्फ आज की तारीख
    //         ->orderBy('id', 'desc')
    //         ->get();
    
    //     // ⭐ Wingo History (game_id 1,2,3,4)
    //     $wingo_history = DB::table('bets')
    //         ->whereIn('userid', $userIds)
    //         ->whereIn('game_id', [1,2,3,4])
    //         ->orderBy('id', 'desc')
    //         ->get();
    
    //     // ⭐ Dragon & Tiger History (game_id 5)
    //     $dragon_history = DB::table('chicken_bets')
    //         ->whereIn('user_id', $userIds)
    //         ->where('game_id', 19)
    //         ->orderBy('id', 'desc')
    //         ->get();
    
    //     // ⭐ Aviator History (aviator_bet table)
    //     $aviator_history = DB::table('aviator_bet')
    //         ->whereIn('uid', $userIds)
    //         ->orderBy('id', 'desc')
    //         ->get();
    
    //     // Summary (existing)
    //     $summary = [
    //         'total_users' => $users->count(),
    //         'total_deposit' => DB::table('payins')->whereIn('user_id', $userIds)->where('status', 2)->sum('cash'),
    //         'today_deposit' => DB::table('payins')->whereIn('user_id', $userIds)->where('status', 2)->sum('cash'),
    //         'total_withdraw' => DB::table('withdraw_histories')->whereIn('user_id', $userIds)->where('status', 2)->sum('amount'),
    //         'today_withdraw' => DB::table('withdraw_histories')->whereIn('user_id', $userIds)->where('status', 2)->sum('amount'),
    
    //         // ⭐ NEW Summary
    //         'total_wingo_bet' => DB::table('bets')
    //                             ->whereIn('userid', $userIds)
    //                             ->whereIn('game_id', [1,2,3,4])
    //                             ->sum('amount'),
    
    //         'total_dragon_bet' => DB::table('chicken_bets')
    //                             ->whereIn('user_id', $userIds)
    //                             ->where('game_id', 19)
    //                             ->sum('amount'),
    
    //         'total_aviator_bet' => DB::table('aviator_bet')
    //                             ->whereIn('uid', $userIds)
    //                             ->sum('amount'),
    //     ];
    
    //     return view('user.agent_user_details', compact('users', 'deposits','today_deposits', 'withdraws', 'today_withdraws','summary', 'wingo_history', 'dragon_history', 'aviator_history'));
    // }

    public function Agent()
    {
        $agents = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.role_id', 4)
            ->select('users.*', 'roles.name as role_name') // 'name' is role name column in roles table
            ->get();
    
        return view('aget')->with('agents', $agents);
    }

    public function agentStore(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required|min:8',
        ]);
        
        // Generate u_id → WB + 5 random digits
        $u_id = 'WB' . rand(10000, 99999);

        // Generate referral_code → WB + 8 random uppercase letters
        $referral_code = 'WB' . strtoupper(Str::random(8));

        try {
            DB::table('users')->insert([
        'username' => $request->username,
        'password' => $request->password,
        'email'    => $request->email,
        'mobile'   => $request->mobile,
        'country_code' => "+91",
        'role_id'  => 4, // agent role
        'status'   => 1, // default active
        'verification'   => 2, // default active
        'u_id'          => $u_id,
        'referral_code' => $referral_code,
        'userimage'=> "https://root.winbhai.in/uploads/profileimage/1.png",
        'created_at' => now(),
        'updated_at' => now(),
    ]);

            return redirect()->back()->with('success', 'Agent created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create agent: ' . $e->getMessage());
        }
    }

    public function agentUpdate(Request $request)
    {
        $validated = $request->validate([
            'user_id'  => 'required|exists:users,id',
            'username' => 'required|string|max:255',
            'email'    => 'required|email',
            'mobile'   => 'required|string|max:15',
            'password' => 'required|min:8',
        ]);
    
        
        $updated = DB::table('users')->where('id', $request->user_id)->update([
            'username' => $request->username,
            'password' => $request->password,
            'email'    => $request->email,
            'mobile'   => $request->mobile,
            'updated_at' => now(),
        ]);
    
        return $updated
            ? redirect()->back()->with('success', 'Changes saved successfully!')
            : redirect()->back()->with('error', 'No changes made or agent not found.');
    }

    public function agentstatus(Request $request)
    {
        $validated = $request->validate([
            'id'     => 'required|exists:users,id',
            'status' => 'required|in:1,0',
        ]);

        try {
            DB::table('users')
                ->where('id', $request->id)
                ->where('role_id', 4)
                ->update([
                    'status'     => $request->status,
                    'updated_at' => now(),
                ]);

            return redirect()->back()->with('success', 'Status updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    public function agentPermission(Request $request)
    {
        $user_id = $request->user_id;
    
        // Get the user whose permission needs to be managed
        $user = User::where('role_id', 4)->where('id', $user_id)->first();
    
        if (!$user) {
            return redirect()->back()->with('error', 'User not found or not an agent.');
        }
    
        // Fetch all permissions except ID 13
        $permissions = DB::table('permissions')
            ->select('id', 'name')
            ->whereNotIn('id', [13])->where('status', '!=', 0)  
            ->get();
    
        return view('role.adminrole', [
            'user' => $user,
            'permissions' => $permissions,
            'id' => $user_id
        ]);
    }


}
