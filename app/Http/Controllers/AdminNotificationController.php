<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller; // Add this line
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AdminNotificationController extends Controller
{

  public function sendNotification(Request $request)
{
    logger('✅ Notification API HIT');
    logger($request->all());

    $validator = Validator::make($request->all(), [
        'purpose' => 'required|string',
        'content' => 'required|string',
        'send_to' => 'required|in:single,all',
        'user_id' => 'nullable|integer'
    ]);

    if ($validator->fails()) {
        logger('❌ Validation failed');
        logger($validator->errors());

        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    DB::table('notification_admin')->insert([
        'user_id' => $request->send_to == 'single' ? $request->user_id : null,
        'purpose' => $request->purpose,
        'content' => $request->content,
        'send_to' => $request->send_to,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    logger('✅ DB INSERT SUCCESS');

    return response()->json([
        'status' => true,
        'message' => 'Notification stored successfully'
    ]);
}
    // SHOW ADMIN UI
    public function index()
    {
        $users = DB::table('users')->select('id','username')->get();
        return view('notification_admin.index', compact('users'));
    }

   
}