<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use DB;
use Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class LoginController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function register_create()
    {
        return view ('admin.register');
    }
    
    public function register_store(Request $request)
    {
         $request->validate([
             'name'   => 'required | max:45',
             'email'  => 'required',
             'mobile'  =>'required',
             'user_name' =>'required',
             'password'  =>'required',
                  
         ]);
        $data=[
             'name'=>$request->name,
             'email'=>$request->email,
             'mobile'=>$request->mobile,
             'user_name'=>$request->user_name,
             'password'=>$request->password,
             'status'=>1
             ];
 
             User::create($data);
            return redirect()->route('login');
        
      }
      
    public function login()
    {
        return view('admin.login');
    }

	
	
//     public function auth_login(Request $request) 
//     {
//         $request->validate([
//             'email'=>'required',
//             'password'=>'required',
		
//             ]);
//         //$login = DB::table('user')->where('email','=',$request['email'])->
//       // where('password','=', $request['password'])->first();
// 		$login = DB::table('users')->where('email','=',$request['email'])->
//         where('password','=', $request['password'])->where('verification','=','2')->where('role_id','=','1')->where('id','=','1')->first();
// 		// $otp=DB::table('otp_sms')->where('mobile','=','9167027770')->where('otp','=', $request['otp'])->first();
	
//         if($login == NULL)
//         {
		
//             session()->flash('msg_class','danger');
//             session()->flash('msg','The provided Admin do not match our records.');
//             return redirect()->route('login');
// 		}
			
// 		else{
// 			 $request->session()->put('id', $login->id);

//             return redirect()->route('dashboard'); 
// 			}
			 
//         }


    public function auth_login(Request $request) 
{
    $request->validate([
        'email' => 'required',
        'password' => 'required',
    ]);

    $login = DB::table('users')
        ->where('email', $request['email'])
        ->where('password', $request['password'])
        ->where('verification', '2')
        ->first();

    if ($login == NULL) {
        session()->flash('msg_class', 'danger');
        session()->flash('msg', 'The provided credentials do not match our records.');
        return redirect()->route('login');
    } else {

        // Store session
        $request->session()->put('id', $login->id);
        $request->session()->put('permissions', json_decode($login->permissions, true));

        // ===============================
        // ✔ If role_id = 4 then run agentUserDetails()
        // ===============================
        if ($login->role_id == 4) {

            // Call function (same controller)
            return $this->agentUserDetails($login->id);
        }

        // Other roles
        return redirect()->route('dashboard');
    }
}


    public function auth_login_old(Request $request) 
    {
       // dd($request);
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
    
        $login = DB::table('users')
            ->where('email', $request['email'])
            ->where('password', $request['password']) // ⚠️ Not secure, consider using Hash::check
            ->where('verification', '2')
            ->first();
    
        if ($login == NULL) {
            session()->flash('msg_class', 'danger');
            session()->flash('msg', 'The provided credentials do not match our records.');
            return redirect()->route('login');
        } else {
            // ✅ Store ID in session
            $request->session()->put('id', $login->id);
            
    
            // ✅ Decode permissions and store in session
            $permissions = json_decode($login->permissions, true); // assuming it's like ["1","2","3"]
              // dd($permissions);

            $request->session()->put('permissions', $permissions);
           // dd($request);
            
    
            return redirect()->route('dashboard'); 
        }
    }
    
    
    private function agentUserDetails($agent_id)
    {
        // MLM multi-level users
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
    
        // Deposit History
        $deposits = DB::table('payins')
            ->whereIn('user_id', $userIds)
            ->orderBy('id', 'desc')
            ->get();
    
        // Withdraw History
        $withdraws = DB::table('withdraw_histories')
            ->whereIn('user_id', $userIds)
            ->orderBy('id', 'desc')
            ->get();
    
        // ⭐ Wingo History (game_id 1,2,3,4)
        $wingo_history = DB::table('bets')
            ->whereIn('userid', $userIds)
            ->whereIn('game_id', [1,2,3,4])
            ->orderBy('id', 'desc')
            ->get();
    
        // ⭐ Dragon & Tiger History (game_id 5)
        $dragon_history = DB::table('bets')
            ->whereIn('userid', $userIds)
            ->where('game_id', 5)
            ->orderBy('id', 'desc')
            ->get();
    
        // ⭐ Aviator History (aviator_bet table)
        $aviator_history = DB::table('aviator_bet')
            ->whereIn('uid', $userIds)
            ->orderBy('id', 'desc')
            ->get();
    
        // Summary (existing)
        $summary = [
            'total_users' => $users->count(),
            'total_deposit' => DB::table('payins')->whereIn('user_id', $userIds)->where('status', 2)->sum('cash'),
            'total_withdraw' => DB::table('withdraw_histories')->whereIn('user_id', $userIds)->where('status', 2)->sum('amount'),
    
            // ⭐ NEW Summary
            'total_wingo_bet' => DB::table('bets')
                                ->whereIn('userid', $userIds)
                                ->whereIn('game_id', [1,2,3,4])
                                ->sum('amount'),
    
            'total_dragon_bet' => DB::table('bets')
                                ->whereIn('userid', $userIds)
                                ->where('game_id', 5)
                                ->sum('amount'),
    
            'total_aviator_bet' => DB::table('aviator_bet')
                                ->whereIn('uid', $userIds)
                                ->sum('amount'),
        ];
    
        return view('agent_panel', compact(
            'users',
            'deposits',
            'withdraws',
            'summary',
            'wingo_history',
            'dragon_history',
            'aviator_history'
        ));
    
    }

	
    
    
    	
	public function dashboard(Request $request) 
    {
    $userId = $request->session()->get('id');
//dd($userId);
    if (!empty($userId)) {
        date_default_timezone_set("Asia/Calcutta"); 
        $date = date('Y-m-d');

        $startdate = $request->input('start_date');
        $enddate = $request->input('end_date');

       

        if (empty($startdate) && empty($enddate)) {
             $users = DB::select("SELECT
    (SELECT COUNT(id) FROM users) as totaluser,
	 (SELECT COUNT(id) FROM users WHERE users.created_at LIKE '$date%' ) as todayuser,
	(select count(id) from users where users.status='1')as activeuser,
    (SELECT COUNT(id) FROM game_settings WHERE game_settings.status = 0) as totalgames,
    (SELECT COUNT(id) FROM bets) as totalbet,
    (SELECT COUNT(id) FROM feedbacks) as totalfeedback,
    (SELECT SUM(cash) FROM payins  WHERE status='2') as totaldeposit,
  COALESCE  ((SELECT SUM(amount) FROM withdraw_histories WHERE withdraw_histories.status = 2 AND withdraw_histories.created_at LIKE '$date%'),0)as tamount,
  COALESCE  ((SELECT SUM(amount) FROM withdraw_histories WHERE withdraw_histories.status = 2 ),0)as totalwithdraw,
    COALESCE((SELECT SUM(cash) FROM payins WHERE status = '2' AND payins.created_at LIKE '$date%'), 0) as tdeposit,
   SUM(commission) as commissions,
    COALESCE( (SELECT SUM(amount) FROM `bets` WHERE bets.created_at LIKE '$date%'),0 )as todayturnover,
    COUNT(id) as users,
    (SELECT SUM(amount) FROM `bets`) as total_turnover
FROM users;");
			
        } else {
            $users = DB::select("
                SELECT
                    (SELECT COUNT(id) FROM users WHERE created_at BETWEEN '$startdate' AND '$enddate') as totaluser,
					(SELECT COUNT(id) FROM users WHERE users.created_at LIKE '$date%' ) as todayuser,
					(select count(id) from users where created_at BETWEEN '$startdate' and '$enddate' and users.status='1')as activeuser,
                    (SELECT COUNT(id) FROM game_settings WHERE created_at BETWEEN '$startdate' AND '$enddate') as totalgames,
                    (SELECT COUNT(id) FROM bets WHERE created_at BETWEEN '$startdate' AND '$enddate') as totalbet,
                    (SELECT COUNT(id) FROM feedbacks WHERE created_at BETWEEN '$startdate' AND '$enddate') as totalfeedback,
                    COALESCE((SELECT SUM(cash) FROM payins WHERE status = 2 AND DATE(created_at) BETWEEN '$startdate' AND '$enddate'), 0) as totaldeposit,
                    COALESCE((SELECT SUM(amount) FROM withdraw_histories WHERE status = 2 AND DATE(created_at) BETWEEN '$startdate' AND '$enddate'), 0) as tamount,
                    COALESCE((SELECT SUM(amount) FROM withdraw_histories WHERE status = 2), 0) as totalwithdraw,
                    COALESCE((SELECT SUM(cash) FROM payins WHERE status = 2 AND DATE(created_at) BETWEEN '$startdate' AND '$enddate'), 0) as tdeposit,
                    SUM(commission) as commissions,
                    COALESCE((SELECT SUM(amount) FROM `bets` WHERE bets.created_at LIKE '$date%'), 0) as todayturnover,
                    COUNT(id) as users,
                    SUM(turnover) as total_turnover
                FROM users
                WHERE created_at BETWEEN '$startdate' AND '$enddate'
            ");
        }
	//	dd($users);
		
        session()->flash('msg_class','success');
        session()->flash('msg','Login Successfully ..!');
        return view('admin.index', ['users' => $users]);
    } else {
        return redirect()->route('login');  
    }
}

    public function logout(Request $request): RedirectResponse
    {
        
           $request->session()->forget('id');
		 session()->flash('msg_class','success');
            session()->flash('msg','Logout Successfully ..!');
     
         return redirect()->route('login')->with('success','Logout Successfully ..!');
    }
	
	public function password_index()
    {
        return view('change_password');
    }
	
    public function password_change(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
        'npassword' => 'required|min:6',
    ]);

    if ($validator->fails()) {
        return redirect()->route('change_password')
            ->withErrors($validator)
            ->withInput();
    }

    $user = DB::table('users')->where('email', $request->input('email'))->first();

    if ($user) {
        if ($request->input('password') === $user->password) {
            DB::table('users')
                ->where('email', $request->input('email'))
                ->update(['password' => $request->input('npassword')]);

            // Session clear and logout
            $request->session()->forget('id'); // remove user session
            $request->session()->flush(); // optional: clear all session data

            // Flash logout message
            session()->flash('msg_class', 'success');
            session()->flash('msg', 'Password changed successfully. Please login again.');

            // Redirect to login
            return redirect()->route('login');
        } else {
            session()->flash('msg_class', 'danger');
            session()->flash('msg', 'Current password is incorrect.');
        }
    } else {
        session()->flash('msg_class', 'danger');
        session()->flash('msg', 'The provided email does not match our records.');
    }

    return redirect()->route('change_password')->withInput();
}

}
