<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgetController extends Controller
{
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

        try {
            DB::table('users')->insert([
        'username' => $request->username,
        'password' => $request->password,
        'email'    => $request->email,
        'mobile'   => $request->mobile,
        'role_id'  => 4, // agent role
        'status'   => 1, // default active
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
        ->whereNotIn('id', [13])
        ->get();

    return view('role.adminrole', [
        'user' => $user,
        'permissions' => $permissions,
        'id' => $user_id
    ]);
}


}
