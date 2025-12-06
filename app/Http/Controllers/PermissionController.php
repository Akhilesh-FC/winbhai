<?php

namespace App\Http\Controllers;

use App\Models\PermissionsUser;
use Illuminate\Http\Request;
use DB;
class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

  

// <!------------old code---------------------->
public function store(Request $request)
{
    
    // dd($request);
    // Static role mapping
    $roleMap = [
      
        'Agents' => 2,
    ];

    foreach ($roleMap as $roleKey => $roleId) {
        if ($request->has($roleKey)) {
            $permissions = $request->input($roleKey, []);
            $jsonPermissions = json_encode($permissions); // Will be like: [1,2,3,4,5]

         // Update users table where role_id matches
            DB::table('users')->where('role_id', $roleId)->update([
                'permissions' => $jsonPermissions
            ]);
        }
    }

    return redirect()->back()->with('success', 'Permissions updated successfully!');
}



 
    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        //
    }
}
