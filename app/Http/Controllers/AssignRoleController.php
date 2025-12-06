<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use DB;
use App\Models\{Permission,PermissionsUser};

class AssignRoleController extends Controller
{
//     public function assignRole_index()
//     {
//     $permission = Permission::where('status', 1)->get(); 
//     $permissionsData = [
//         // 'SuperStokez' => json_decode(DB::table('users')->where('role_id', 3)->value('permissions'), true) ?? [],
//         // 'Stokez' => json_decode(DB::table('users')->where('role_id', 2)->value('permissions'), true) ?? [],
//         'Agents' => json_decode(DB::table('users')->where('role_id', 1)->value('permissions'), true) ?? [],
//     ];

//     return view('AssignRole.index', compact('permission', 'permissionsData'));
// }

    public function assignRole_index()
    {
        // Active permissions list
        $permission = Permission::where('status', 1)->get(); 
       // dd($permission);
    
        // Sirf Agents (role_id = 2) ke liye permissions nikalna
        // $permissionsData = json_decode(
        //     DB::table('users')->where('role_id', 2)->value('permissions'),
        //     true
        // ) ?? [];
        
         $permissionsData = [
            
            'Agents' => json_decode(DB::table('users')->where('role_id', 2)->value('permissions'), true) ?? [],
        ];
    
    //dd($permissionsData);
    
        return view('AssignRole.index', compact('permission', 'permissionsData'));
    }

  
    public function agentStore(Request $request)
    {
  // dd($request->all());
    $validated = $request->validate([
        'username' => 'required',
        'password' => 'required',
        'name' => 'required',
        'revenue' => 'required',
        'type' => 'required',
        'parent_id' => 'required', // 👈 validation add
    ]);

    try {
       // dd('Inside try block');
        $permissionUser = DB::table('users')
            ->where('role_id', 2)
            ->whereNotNull('permissions')
            ->first();

        // If exists, assign same permissions, else use default empty
        $assign_per = $permissionUser ? $permissionUser->permissions : json_encode([]);
       // dd($permissionUser);

        $data = [
            'username'   => $request->username,
            'password'   => $request->password,
            'name'       => $request->name,
            'revenue'    => $request->revenue,
            'type'       => $request->type,
            'permissions'=> $assign_per,
            'parent_id'  => $request->parent_id, // 👈 ab request se liya
            'role_id'    => 2,
        ];
       


        //User::create($data);
        DB::table('users')->insert($data);
        
        return redirect()->back()->with('success', 'User created successfully!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to create user: ' . $e->getMessage());
    }
}
    
}
