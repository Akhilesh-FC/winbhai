<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversionController extends Controller
{
    // Display the conversion rate UI
    public function index()
    {
        $conversion = DB::table('payment_limits')->where('id', 14)->first();
        return view('usdt_qr.index', compact('conversion'));
    }

    // Update the conversion rate
    public function update(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        DB::table('payment_limits')
            ->where('id', 14)
            ->update([
                'amount' => $request->amount,
                'updated_at' => now(),
            ]);

        return redirect()->route('usdt_conversion.index')->with('success', 'USDT Withdraw Conversion Rate Updated Successfully!');
    }
    
    
    
    
    
    
}



