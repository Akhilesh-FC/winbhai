<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use DB;
use Illuminate\Support\Str;
use App\Models\All_image;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
    public function revenue_show()
    {
        $data = DB::table('revenue')->select('id', 'revenue')->get();

        return view('revenues.index', compact('data'));
    }
    // Update Revenue
    public function revenue_update(Request $request)
    {
        $request->validate([
            'id'      => 'required|integer|exists:revenue,id',
            'revenue' => 'required|numeric'
        ]);

        DB::table('revenue')->where('id', $request->id)->update([
            'revenue'    => $request->revenue,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Revenue updated successfully!');
    }
    
    public function paymode_show()
    {
        $payModes = DB::table('pay_modes')->select('id', 'name', 'image', 'status', 'type')->get();
        // ✅ WhatsApp Deposit Number (id = 17)
         $whatsappDeposit = DB::table('admin_settings')
            ->where('id', 17)
            ->value('longtext');
    
        return view('paymode.index', compact('payModes', 'whatsappDeposit'));
    }
    
    public function paymode_updateImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);
    
        // NEW FILE NAME
        $imageName = time() . '.' . $request->image->extension();
    
        // MOVE FILE
        $request->image->move(public_path('uploads/paymodes'), $imageName);
    
       // STATIC BASE URL
        $baseUrl = "https://root.winbhai.in";
    
        // FULL URL
        $fullUrl = $baseUrl . '/uploads/paymodes/' . $imageName;
        // UPDATE DB
        DB::table('pay_modes')->where('id', $id)->update([
            'image' => $fullUrl
        ]);
    
        return back()->with('success', 'Image updated successfully!');
    }
    
    public function updateWhatsappDeposit(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required'
        ]);
    
        DB::table('admin_settings')
            ->where('id', 17)
            ->update([
                'longtext' => $request->whatsapp_number
            ]);
    
        return back()->with('success', 'WhatsApp deposit number updated successfully!');
    }


    public function updateNotice(Request $request)
    {
        $id = $request->id;
        $updateData = [
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'updated_at' => now(),
        ];
    
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/notice'), $filename);
            $updateData['image'] = $filename;
        }
    
        DB::table('Notice')->where('id', $id)->update($updateData);
    
        return redirect()->back()->with('success', 'Notice updated successfully!');
    }

    public function showSponser()
    {
        $notices = DB::table('Notice')->orderBy('id', 'DESC')->get();
        return view('sponser.index', compact('notices'));
    }
    
    public function toggleSponserStatus($id)
    {
        $notice = DB::table('Notice')->where('id', $id)->first();
    
        if ($notice) {
            $newStatus = $notice->status == 1 ? 0 : 1;
            DB::table('Notice')->where('id', $id)->update(['status' => $newStatus]);
            return redirect()->back()->with('success', 'Notice status updated successfully!');
        }
    
        return redirect()->back()->with('error', 'Notice not found.');
    }
    
    public function deleteSponser($id)
    {
        DB::table('Notice')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Notice deleted successfully!');
    }

    public function paymentLimitsList(Request $request)
    {
        if ($request->session()->has('id')) {
            // ✅ Fetch paginated records (10 per page)
            $paymentLimits = DB::table('payment_limits')
                ->select('id', 'name', 'amount', 'status', 'created_at')
                ->orderBy('id', 'DESC')
                ->paginate(5); // You can change 10 → any number
    
            return view('paymentLimitsList.index', compact('paymentLimits'));
        } else {
            return redirect()->route('login');
        }
    }

    public function updatePaymentLimit(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
        ]);
    
        DB::table('payment_limits')
            ->where('id', $request->id)
            ->update([
                'amount' => $request->amount,
                'updated_at' => now(),
            ]);
    
        return response()->json([
            'status' => true,
            'message' => 'Payment limit updated successfully!',
        ]);
    }
    
    public function campaignList(Request $request)
    {
        if ($request->session()->has('id')) {
    
            $query = DB::table('campaigns')
                ->select('id', 'user_id', 'campaign_name', 'unique_code', 'referral_link', 'created_at')
                ->orderBy('id', 'DESC');
    
            // 🔍 Search filter
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('campaign_name', 'LIKE', "%{$search}%")
                      ->orWhere('unique_code', 'LIKE', "%{$search}%")
                      ->orWhere('referral_link', 'LIKE', "%{$search}%")
                      ->orWhere('user_id', 'LIKE', "%{$search}%");
                });
            }
    
            $campaigns = $query->paginate(5);
    
            // Data Processing
            foreach ($campaigns as $campaign) {
    
                // ⏺ Total players (users registered by this referral code)
                $playerCount = DB::table('users')
                    ->where('referral_code', $campaign->unique_code)
                    ->count();
    
                $campaign->players = $playerCount;
    
                // ⏺ Affiliation Percentage
                if ($playerCount <= 2) {
                    $campaign->affiliation_percentage = "10%";
                } elseif ($playerCount <= 10) {
                    $campaign->affiliation_percentage = "20%";
                } else {
                    $campaign->affiliation_percentage = "30%";
                }
    
               $createdBy = DB::table('users')
            ->where('id', $campaign->user_id)
            ->select('username', 'mobile')
            ->first();
        
        $campaign->created_by = $createdBy->username ?? 'Unknown';
        $campaign->created_by_mobile = $createdBy->mobile ?? 'N/A';

            }
    
            $campaigns->appends($request->only('search'));
    
            return view('campaigns.index', compact('campaigns'));
    
        } else {
            return redirect()->route('login');
        }
    }

    public function demoUser(Request $request)
    {
        if ($request->session()->has('id')) {
            $demo_users = DB::select(" SELECT * FROM `users` WHERE `account_type`=1");
    
            return view('user.demo_user', compact('demo_users'));
        } else {
            return redirect()->route('login');
        }
    }
    
    private function generateNumericCode($length = 13)
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }

    private function NumericCode($length = 8) 
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }

    public function store(Request $request)
    {
       // dd($request);
        // Step 1: Validate Request Data
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required',
            'password' => 'required|min:6',
        ]);
        
        // Step 2: Generate Required Data
        $randomName = 'User_' . strtoupper(Str::random(5));
        $randomReferralCode = $this->generateNumericCode(13);
        $baseUrl = URL::to('/');
        $uid = $this->NumericCode(8);
        $randomNumber = rand(1, 20);
    
        // Step 3: Prepare User Data
        $data = [
            'username' => $randomName,
            'name' => $randomName,
            'u_id' => $uid,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'password' => $request->password,
            'userimage' => $baseUrl . "/uploads/profileimage/" . $randomNumber . ".png",
            'status' => 1,
            'referral_code' => $randomReferralCode,
            'wallet' => 0,
            'account_type'=>1,
            'country_code' => $request->country_code ?? '', // Default to empty if not provided
            'created_at' => now(),
            'updated_at' => now(),
        ];
    
        // Step 4: Add Referrer
        if ($request->filled('referral_code')) {
            $referrer = DB::table('users')->where('referral_code', $request->referral_code)->first();
            $data['referral_user_id'] = $referrer ? $referrer->id : null;
        } else {
            $data['referral_user_id'] = 1;
        }
    
        // Step 5: Store User Data
        DB::table('users')->insert($data);
    
        // Step 6: Redirect with Success Message
        return redirect()->route('register.create')->with('success', 'User registered successfully!');
    }
        
    public function user_create_old(Request $request)
    {
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {

			 $users = DB::select("SELECT e.*, m.username AS sname FROM users e LEFT JOIN users m ON e.referral_user_id = m.id; ");
		
		//$users = DB::table('user')->latest()->get();
        
        return view ('user.index', compact('users'));
        }
        else
        {
           return redirect()->route('login');  
        }
        
    }
    
    
    public function user_create(Request $request)
    {
        // Session se user id
        $user_id = $request->session()->get('id');
    	
    		 // Agar login nahi hai
        if (empty($user_id)) {
            return redirect()->route('login');
        }
        // User ka role_id DB se nikaalna
        $user = DB::table('users')->where('id', $user_id)->first();
        $role_id = $user->role_id ?? null;
    
        // Debug
        // dd($role_id);
    
        // ---- Role Check START ----
        if ($role_id == 4) {
            // Agar role_id = 4 → Agent specific data
            $users = $this->agentUserDetails($user_id);
    		//dd($user_id);
            return view('agent_player', compact('users', 'role_id', 'user_id'));
            //return view('user.index', compact('users'));
        } 
        else {
            // Agar koi aur role → Normal users list
            $users = DB::select("
                SELECT e.*, m.username AS sname 
                FROM users e 
                LEFT JOIN users m ON e.referral_user_id = m.id
            ");
    
            return view('user.index', compact('users', 'role_id', 'user_id'));
        }
    	 
        // ---- Role Check END ----
    }
	
	private function agentUserDetails($agent_id)
    {
        // MLM multi-level users
        $allUsers = collect();
        $currentLevelIds = collect([$agent_id]);
    
        while (true) {
            $nextUsers = DB::table('users')
                ->whereIn('referral_user_id', $currentLevelIds)
                ->select('id','username','email','mobile','created_at','referral_user_id')
                ->get();
    
            if ($nextUsers->isEmpty()) break;
    
            $allUsers = $allUsers->merge($nextUsers);
            $currentLevelIds = $nextUsers->pluck('id');
        }
    
        $user = $allUsers;
    		//dd($user);
        $userIds = $user->pluck('id');
    		
    		 return $user; 
    }

	
    
    
	
    public function user_details(Request $request,$id)
    {
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
        $users = DB::select("SELECT * FROM `bets` WHERE `userid`='$id' ");
        $withdrawal = DB::select("SELECT * FROM `withdraw_histories` WHERE `user_id`='$id' ");
        $dipositess = DB::select("SELECT * FROM `payins` WHERE `user_id`='$id' ");
       return view ('user.user_detail',compact('dipositess','users','withdrawal')); 
			  }
        else
        {
           return redirect()->route('login');  
        }
    }

    public function user_active(Request $request,$id)
    {
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
    //   Order::where("id",$id)->update(['status'=>0]);
    DB::update("UPDATE `users` SET `status`='1' WHERE id=$id;");
        
        return redirect()->route('users');
			  }
        else
        {
           return redirect()->route('login');  
        }
    }
	
    public function user_inactive(Request $request,$id)
    {
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
    //   Order::where("id",$id)->update(['status'=>1]);
      DB::update("UPDATE `users` SET `status`='0' WHERE id=$id;");
        return redirect()->route('users');
			  }
        else
        {
           return redirect()->route('login');  
        }
  }

    public function remark_update(Request $request, $id)
    {
        if ($request->session()->has('id')) {
    
            $remark = $request->remark;
    
            DB::update(
                "UPDATE users SET remark = ? WHERE id = ?",
                [$remark, $id]
            );
    
            return redirect()->route('users')
                ->with('success', 'Remark updated successfully!');
        } 
        else {
            return redirect()->route('login');
        }
    }



	public function password_update(Request $request, $id)
    {
		 $value = $request->session()->has('id');
	
        if(!empty($value))
        {
        $password=$request->password;
               $data= DB::update("UPDATE `users` SET `password`='$password' WHERE id=$id");
         
             return redirect()->route('users')->with('success', 'Password updated successfully!');
			  }
        else
        {
           return redirect()->route('login');  
        }
          
      }
      
    public function refer_id_store(Request $request ,$id)
    {
		date_default_timezone_set('Asia/Kolkata');
		$date=date('Y-m-d H:i:s');
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
      $refer=$request->referral_user_id;
     //dd($wallet);
         $data = DB::update("UPDATE `users` SET `referral_user_id` = $refer WHERE id = $id;");
			
             return redirect()->route('users');
			  }
        else
        {
           return redirect()->route('login');  
        }
      }

    public function wallet_store(Request $request, $id)
    {
        date_default_timezone_set('Asia/Kolkata');
        $date = date('Y-m-d H:i:s');
        $value = $request->session()->has('id');

        if (!empty($value)) {
            $wallet = $request->wallet;

            if (empty($wallet) || !is_numeric($wallet) || $wallet <= 0) {
                return redirect()->back()->with('error', 'Invalid wallet amount.');
            }

            // Update wallet
            DB::update("
                UPDATE users 
                SET 
                    wallet = wallet + ?,
                    deposit_balance = deposit_balance + ?,
                    total_payin = total_payin + ?
                WHERE id = ?
            ", [$wallet, $wallet, $wallet, $id]);

            // Insert record into payins table
            DB::insert("
                INSERT INTO payins(user_id, cash, order_id, type, status, created_at) 
                VALUES (?, ?, 'via Admin', '2', '2', ?)
            ", [$id, $wallet, $date]);

            return redirect()->back()->with('success', 'Amount added successfully!');
        } else {
            return redirect()->route('login');
        }
    }

    public function wallet_subtract(Request $request, $id)
    {
        date_default_timezone_set('Asia/Kolkata');
         $date = date('Y-m-d H:i:s');
        $amount = $request->wallet;

        if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
            return redirect()->back()->with('error', 'Invalid wallet amount.');
        }

        // Retrieve the user
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        if ($user->wallet < $amount) {
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }

        // Subtract the amount
        $user->wallet -= $amount;
        $user->save();

        // // Optional: Record transaction
        DB::insert("
            INSERT INTO `withdraw_histories`(`user_id`, `amount`, `type`, `status`, `created_at`) 
            VALUES (?, ?, 'Admin Subtract', '2', ?)
        ", [$id, $amount, $date]);

        return redirect()->back()->with('success', 'Amount subtracted successfully!');
    }

	public function user_mlm(Request $request,$id)
    {
			
    $value = $request->session()->has('id');
    	
            if(!empty($value))
            {
    
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://mahajong.club/admin/index.php/Mahajongapi/level_getuserbyrefid?id=$id",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Cookie: ci_session=itqv6s6aqactjb49n7ui88vf7o00ccrf'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    $data= json_decode($response);
    
                 return view ('user.mlm_user_view')->with('data', $data);
    			
    			  }
            else
            {
               return redirect()->route('login');  
            }
    }
      
    public function registerwithref($id)
    {
         
         $ref_id = User::where('referral_code',$id)->first();
        //  $country=DB::select("SELECT `phone_code` FROM `country` WHERE 1;");
        $country = DB::table("country")->select("phone_code")->get();
       
         return view('user.newregister')->with('ref_id',$ref_id)->with('country',$country);
         
     }
     
    protected function generateRandomUID() 
    {
					$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$digits = '0123456789';

					$uid = '';

					// Generate first 4 alphabets
					for ($i = 0; $i < 4; $i++) {
						$uid .= $alphabet[rand(0, strlen($alphabet) - 1)];
					}

					// Generate next 4 digits
					for ($i = 0; $i < 4; $i++) {
						$uid .= $digits[rand(0, strlen($digits) - 1)];
					}

					return $this->check_exist_memid($uid);
					
				}

	protected function check_exist_memid($uid)
	{
					$check = DB::table('users')->where('u_id',$uid)->first();
					if($check){
						return $this->generateRandomUID(); // Call the function using $this->
					} else {
						return $uid;
					}
				}
      
    public function register_store(Request $request,$referral_code)
    {
          $validatedData = $request->validate([
            'mobile' => 'required|unique:users,mobile|regex:/^\d{10}$/',
            'password' => 'required',
            'email' => 'required|unique:users,email',
        ]);
          //dd($ref_id);

       $refer = DB::table('users')->where('referral_code', $referral_code)->first();
	 	if ($refer !== null) {
			$referral_user_id = $refer->id;

        // $username = Str::upper(Str::random(6, 'alpha'));
        $username = Str::random(6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    	    $u_id = $this->generateRandomUID();
    	     
    	     $referral_code = Str::upper(Str::random(6, 'alpha'));
    	     
    	      $rrand = rand(1,20);
              $all_image = All_image::find($rrand);
              
        $image = $all_image->image;
    	
        $userId = DB::table('users')->insertGetId([
            'mobile' => $request->mobile,
            'email' => $request->email,
            'username' => $username,
            'password' =>$request->password,
            'referral_user_id' =>$referral_user_id,
            'referral_code' => $referral_code,
    		'u_id' => $u_id,
    		'status' => 1,
    		'userimage' => $image,
        ]);
      // $refid= isset($referral_user_id)? $referral_user_id : '8';
         DB::select("UPDATE `users` SET `yesterday_register`=yesterday_register+1 WHERE `id`=$referral_user_id");
    	
        return redirect(str_replace('https://admin.', 'http://', "https://winbhai.in/"));
    		
        }
    }

    public function updatereferral(Request $request, $id)
    {
         //dd($request->all(), $id );
        $request->validate([
            'referral_user_id' => 'required|string|max:255',
        ]);

        DB::table('users')
            ->where('id', $id)
            ->update(['referral_user_id' => $request->input('referral_user_id')]);
        return redirect()->back()->with('success', 'Sponser ID updated successfully!');
    }
      
}