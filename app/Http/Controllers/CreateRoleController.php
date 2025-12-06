<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
// use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Validator; 
class CreateRoleController extends Controller{
    
//   public function createrole(){
//       $loggedInUser = Auth::user();
//       $Auth_role = $loggedInUser->role_id;
//       $Auth_id = $loggedInUser->id;
//       //dd($role_id);
//       $permissions = DB::table('permissions')
//         ->select('id', 'name')
//         ->whereNotIn('id', [13])   // id 13 ko exclude kar diya
//         ->get();
//       $roles = DB::table('roles')->select('id','name')->whereIn('id', [4,5])->get();
//      // dd($roles);
//       return view('role.createrole',compact('permissions','roles','Auth_role','Auth_id'));
//   }
public function createrole()
{
    $loggedInUser = Auth::user(); // login user ho to milega, warna null 
    // Agar login nahi hai to dono variable null honge
    $Auth_role = $loggedInUser->role_id ?? null;
    $Auth_id = $loggedInUser->id ?? null;

    $permissions = DB::table('permissions')
        ->select('id', 'name')
        ->whereNotIn('id', [13])->where('status', '!=', 0)  
        ->get();

    $roles = DB::table('roles')
        ->select('id', 'name')
        ->whereIn('id', [4])
        ->get();

    return view('role.createrole', compact('permissions', 'roles', 'Auth_role', 'Auth_id'));
}


	public function store_permissions(Request $request)
{
    // ✅ Step 1: Validation with all necessary rules
    $validatedData = $request->validate([
        'username' => 'required|unique:users,username',
        'mobile' => 'required|digits:10|unique:users,mobile',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:6',
        'role_id' => 'required|exists:roles,id',
    ], [
        // ✅ Custom error messages (optional but helpful)
        'username.unique' => 'This username is already taken.',
        'mobile.unique' => 'This mobile number is already registered.',
        'email.unique' => 'This email address is already registered.',
        'mobile.digits' => 'Mobile number must be exactly 10 digits.',
    ]);

    // ✅ Step 2: Prepare user data
    $role_id = $request->role_id;
    $name = $request->username;
    $email = $request->email;
    $mobile = $request->mobile;
    $password = $request->password;
    $permissionsArray = $request->input('permissions');
    $auth_id = $request->input('auth_id');
    $permissionsJson = json_encode($permissionsArray);
    $now = Carbon::now('Asia/Kolkata');

    // ✅ Step 3: Insert data
    $userData = [
        'role_id' => $role_id,
        'username' => $name,
        'email' => $email,
        'mobile' => $mobile,
        'password' => $password, // aap bcrypt nahi use karte — isliye plain rakha
        'permissions' => $permissionsJson,
        'verification' => 2,
        'status' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ];

    try {
        $insert = DB::table('users')->insert($userData);

        if ($insert) {
            return back()->with('message', 'Created successfully.');
        } else {
            return back()->with('fail', 'Something went wrong.');
        }

    } catch (\Illuminate\Database\QueryException $e) {
        // ✅ Handle duplicate entry or DB error gracefully
        if ($e->getCode() == 23000) {
            return back()->with('fail', 'Duplicate entry found: Mobile or Email already exists.');
        }
        return back()->with('fail', 'Database error: ' . $e->getMessage());
    }
}


public function admin_role($id){ 
    $query = User::where('role_id', $id);
    $users = $query->orderBy('id', 'desc')->paginate(10);
    $permissions = DB::table('permissions')->select('id','name')->whereNotIn('id', [13])->get();
    return view('role.adminrole')
        ->with('user', $users)
        ->with('permissions', $permissions)
        ->with('id', $id);
}

public function update_permission(Request $request, $id)
{ 
    DB::table('users')
        ->where('id', $id)
        ->update([
            'permissions' => json_encode($request->permissions)
        ]);
      return back()->with('success', 'Permission Updated');
   }
}