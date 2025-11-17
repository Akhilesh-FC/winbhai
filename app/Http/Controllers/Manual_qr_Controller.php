<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use  Illuminate\Support\Facades\DB;

class Manual_qr_Controller extends Controller
{
    
    
    
    public function updateStatus(Request $request, $id)
    {
        $status = $request->status;
        DB::table('qr_codes')->where('id', $id)->update(['status' => $status]);
        return response()->json(['success' => true]);
    }

    public function manual_qr_view()
    {
       $manual = DB::select("SELECT * FROM `qr_codes`");
        return view('manual_qr.index', compact('manual'));
    }

    public function update_manual_qr(Request $request, $id)
    {
        // ✅ Simple validation (no MIME guessing)
        $request->validate([
            'image' => 'required|file|max:2048',
            'wallet_address' => 'required|string'
        ]);
    
        $image = $request->file('image');
        $wallet_address = $request->wallet_address;
    
        // ✅ Define upload path
        $uploadPath = public_path('uploads/payinqr');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
    
        // ✅ Generate unique name and move file
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->move($uploadPath, $imageName);
    
        // ✅ Create public URL
        $imageUrl = "https://root.winbhai.in/public/uploads/payinqr/" . $imageName;
    
        // ✅ Update database
        DB::table('qr_codes')->where('id', $id)->update([
            'qr_code' => $imageUrl,
            'wallet_address' => $wallet_address
        ]);
    
        return redirect()->back()->with('message', 'QR Updated Successfully!');
    }




 
}