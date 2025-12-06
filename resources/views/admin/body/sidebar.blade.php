<div class="full_container">
         <div class="inner_container">
            <!-- Sidebar  -->
            <nav id="sidebar">
               <div class="sidebar_blog_1">
                  <div class="sidebar-header">
                     <div class="logo_section">
                        <a href="index.html"><img class="logo_icon img-responsive" src="images/logo/logo_icon.png" alt="#" /></a>
                     </div>
                  </div>
                  <div class="sidebar_user_info">
                     <div class="icon_setting"></div>
                     <div class="user_profle_side">
                        <div class="user_img"><img class="img-responsive" src="https://root.winbhai.in/public/images/layout_img/user_img.jpg" 								alt="#" /></div>
                        <div class="user_info">
                           <h6>Admin</h6>
                           <p><span class="online_animation"></span> Online</p>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="sidebar_blog_2">
                  <h4>General</h4>
                 
                  
                   @php
    $permissions = session('permissions', []); // Example: ["1", "2", "3"]
   
    $permissionMap = [
        '1' => 'dashboard',
        '2' => 'gameList',
        '3' => 'attendance',
        '4' => 'players',
        '5' => 'mlmlevel',
        '6' => 'colour_prediction',
        '7' => 'aviator_game',
        '8' => 'bet_history',
        '9' => 'chicken_road_game',
        '12' => 'assign_role',
        '13' => 'role_permission',
        '14' => 'gift',
        '15' => 'gift_redeemed_history',
        '16' => 'activity_and_banner',
        '17' => 'agents',
        '18' => 'deposit',
        '20' => 'widthdrawl',
        '21' => 'usdt_qr_code',
        '22' => 'usdt_deposit',
        '23' => 'usdt_widthdrawl',
        '24' => 'notice',
        '25' => 'settings',
	    '26' => 'support_settings',
	    '27' => 'change_password',
	    '28' => 'logout',
	    '29' => 'ajent',
	    '30' => 'tripleChance_bet_result',
	    '31' => 'triplechance_bet_history',
	    '32' => 'game_summary',
	    '33' => 'set_winning_priority',
	    '35' => 'manual_deposit',
	    '36' => 'manual_widthdrawl',
	    '37' => 'manual_qr',
	    '38' => 'usdt_conversion',
	   
	    '40' => 'campaign',
	    '41' => 'conversion',
	    '42' => 'feedback',
	    '43' => 'sponser',
	    '44' => 'paymode_show',
	    '45' => 'offer',
	    '47' => 'revenue',
	    '48' => 'game_slider_img',
	    '50' => 'category_language',
	    '51' => 'notification_admin',
	    
	    
    ];

    $allowed = collect($permissions)->map(fn($id) => $permissionMap[$id] ?? null)->filter()->toArray();
@endphp
                  
     
                  <ul class="list-unstyled components">
                      @if(in_array('dashboard', $allowed)) 
                     <li><a href="{{route('dashboard')}}"><i class="fas fa-chart-line yellow_color"></i> <span>Dashboard</span></a></li>
					   @endif
					  
					   @if(in_array('gameList', $allowed)) 
						<li>
							<a href="{{ route('gameList')}}" class="nav-item-custom">
								<i class="fas fa-gamepad"></i> 
								<span>Game List</span>
							</a>
						</li>
					   @endif
					  
					   @if(in_array('attendance', $allowed)) 
                     <li><a href="{{route('attendance.index')}}"><i class="fa fa-clock-o purple_color2"></i> <span>Attendance</span></a></li>
					  @endif
					  
					  
					   @if(in_array('agents', $allowed))
                    <li><a href="{{ route('agents') }}"><img width="25" height="25" src="https://img.icons8.com/color-glass/48/show-password.png" style="margin-right:8px;"/><span> Agents</span></a></li>
                    @endif
                    
                    
                    @if(in_array('players', $allowed)) 
                     <li><a href="{{route('users')}}"><i class="fa fa-user orange_color"></i> <span>Players</span></a></li>
					  @endif
					  
    		  
					    
					   @if(in_array('assign_role', $allowed))
                        <li><a href="{{ route('role.createrole') }}"><img width="25" height="25" src="https://img.icons8.com/3d-fluency/94/bell.png" style="margin-right:8px;" /><span>Assign Role</span></a></li>
                        @endif
                      
					  
					   
					   @if(in_array('campaign', $allowed)) 
                     <li><a href="{{route('campaign.list')}}"><i class="fa fa-user orange_color"></i> <span>Campaign List</span></a></li>
					  @endif
					  
					   <!--@if(in_array('demo_user', $allowed)) -->
					   <!--<li class="{{ Request::routeIs('register.create') ? 'active' : '' }}"><a href="{{route('register.create')}}"><i class="fa fa-user orange_color"></i> <span>Dmeo User</span></a></li>-->
        <!--                 @endif-->
                         
                         @if(in_array('conversion', $allowed)) 
                     <li><a href="{{route('payment.limits')}}"><i class="fa fa-user orange_color"></i> <span>Conversion List</span></a></li>
					  @endif
                   
					   @if(in_array('mlmlevel', $allowed)) 
                     <li><a href="{{route('mlmlevel')}}"><i class="fa fa-list red_color"></i> <span>MLM Levels</span></a></li>
                     @endif
                     
                     
                 @php
                        $firstPart = DB::select("SELECT * FROM `game_settings` LIMIT 4");
                        // id = 1 waale record ko find karo
                        $recordWithId1 = collect($firstPart)->firstWhere('id', 1);
                    @endphp

                   @if($recordWithId1)
                        @if(in_array('colour_prediction', $allowed))			  
                            <li>
                                <a href="{{ route('colour_prediction', $recordWithId1->id) }}">
                                    <i class="fa fa-list red_color"></i>
                                    <span>Colour Prediction</span>
                                </a>
                            </li>
                        @endif
                    @endif
                    
                    
        						 @if(in_array('aviator_game', $allowed)) 
					  <li><a href="{{route('result' , 5)}}"><i class="fa fa-object-group blue2_color"></i><span>Aviator Game</span></a></li>
                      @endif
                      
                      
                      
                      
                      	 @if(in_array('chicken_road_game', $allowed))			    <!-- Chicken Road Game -->
          <li>
            <a href="#apps1" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
				   <i class="fa fa-gamepad dark_color" style="color: gold;"></i>
					<span> Chicken Road Game</span>
            </a>
            <ul class="collapse list-unstyled" id="apps1">
             <li class="{{ Request::is('multiplier') ? 'active' : '' }}">
              <a href="{{ url('multiplier') }}">
                <i class="fas fa-percentage"></i> <span>Multiplier</span>
              </a>
            </li>
             <li class="{{ Request::is('bet') ? 'active' : '' }}">
                  <a href="{{ url('bet') }}">
                    <i class="fas fa-dice"></i> <span>Bet History</span>
                  </a>
                </li>
               <li class="{{ Request::is('betValues') ? 'active' : '' }}">
                  <a href="{{ route('betValues') }}">
                    <i class="fas fa-star"></i> <span>Bet Values</span>
                  </a>
                </li>
             <li class="{{ Request::routeIs('amountSetup') ? 'active' : '' }}">
                  <a href="{{ route('amountSetup') }}">
                    <i class="fas fa-rupee-sign"></i> <span>Amount Setup</span>
                  </a>
                </li>
            </ul>
          </li>
			  @endif 
				
					   @php
                         $game_id = DB::select("SELECT * FROM `game_settings` where status=0 LIMIT 5;");
                       @endphp
					  
					   @if(in_array('bet_history', $allowed)) 
					  <li>
                        <a href="#apps-xy" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-object-group blue2_color"></i> <span>Bet History</span></a>
                        <ul class="collapse list-unstyled" id="apps-xy">
							 @foreach($game_id as $itemm)
               				 <li><a href="{{route('all_bet_history',$itemm->id)}}"> <span>{{$itemm->name}}</span></a></li>
							 @endforeach
                        </ul>
                     </li>
                     @endif
					  
					   <li>
					       
					 
					       
				 @if(in_array('offer', $allowed))	  
			<li><a href="{{route('coupons.index')}}"><i class="fa fa-bullhorn dark_color"></i>
                </i> <span>Deposit Coupons</span></a></li>
                	@endif
                	
                	 @if(in_array('gift', $allowed))
                     <li><a href="{{route('gift')}}"><i class="fa fa-gift dark_color"></i>
                        </i> <span>Reedem Bonus</span></a></li>
                        	@endif
                        	
                        	 @if(in_array('gift_redeemed_history', $allowed))
					  <li><a href="{{route('giftredeemed')}}"><i class="fa fa-credit-card dark_color"></i>
                         <span>Gift Redeemed History</span></a></li>
                         @endif
                          @if(in_array('activity_and_banner', $allowed))
                    <li><a href="{{route('banner')}}"><i class="fa fa-picture-o" aria-hidden="true"></i> <span> Activity & Banner</span></a></li> 
                    @endif
                    
                    @if(in_array('game_slider_img', $allowed))
                    <li><a href="{{route('game_slider_img')}}"><i class="fa fa-picture-o" aria-hidden="true"></i> <span> Game Banner</span></a></li> 
                    @endif
                    
                     @if(in_array('feedback', $allowed))
                     <li><a href="{{route('feedback')}}"><i class="fa fa-file blue1_color"></i> <span>FeedBack</span></a></li>
                     @endif
                     
                      @if(in_array('category_language', $allowed))
                     <li><a href="{{route('index')}}"><i class="fa fa-file blue1_color"></i> <span>Learn Section</span></a></li>
                     @endif
                     
                     
                      @if(in_array('deposit', $allowed))
					   <li>
                         <a href="#app13" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-tasks  green_color"></i>            <span>Bappa Venture Deposit</span></a>
                         <ul class="collapse list-unstyled" id="app13">
                       <li><a href="{{ route('deposit', 1) }}">Pending</a></li>
                    <li><a href="{{ route('deposit', 2) }}">Success</a></li>
                    <li><a href="{{ route('deposit',3) }}">Reject</a></li>
                    
                    
                         </ul>
                      </li>
                      @endif
                      
                    	 @if(in_array('widthdrawl', $allowed))				  
					  <li>
                         <a href="#app11" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-wrench purple_color2"></i>            <span>Bappa Venture Withdrawal</span></a>
                         <ul class="collapse list-unstyled" id="app11">
                       <li><a href="{{ route('widthdrawl', 1) }}">Pending</a></li>
                    <li><a href="{{ route('widthdrawl', 2) }}">Approved</a></li>
                    <li><a href="{{ route('widthdrawl',3) }}">Reject</a></li>
                    <!--<li><a href="{{ route('widthdrawl', 4) }}">Successfull</a></li>
                    <li><a href="{{ route('widthdrawl',5) }}">Failed</a></li>-->
                    
                    
                         </ul>
                      </li>
                      @endif
                      
                      
                         @if(in_array('usdt_qr_code', $allowed))
                <li><a href="{{route('usdtqr')}}"><i class="fa fa-table purple_color2"></i> 
                <span>USDT QR Code</span></a></li>
                @endif
                
                 @if(in_array('usdt_conversion', $allowed))
                <li><a href="{{route('usdt_conversion.index')}}"><i class="fa fa-table purple_color2"></i> 
                <span>USDT Conversion Rate</span></a></li>
                @endif
                
                
                 @if(in_array('paymode_show', $allowed))
                <li><a href="{{route('paymode_show.index')}}"><i class="fa fa-table purple_color2"></i> 
                <span>Paymode show</span></a></li>
                @endif
                
                
                 @if(in_array('usdt_deposit', $allowed))
                                                          <li>
                     <a href="#app20" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fa fa-tasks  green_color"></i><span>USDT Deposit</span></a>
                     <ul class="collapse list-unstyled" id="app20">
                   <li><a href="{{ route('usdt_deposit', 1) }}">Pending</a></li>
                <li><a href="{{ route('usdt_deposit', 2) }}">Success</a></li>
                <li><a href="{{ route('usdt_deposit',3) }}">Reject</a></li>
                
                
                     </ul>
                  </li>
                  @endif
                                    @if(in_array('usdt_widthdrawl', $allowed))
                                           <li>
                             <a href="#app21" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fa fa-wrench purple_color2"></i>            <span>USDT Withdrawal</span></a>
                             <ul class="collapse list-unstyled" id="app21">
                           <li><a href="{{ route('usdt_widthdrawl', 1) }}">Pending</a></li>
                        <li><a href="{{ route('usdt_widthdrawl', 2) }}">Success</a></li>
                        <li><a href="{{ route('usdt_widthdrawl',3) }}">Reject</a></li>
                        
                             </ul>
                          </li>@endif
                          
                            @if(in_array('usdt_qr_code', $allowed))
                <li><a href="{{route('manual_qr')}}"><i class="fa fa-table purple_color2"></i> 
                <span>QR Code</span></a></li>
                @endif
                
                          @if(in_array('manual_deposit', $allowed))
                                                          <li>
                     <a href="#app35" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fa fa-tasks  green_color"></i><span>Manual Deposit</span></a>
                     <ul class="collapse list-unstyled" id="app35">
                   <li><a href="{{ route('manual_deposit', 1) }}">Pending</a></li>
                <li><a href="{{ route('manual_deposit', 2) }}">Success</a></li>
                <li><a href="{{ route('manual_deposit',3) }}">Reject</a></li>
                
                
                     </ul>
                  </li>
                  @endif
                                    @if(in_array('manual_widthdrawl', $allowed))
                                           <li>
                             <a href="#app36" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fa fa-wrench purple_color2"></i>            <span>Manual Withdrawal</span></a>
                             <ul class="collapse list-unstyled" id="app36">
                           <li><a href="{{ route('manual_widthdrawl', 1) }}">Pending</a></li>
                        <li><a href="{{ route('manual_widthdrawl', 2) }}">Success</a></li>
                        <li><a href="{{ route('manual_widthdrawl',3) }}">Reject</a></li>
                        
                        
                             </ul>
                          </li>@endif
                          
                           @if(in_array('notice', $allowed))
					  <li><a href="{{route('notification')}}"><i class="fa fa-bell  yellow_color"></i> <span>Notice</span></a></li>@endif
					  
					  @if(in_array('notification_admin', $allowed))
					  <li><a href="{{route('notification_admin')}}"><i class="fa fa-bell  yellow_color"></i> <span>Notification admin</span></a></li>@endif
					 
					 
					  
					    @if(in_array('revenue', $allowed))
					  <li><a href="{{route('revenues')}}"><i class="fa fa-bell  yellow_color"></i> <span>Revenue Update</span></a></li>@endif
					  
					  
					   @if(in_array('sponser', $allowed))
					  <li><a href="{{route('admin.sponser')}}"><i class="fa fa-bell  yellow_color"></i> <span>Sponser</span></a></li>@endif
					  
					   @if(in_array('settings', $allowed))
                     <li><a href="{{route('setting')}}"><i class="fa fa-cogs dark_color"></i>
                            <span>Setting</span></a></li>@endif
                            
                             @if(in_array('support_settings', $allowed))
					  <li><a href="{{route('support_setting')}}"><i class="fa fa-info-circle  yellow_color"></i> <span>Support Setting </span></a></li> 
					  @endif
					   @if(in_array('change_password', $allowed))
                      <li><a href="{{route('change_password')}}"><i class="fa fa-warning red_color"></i> <span>Change Password</span></a></li>
                      @endif
                       @if(in_array('logout', $allowed))
                     <li><a href="{{route('auth.logout')}}"><i class="fa fa-line-chart yellow_color"></i> <span>Logout</span></a></li>
	                    @endif
	
	
                  </ul>
               </div>
            </nav>
            <!-- end sidebar -->