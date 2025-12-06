<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class SettingController extends Controller
{
    public function setting_index()
    {
         $settings = DB::select("SELECT * FROM `settings` WHERE 1");
        return view('work_order_assign.setting', compact('settings'));
    }
    
     public function view($id)
    {
        //  $views = DB::select("SELECT * FROM `setting` WHERE `id`='$id'");
         $views = DB::table('settings')->where('id',$id)->first();
         
        return view('work_order_assign.view')->with('views',$views);
    }
    public function setting_update(Request $request,$id)
    {
        $discription=$request->description;
    
         $views = DB::table('settings')->where('id',$id)->update(['description' => $discription]);
        
  
        //  DB::update("UPDATE `setting` SET `disc`='$discription' WHERE id=$id");
         
             return redirect()->route('setting');
    }
	
public function support_setting()
{
    $settings = DB::select("SELECT * FROM customer_services");

    // ✅ NEW DATA (CONTACT TABLES)
    $contactUs = DB::table('contact_us')->first();
    $contactWithUs = DB::table('contact_with_us')->first();
    // NEW: WhatsApp Chat number
    $Chat_With_Us_view = DB::table('admin_settings')->where('id', 24)->first();

    return view(
        'work_order_assign.support_setting',
        compact('settings', 'contactUs', 'contactWithUs','Chat_With_Us_view')
    );
}

public function contactUsUpdate(Request $request, $id)
{
    DB::table('contact_us')
        ->where('id', $id)
        ->update([
            'contact' => $request->contact
        ]);

    return redirect()->back()->with('success', 'Contact number updated');
}


public function Need_Help_Chat_With_Us_view($id)
{
    $Chat_With_Us_view = DB::table('admin_settings')->where('id', 24)->first();
    return view('work_order_assign.support_setting', compact('Chat_With_Us_view'));
}


public function Need_Help_Chat_With_Us(Request $request, $id)
{
    DB::table('admin_settings')
        ->where('id', 24)
        ->update([
            'longtext' => $request->chat_on_whatsapp
        ]);

    return redirect()->back()->with('success', 'Whatsapp contact updated successfully');
}




public function contactWithUsUpdate(Request $request, $id)
{
    // Build full URLs
    $instagram = $request->instagram_username
        ? 'https://www.instagram.com/' . $request->instagram_username
        : null;

    $telegram = $request->telegram_username
        ? 'https://t.me/' . $request->telegram_username
        : null;

    $whatsapp = $request->whatsapp_number
        ? 'https://wa.me/' . $request->whatsapp_number
        : null;

    DB::table('contact_with_us')
        ->where('id', $id)
        ->update([
            'instagram_link' => $instagram,
            'telegram_link'  => $telegram,
            'whatsapp_link'  => $whatsapp
        ]);

    return redirect()->back()->with('success', 'Social links updated successfully');
}


    
    
	  public function supportsetting_update(Request $request,$id)
    {
        $socialmedia=$request->socialmedia;
    
         $views = DB::table('customer_services')->where('id',$id)->update(['link' => $socialmedia]);
        
  
        //  DB::update("UPDATE `setting` SET `disc`='$discription' WHERE id=$id");
         
             return redirect()->route('support_setting');
    }
    
	public function notification()
    {
			    
			
         $settings = DB::select("SELECT * FROM `notifications` WHERE `status`=1
        ");
        return view('work_order_assign.notification', compact('settings'));
    }
	
	public function view_notification($id)
    {
        //  $views = DB::select("SELECT * FROM `setting` WHERE `id`='$id'");
         $views = DB::table('notifications')->where('id',$id)->first();
         
        return view('work_order_assign.view_notification')->with('views',$views);
    }
	
	    public function notification_update(Request $request,$id)
        {
            $discription=$request->disc;
        
             $views = DB::table('notifications')->where('id',$id)->update(['disc' => $discription]);
            
      
      
             
                 return redirect()->route('notification');
        }
	
	
	    public function notification_store(Request $request)
    {
		$name=$request->name;	
        $discription=$request->disc;
     
         $query =  DB::table('notifications')->insert([
            'name' => $name,
            'disc' => $discription,
			 'status'=>1
        ]);
             return redirect()->route('notification');
    }
	
	    public function add_notification()
    {
			    
			
         $settings = DB::select("SELECT * FROM `notifications` WHERE `status`=1
");
        return view('work_order_assign.add_notification');
    }
	
	
public function gameList()
{
    $games = DB::table("game_settings")
                ->select('name')
                ->groupBy('name')
                ->get();

    return view('games.gamelist', compact('games'));
}

}
