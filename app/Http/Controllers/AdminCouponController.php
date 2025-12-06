<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    
     public function game_slider_show()
    {
        $sliders = DB::table('game_subcat_sliders')->get();
        return view('game_silder_img.index', compact('sliders'));
    }

    // UPDATE IMAGE
    public function game_slider_update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'image' => 'required|image|mimes:jpg,jpeg,png'
        ]);

        $id = $request->id;

        // Old image fetch
        $old = DB::table('game_subcat_sliders')->where('id', $id)->first();

        if (!$old) {
            return response()->json([
                'status' => 400,
                'message' => 'Slider not found'
            ]);
        }

        // Upload new image
        $imageName = time() . ".jpg";
        $request->image->move(public_path('game_subcat_sliders'), $imageName);

        // Update DB
        DB::table('game_subcat_sliders')->where('id', $id)->update([
            'image' => $imageName,
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Slider updated successfully',
            'image_url' => "https://root.winbhai.in/public/game_subcat_sliders/" . $imageName
        ]);
    }
    
    
    
    
    public function delete($id)
{
    // Check coupon exists
    $coupon = DB::table('coupons')->where('id', $id)->first();
    if (!$coupon) {
        return back()->with('error', 'Coupon not found.');
    }

    // Delete
    DB::table('coupons')->where('id', $id)->delete();

    // Also delete its history so no error comes
    DB::table('coupon_history')->where('coupon_id', $id)->delete();

    return back()->with('success', 'Coupon deleted successfully!');
}

    
    
    public function store(Request $request)
{
    $request->validate([
        'title' => 'required',
        'coupon_code' => 'required|unique:coupons,coupon_code',
        'percentage' => 'required|numeric|min:1',
        'description' => 'nullable'
    ]);

    DB::table('coupons')->insert([
        'title' => $request->title,
        'coupon_code' => strtoupper($request->coupon_code),
        'percentage' => $request->percentage,
        'description' => $request->description,
        'status' => 1,
        'use_limit_per_user' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    return back()->with('success', 'New Coupon added successfully!');
}

    
    
    public function index()
    {
        $coupons = DB::table('coupons')->orderBy('id', 'DESC')->get();
        return view('coupons.index', compact('coupons'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'coupon_code' => 'required',
            'percentage' => 'required|numeric|min:1',
            'description' => 'nullable'
        ]);

        DB::table('coupons')->where('id', $id)->update([
            'title' => $request->title,
            'coupon_code' => strtoupper($request->coupon_code),
            'percentage' => $request->percentage,
            'description' => $request->description,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Coupon updated successfully!');
    }

    public function toggleStatus($id)
    {
        $coupon = DB::table('coupons')->where('id', $id)->first();

        $newStatus = $coupon->status == 1 ? 0 : 1;

        DB::table('coupons')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Coupon status updated!');
    }
}
