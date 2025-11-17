<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    public function auth_index(){
        // dd("dfgthyujikol");
        return view('auth.index');
    }
    
    
    public function AuthLogin(Request $request) 
    {
        
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
            
    
            return redirect()->route('dashboard'); 
        }
    }


    public function AuthLogin_old(Request $request): RedirectResponse
    {
        try {
            dd($request);
            // ✅ Validate request
            $credentials = $request->validate([
                'username' => ['required'],
                'password' => ['required'],
            ]);
    
            // ✅ Fetch user
            $user = User::where('username', $credentials['username'])
                        ->whereIn('role_id', [1, 2])
                        ->first();
    
            // ✅ Check password (plain text match, not secure!)
            if ($user && $user->password == $credentials['password']) {
                Auth::login($user);
                $request->session()->regenerate();
    
                // 🟢 Store user data in session
                session()->put('user_id', $user->id);
                session()->put('role_id', $user->role_id);
                session()->put('parent_id', $user->parent_id);
    
                return redirect()->intended('dashboard');
            }
    
            // ❌ Invalid credentials
            return back()->withErrors([
                'username' => 'The provided credentials do not match our records.',
            ])->onlyInput('username');
    
        } catch (\Throwable $e) {
            // ✅ Log error into storage/logs/laravel.log
            Log::error('AuthLogin Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'username' => $request->input('username')
            ]);
    
            return back()->withErrors([
                'username' => 'Something went wrong. Please try again later.',
            ])->onlyInput('username');
        }
    }

    // public function AuthLogin(Request $request): RedirectResponse
    // {
    //     $credentials = $request->validate([
    //         'username' => ['required'],
    //         'password' => ['required'],
    //     ]);
    
    //     $user = User::where('username', $credentials['username'])
    //                 ->whereIn('role_id', [1, 2])
    //                 ->first();
    
    //     if ($user && $user->password == $credentials['password']) {
    //         Auth::login($user);
    //         $request->session()->regenerate();
    
    //         // 🟢 Store user data in session
    //         session()->put('user_id', $user->id);
    //         session()->put('role_id', $user->role_id);
    //         session()->put('parent_id', $user->parent_id);
    
    //         return redirect()->intended('dashboard');
    //     }
    
    //     return back()->withErrors([
    //         'username' => 'The provided credentials do not match our records.',
    //     ])->onlyInput('username');
    // }

    public function AuthLogout(Request $request): RedirectResponse
    {
        Auth::logout(); 
        $request->session()->invalidate(); 
        $request->session()->regenerateToken(); 
        return redirect('/');
    }

    // Change_Password
    public function ChangePasswordIndex()
    {
            $user = Auth::user(); 
            return view('changePassword.index')->with('user',$user);
        }
        
        
    public function ChangePassword(Request $request)
    { 
        $validated = $request->validate([
            'username' => 'required',
            'old_password' => 'required|exists:users,password',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
        ]);

        User::where('username', $request->username)
          ->where('password', $request->old_password)
          ->update(['password' => $request->password]); 
           return redirect()->back()->with('success','Change Password Successfully..!');
    }
}
