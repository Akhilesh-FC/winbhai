<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\CreateorderController;
// use App\Http\Controllers\WorkassignController;
use App\Http\Controllers\ProjectmaintenanceController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\RevenueController;  
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TrxAdminController;

use App\Http\Controllers\GiftController;
use App\Http\Controllers\PlinkoController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\WidthdrawlController;
use App\Http\Controllers\AdminPayinController;
use App\Http\Controllers\MlmlevelController;
use App\Http\Controllers\ColourPredictionController;
use App\Http\Controllers\AdminSettingController; 
use App\Http\Controllers\BannerController;
use App\Http\Controllers\AllBetHistoryController;
use App\Http\Controllers\BankDetailController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PlinkoBetController;
use App\Http\Controllers\Api\jiliApiController;
use App\Http\Controllers\{AviatorAdminController,UpAndDownController,RedBlackController,KinoController,DiceController,SpinController,jhandimundaController, highlowController,jackpotController,hotairballoonController,TitliadminController,teenadminController,miniroulleteadminController,
						  Lucky12Controller,Lucky16Controller,TripleChanceController,FunTargetController,UsdtDepositController,UsdtWidthdrawController,UsdtController,AndarbaharGameController,DragonAdminController,HeadtailGameController,K3Controller,ChickenController,AuthController,AgentController,AssignRoleController,RoleController,PermissionController,CreateRoleController,PlayerController,Manual_qr_Controller,ManualDepositController,ManualWidthdrawController,ConversionController,AgetController,AdminCouponController,CategoryadminLanguageController,AdminNotificationController};
						  
						  
						  
						  

Route::get('/play/{token}', function ($token) {
    $realUrl = Cache::get("GAME_URL_" . $token);

    if (!$realUrl) {
        return "Session expired or invalid game URL.";
    }

    return redirect()->to($realUrl);  // 🔥 FIX
});


Route::get('/play/{token}', [jiliApiController::class, 'launchGame']);




Route::get('/admin/notification', [AdminNotificationController::class,'index'])->name('notification_admin');

Route::post('/admin/send-notification', [AdminNotificationController::class, 'sendNotification'])
    ->name('notification.store');



    Route::get('/admin/media-manager', [CategoryadminLanguageController::class, 'index'])->name('index');
    
    Route::post('/admin/category/store', [CategoryadminLanguageController::class, 'storeCategory'])->name('admin.category.store');
    Route::post('/admin/category/update/{id}', [CategoryadminLanguageController::class, 'updateCategory'])->name('admin.category.update');
    
    Route::post('/admin/language/store', [CategoryadminLanguageController::class, 'storeLanguage'])->name('admin.language.store');
    Route::post('/admin/language/update/{id}', [CategoryadminLanguageController::class, 'updateLanguage'])->name('admin.language.update');
    
    Route::post('/admin/media/store', [CategoryadminLanguageController::class, 'storeMedia'])->name('admin.media.store');
    Route::post('/admin/media/update/{id}', [CategoryadminLanguageController::class, 'updateMedia'])->name('admin.media.update');
    Route::post('/admin/media/delete/{id}', [CategoryadminLanguageController::class, 'deleteMedia'])->name('admin.media.delete');


    // Revenue List Page
    Route::get('/revenue_show', [UserController::class, 'revenue_show'])->name('revenues');
    // Revenue Update
    Route::post('/revenue_update', [UserController::class, 'revenue_update'])->name('revenues.update');

   
    Route::get('/game_slider_show', [AdminCouponController::class, 'game_slider_show'])->name('game_slider_img');
    Route::post('/game_slider_update', [AdminCouponController::class, 'game_slider_update'])->name('game_slider_img.update');
    
    
    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons/update/{id}', [AdminCouponController::class, 'update'])->name('coupons.update');
    Route::post('/coupons/toggle-status/{id}', [AdminCouponController::class, 'toggleStatus'])->name('coupons.toggle');
    Route::post('/coupons/store', [AdminCouponController::class, 'store'])->name('coupons.store');
    Route::post('/coupons/delete/{id}', [AdminCouponController::class, 'delete'])->name('coupons.delete');


    Route::get('/paymode_show', [UserController::class, 'paymode_show'])->name('paymode_show.index');
    Route::post('/paymode_show/update-image/{id}', [UserController::class, 'paymode_updateImage'])->name('paymode_show.updateImage');
    
    Route::post('/update-whatsapp-deposit', [UserController::class, 'updateWhatsappDeposit'])->name('update.whatsapp.deposit');
   
    Route::get('/admin/sponser', [UserController::class, 'showSponser'])->name('admin.sponser');
    Route::get('/admin/sponser/toggle/{id}', [UserController::class, 'toggleSponserStatus'])->name('admin.sponser.toggle');
    Route::get('/admin/sponser/delete/{id}', [UserController::class, 'deleteSponser'])->name('admin.sponser.delete');
    Route::post('/admin/sponser/update', [UserController::class, 'updateSponser'])->name('admin.sponser.update');
      
    /////////////////Role Management//////////////
    Route::controller(CreateRoleController::class)->group(function(){
        Route::get('/createrole','createrole')->name('role.createrole');  
        Route::get('/admin_role/{id}','admin_role')->name('admin_role'); 
        Route::post('/update-permission/{id}','update_permission')->name('update.permission');
        Route::post('/create_role','store_permissions')->name('role.store');  
        // Route::any('all_role','all_role')->name('allrole');
    });

    Route::get('/agent',[AgetController::class,'Agent'])->name('agents');
    Route::post('/agentStore',[AgetController::class,'agentStore'])->name('agentStore');
    Route::post('/agentUpdate',[AgetController::class,'agentUpdate'])->name('agentUpdate');
    Route::get('/agent-status',[AgetController::class,'agentstatus'])->name('agent.status');
    Route::post('/agent/permission', [AgetController::class, 'agentPermission'])->name('agent.permission');
    
    // routes/web.php
    Route::get('/agent/users/{agent_id}', [AgetController::class, 'agentUserDetails'])->name('agent.users');
    // Agent panel - Show Player List + Add Player Form
    Route::get('/agent/players', [AgetController::class, 'agentPlayers'])->name('agent.players');

    // Store New Player created by Agent
    Route::post('/agent/player/store', [AgetController::class, 'agentPlayerStore'])->name('agent.player.store');


    

    Route::get('/campaign', [UserController::class, 'campaignList'])->name('campaign.list');
    Route::post('/campaign/update-revenue', 
    [UserController::class, 'updateUserCampaignRevenue']
)->name('campaign.update.revenue');



    
    Route::get('/paymentLimitsList', [UserController::class, 'paymentLimitsList'])->name('payment.limits');
    Route::post('/payment-limits/update', [UserController::class, 'updatePaymentLimit'])->name('payment.limit.update');
  
    //// demo user route ///
	Route::get('/system_user', [UserController::class, 'demoUser'])->name('register.create');
    Route::post('/demo_registers', [UserController::class, 'store'])->name('demo_register.store');


	Route::get('/manual_qr',[Manual_qr_Controller::class, 'manual_qr_view'])->name('manual_qr');
   	Route::post('/manual_qr/{id}', [Manual_qr_Controller::class, 'update_manual_qr'])->name('manual_qr.update');
    Route::post('/manual_qr/status/{id}', [Manual_qr_Controller::class, 'updateStatus'])->name('manual_qr.status');
   

    Route::get('/usdt-conversion', [ConversionController::class, 'index'])->name('usdt_conversion.index');
    Route::post('/usdt-conversion/update', [ConversionController::class, 'update'])->name('usdt_conversion.update');


    // Players Controller
    Route::controller(PlayerController::class)->group(function (){
    Route::any('/player-index', 'player_index')->name('player.index');
    Route::get('/player_activity_info/{id}', 'player_activity_info')->name('player_activity_info');
    Route::post('/playerStore', 'PlayerStore')->name('PlayerStore');
    Route::post('/playerUpdate', 'PlayerUpdate')->name('PlayerUpdate');
    Route::get('/playerStatus', 'playerStatus')->name('playerStatus');
     // Route::post('/wallet-add-{id}', 'wallet_store')->name('wallet.store');
    //	Route::post('/wallet-subtract-{id}', 'wallet_subtract')->name('wallet.subtract');
	Route::get('/player-index/update/{status}/{id}','update_status'); 
	Route::post('/player/store',  'storePlayer')->name('player.store');
    });



    // Auth Login Controller
    Route::controller(AuthController::class)->group(function (){
        Route::get('/', 'auth_index')->name('login');
        Route::post('/AuthLogin', 'AuthLogin')->name('AuthLogin');
        Route::get('/AuthLogout', 'AuthLogout')->name('AuthLogout')->middleware('auth'); 
        Route::get('/ChangePasswordIndex', 'ChangePasswordIndex')->name('ChangePasswordIndex')->middleware('auth');
        Route::Post('/ChangePassword', 'ChangePassword')->name('ChangePassword')->middleware('auth'); 
    });
 
    ////RoleAssign///
    Route::controller(AssignRoleController::class)->group(function (){
        Route::get('/assignRole-index', 'assignRole_index')->name('assignRole.index');
       // Route::post('/agentStore','agentStore')->name('agentStore');
    });
    
    ///////RoleAssign
    Route::controller(RoleController::class)->group(function (){
        Route::post('/roles', 'store')->name('roles.store');
    });
    
    ////PermissionRole///
    Route::controller(PermissionController::class)->group(function(){
        Route::post('permissions','store')->name('permissions.store');
    });


    Route::controller(ChickenController::class)->group(function(){
        Route::get('bet','betlist');
        Route::get('betValues','betValues')->name('betValues');
        Route::post('updateBetValues','updateBetValues')->name('updateBetValues');
        Route::get('betHistory','bet_history');
        Route::get('cashout','cashout');
        Route::get('multiplier','multiplier');
        Route::get('winning_result','winner');
        Route::get('/amountSetup','amountSetup')->name('amountSetup');
        Route::post('/updateGameRules','updateGameRules')->name('updateGameRules');
        Route::post('add_multiplier','add_multiplier')->name("add_multiplier");
        Route::post('multiplier_update','multiplier_update')->name("multiplier_update");
        Route::post('multiplier_delete','multiplier_delete')->name("multiplier_delete");
        Route::post('updateRoastMultiplier','updateRoastMultiplier')->name("updateRoastMultiplier");
    });


    
    Route::get('/offer', [NoticeController::class, 'offer'])->name('offer');
    Route::get('/offer/edit/{id}', [NoticeController::class, 'edit'])->name('offer.edit');
    Route::put('/offer/update/{id}', [NoticeController::class, 'update'])->name('offer.update');
    
    
    Route::get('/aviator/{game_id}',[AviatorAdminController::class, 'aviator_prediction_create'])->name('result');
    Route::get('/aviator_fetchs/{game_id}', [AviatorAdminController::class, 'aviator_fetchDatacolor'])->name('aviator_fetch_data');
    
    Route::post('/aviator_store',[AviatorAdminController::class, 'aviator_store'])->name('aviator.store');
    Route::post('/aviator_percentage_update', [AviatorAdminController::class, 'aviator_update'])->name('aviator_percentage.update');
    Route::get('/bet-history/{game_id}', [AviatorAdminController::class, 'aviator_bet_history'])->name('bet_history');

    Route::get('/clear', function() {
    
       Artisan::call('cache:clear');
       Artisan::call('config:clear');
       Artisan::call('config:cache');
       Artisan::call('view:clear');
    
       return "Cleared!";
    
    });
 
    Route::get('/',[LoginController::class,'login'])->name('login');
    Route::post('/login',[LoginController::class,'auth_login'])->name('auth.login');
    Route::get('/dashboard',[LoginController::class,'dashboard'])->name('dashboard');
   
    // register
    Route::get('/register',[LoginController::class,'register_create'])->name('register');
    Route::post('/register',[LoginController::class,'register_store'])->name('register.store');
        



    Route::get('/auth-logout',[LoginController::class,'logout'])->name('auth.logout');
    Route::get('/change_password',[LoginController::class,'password_index'])->name('change_password');
    Route::post('/change_password',[LoginController::class,'password_change'])->name('change_pass.update');
    ///Admin Payin Route ///
    Route::any('/admin_payin-{id}',[AdminPayinController::class,'admin_payin'])->name('admin_payin.store');
    Route::get('/bank_details',[BankDetailController::class, 'bankdetails'])->name('bankdetails');
    Route::post('/edit_bank_details',[BankDetailController::class, 'edit_bank_details'])->name('edit_bank_details');
    
    Route::get('/gift-index',[GiftController::class, 'index'])->name('gift');
    Route::post('/gift-store',[GiftController::class, 'gift_store'])->name('gift.store');
    Route::get('/giftredeemed',[GiftController::class, 'giftredeemed'])->name('giftredeemed');

   //Banner
    Route::get('/banner-index',[BannerController::class, 'index'])->name('banner');
    Route::post('/banner-store',[BannerController::class, 'banner_store'])->name('banner.store');
    Route::get('/banner-delete-{id}',[BannerController::class, 'banner_delete'])->name('banner.delete');
    Route::post('/banner-update-{id}', [BannerController::class, 'banner_update'])->name('banner.update');  
    
    Route::get('/attendance',[AttendanceController::class, 'attendance'])->name('attendance.index');
    Route::post('/attendance',[AttendanceController::class, 'attendance_store'])->name('attendance.store');
    Route::get('/attendance-delete-{id}',[AttendanceController::class, 'attendance_delete'])->name('attendance.delete');
    Route::post('/attendance-update-{id}', [AttendanceController::class, 'attendance_update'])->name('attendance.update');
    
    
    Route::get('/users',[UserController::class, 'user_create'])->name('users');
    Route::get('/user_detail-{id}',[UserController::class,'user_details'])->name('userdetail');
    Route::post('/password-update-{id}', [UserController::class, 'password_update'])->name('password.update');
    
    Route::post('/remark-update-{id}', [UserController::class, 'remark_update'])->name('remark.update');

    
    //  Route::post('/users',[UserController::class, 'user_store'])->name('users.store');
    Route::get('/users-delete-{id}',[UserController::class, 'delete'])->name('users.destroy');
    Route::get('/users-active-{id}', [UserController::class,'user_active'])->name('user.active');
    Route::get('/users-inactive-{id}',[UserController::class, 'user_inactive'])->name('user.inactive');
    Route::post('/wallet-store-{id}',[UserController::class, 'wallet_store'])->name('wallet.store');
    Route::post('/refer-store-{id}',[UserController::class, 'refer_id_store'])->name('refer_id.store');
    Route::post('/wallet/subtract/{id}', [UserController::class, 'wallet_subtract'])->name('wallet.subtract');

	Route::get('/users-mlm-{id}',[UserController::class, 'user_mlm'])->name('user.mlm');
	
	Route::get('/registerwithref/{id}',[UserController::class,'registerwithref'])->name('registerwithref');
	Route::post('/register_store-{referral_code}',[UserController::class,'register_store'])->name('user_register');

 	Route::get('/trx/{gameid}',[TrxAdminController::class, 'trx_create'])->name('trx');
	Route::get('/fetch/{gameid}', [TrxAdminController::class, 'fetchData'])->name('fetch_data');

    Route::post('/trx-store',[TrxAdminController::class, 'store'])->name('trx.store');
    Route::post('/percentage.update', [TrxAdminController::class, 'update'])->name('percentage.update');

    Route::get('/colour_prediction/{gameid}',[ColourPredictionController::class, 'colour_prediction_create'])->name('colour_prediction');
	Route::get('/fetch/{gameid}', [ColourPredictionController::class, 'fetchData'])->name('fetch_data');

    Route::post('/colour_prediction-store',[ColourPredictionController::class, 'store'])->name('colour_prediction.store');
    Route::post('/future-result-store', [ColourPredictionController::class, 'future_store'])->name('future_result.store');
     
    Route::get('/k3/{gameid}',[K3Controller::class, 'k3_create'])->name('k3');
	Route::get('/k3_fetch/{gameid}', [K3Controller::class, 'k3_fetchData'])->name('k3_fetch_data');
    Route::post('/k3-store',[K3Controller::class, 'k3_store'])->name('k3.store');
    Route::post('/k3_future-result-store', [K3Controller::class, 'k3_future_store'])->name('k3_future_result.store');
    
    Route::post('/category',[CategoryController::class, 'category_store'])->name('category.store');
    Route::get('/category-active-{id}', [CategoryController::class,'category_active'])->name('category.active');
    Route::get('/category-inactive-{id}',[CategoryController::class, 'category_inactive'])->name('category.inactive');
    Route::get('/category-delete-{id}',[CategoryController::class, 'category_delete'])->name('category.delete');
    Route::post('/category-update-{id}', [CategoryController::class, 'category_update'])->name('category.update');
    
       
    Route::get('/mlm_level',[MlmlevelController::class, 'mlmlevel_create'])->name('mlmlevel');
    Route::post('/mlm_level',[MlmlevelController::class, 'mlmlevel_store'])->name('mlmlevel.store');
    
    Route::get('/mlm_level-active-{id}', [MlmlevelController::class,'mlmlevel_active'])->name('mlmlevel.active');
    Route::get('/mlm_level-inactive-{id}',[MlmlevelController::class, 'mlmlevel_inactive'])->name('mlmlevel.inactive');
    Route::get('/mlm_level-delete-{id}',[MlmlevelController::class, 'mlmlevel_delete'])->name('mlmlevel.delete');
    Route::post('/mlm_level-update-{id}', [MlmlevelController::class, 'mlmlevel_update'])->name('mlmlevel.update');
    
    
    Route::get('/orderlist',[OrderController::class,'order_create'])->name('order.list');
    Route::post('/orderlist-store',[OrderController::class,'order_store'])->name('order.store');
    Route::get('/orderlist-active-{id}', [OrderController::class,'order_active'])->name('order.active');
    Route::get('/orderlist-inactive-{id}',[OrderController::class, 'order_inactive'])->name('order.inactive');
    Route::get('/orderlist-delete-{id}',[OrderController::class, 'order_delete'])->name('order.delete');
    Route::post('/orderlist-update-{id}', [OrderController::class, 'order_update'])->name('order.update');
    
    
    Route::get('/Create-orderlist',[CreateorderController::class,'createorder_index'])->name('create_orderlist');
    Route::post('/Create-orderlist',[CreateorderController::class,'createorder_store'])->name('create_orderlist.store');
    Route::get('/Create-orderlist-active-{id}', [CreateorderController::class,'create_order_active'])->name('create_order.active');
    Route::get('/Create-orderlist-inactive-{id}',[CreateorderController::class, 'create_order_inactive'])->name('create_order.inactive');
    Route::get('/Create-orderlist-delete-{id}',[CreateorderController::class, 'createorder_delete'])->name('create_order.delete');
    Route::post('/Create-orderlist-update-{id}', [CreateorderController::class, 'createorder_update'])->name('create_order.update');
 
    
    Route::get('/setting',[SettingController::class,'setting_index'])->name('setting');
    Route::get('/gameList',[SettingController::class,'gameList'])->name('gameList');
    
    
    // Setting page open
Route::get('/need-help/chat/{id}', [SettingController::class, 'Need_Help_Chat_With_Us_view'])
    ->name('needhelp.chat.view');

// Update whatsapp number
Route::post('/need-help/chat/update/{id}', [SettingController::class, 'Need_Help_Chat_With_Us'])
    ->name('needhelp.chat.update');

    

    Route::get('/view-{id}',[SettingController::class,'view'])->name('view');
    Route::post('/setting',[SettingController::class,'setting_store'])->name('setting.store');
    Route::get('/setting-active-{id}', [SettingController::class,'setting_active'])->name('setting.active');
    Route::get('/setting-inactive-{id}',[SettingController::class, 'setting_inactive'])->name('setting.inactive');
    Route::get('/setting-delete-{id}',[SettingController::class, 'setting_delete'])->name('setting.delete');
    Route::post('/setting-update-{id}', [SettingController::class, 'setting_update'])->name('setting.update');
    
    Route::post('/update-status/{id}', [SettingController::class, 'updateStatus'])->name('update.status');

    Route::get('/support_setting',[SettingController::class,'support_setting'])->name('support_setting');
    Route::post('/supportsetting-update-{id}', [SettingController::class, 'supportsetting_update'])->name('supportsetting.update');
    
    Route::post('/contact-us-update/{id}', [SettingController::class,'contactUsUpdate'])
    ->name('contact.us.update');

Route::post('/contact-with-us-update/{id}', [SettingController::class,'contactWithUsUpdate'])
    ->name('contact.with.us.update');

    
    Route::get('/notification',[SettingController::class,'notification'])->name('notification');
    Route::get('/view_notification-{id}',[SettingController::class,'view_notification'])->name('view_notification');
    Route::post('/notification-update-{id}', [SettingController::class, 'notification_update'])->name('notification.update');
    Route::post('/notification_store', [SettingController::class, 'notification_store'])->name('notification_store');
    Route::get('/add_notification',[SettingController::class,'add_notification'])->name('add_notification');

    Route::get('/deposit-{id}',[DepositController::class,'deposit_index'])->name('deposit');
    Route::post('/deposit',[DepositController::class,'deposit_store'])->name('deposit.store');
    Route::get('/deposit-active-{id}', [DepositController::class,'deposit_active'])->name('deposit.active');
    Route::get('/deposit-inactive-{id}',[DepositController::class, 'deposit_inactive'])->name('deposit.inactive');
    Route::get('/deposit-delete/{id}',[DepositController::class, 'deposit_delete'])->name('deposit.delete');
    Route::post('/deposit-update-{id}', [DepositController::class, 'deposit_update'])->name('deposit.update');
    Route::post('/update-setting',[SettingController::class,'update_setting'])->name('update_setting');
    Route::get('/deposit-delete-all',[DepositController::class, 'deposit_delete_all'])->name('deposit.delete_all');  
    
    Route::post('/deposit_success/{id}',[DepositController::class,'payin_success'])->name('payin_success');
    Route::get('/feedback',[FeedbackController::class,'feedback_index'])->name('feedback');
    Route::post('/feedback',[FeedbackController::class,'feedback_store'])->name('feedback.store');
    Route::get('/feedback-delete-{id}',[FeedbackController::class, 'feedback_delete'])->name('feedback.delete');
    Route::post('/feedback-update-{id}', [FeedbackController::class, 'feedback_update'])->name('feedback.update');
    
    Route::get('/widthdrawl/{id}',[WidthdrawlController::class,'widthdrawl_index'])->name('widthdrawl');
    Route::post('/widthdrawl',[WidthdrawlController::class,'widthdrawl_store'])->name('widthdrawl.store');
    Route::get('/widthdrawl-delete-{id}',[WidthdrawlController::class, 'widthdrawl_delete'])->name('widthdrawl.delete');
    Route::post('/widthdrawl-update-{id}', [WidthdrawlController::class, 'widthdrawl_update'])->name('widthdrawl.update');
    // Route::post('/widthdrawl-active-{id}', [WidthdrawlController::class,'success'])->name('widthdrawl.success');
    Route::post('/only-success-{id}', [WidthdrawlController::class, 'only_success'])->name('widthdrawl.only_success_id');
    Route::post('/widthdrawl/reject/{id}', [WidthdrawlController::class, 'reject'])->name('widthdrawl.reject');
    Route::post('/widthdrawl-upi-{id}', [WidthdrawlController::class,'success_by_upi'])->name('widthdrawl.upi');

	Route::post('/payzaar_withdraw', [WidthdrawlController::class, 'PayzaaarWitdhraw'])->name('widthdrawl.success');
    //////////////////////////////
    
    Route::get('/widthdraw/success/payout/{id}',[WidthdrawlController::class,'sendEncryptedPayoutRequest'])->name('withdraw.success');
    Route::get('/indiaonlin_payout-{id}', [WidthdrawlController::class,'indiaonlin_payout'])->name('indiaonlin_payout.success');
    Route::post('/widthdrawl-all-success',[WidthdrawlController::class, 'all_success'])->name('widthdrawl.all_success');

    Route::get('/complaint',[ComplaintController::class,'complaint_index'])->name('complaint');
    Route::post('/complaint',[ComplaintController::class,'complaint_store'])->name('complaint.store');
    Route::get('/complaint-delete-{id}',[ComplaintController::class, 'complaint_delete'])->name('complaint.delete');
    Route::post('/complaint-update-{id}', [ComplaintController::class, 'complaint_update'])->name('complaint.update');
    
    Route::get('/revenue',[RevenueController::class,'revenue_create'])->name('revenue');
    Route::post('/revenue',[RevenueController::class,'revenue_store'])->name('revenue.store');
    Route::get('/revenue-delete-{id}',[RevenueController::class, 'revenue_delete'])->name('revenue.delete');
    Route::post('/revenue-update-{id}', [RevenueController::class, 'revenue_update'])->name('revenue.update');
    
    //plinko
     Route::get('/plinko-index',[PlinkoController::class, 'index'])->name('plinko');
    Route::get('/all_bet_history/{id}',[AllBetHistoryController::class, 'all_bet_history'])->name('all_bet_history');
    // routes/web.php
    Route::post('/referral/update/{id}', [UserController::class, 'updatereferral'])->name('referral.update');

    Route::get('/plinkobet_hostory', [PlinkoBetController::class, 'Plinko_Bet_History'])->name('plinko_bet_history');
    Route::get('/minen', [PlinkoBetController::class, 'Mines_Bet_History'])->name('mines_bet_history');
    
 	Route::get('/usdt_deposit/{id}',[UsdtDepositController::class,'usdt_deposit_index'])->name('usdt_deposit');
    Route::post('/usdt_deposit',[UsdtDepositController::class,'usdt_deposit_store'])->name('usdt_deposit.store');
    Route::get('/usdt_success/{id}',[UsdtDepositController::class,'usdt_success'])->name('usdt_success');
    Route::get('/usdt_reject/{id}',[UsdtDepositController::class,'usdt_reject'])->name('usdt_reject');
    Route::get('/usdt_deposit-active-{id}', [UsdtDepositController::class,'usdt_deposit_active'])->name('usdt_deposit.active');
    Route::get('/usdt_deposit-inactive-{id}',[UsdtDepositController::class, 'usdt_deposit_inactive'])->name('usdt_deposit.inactive');
    Route::get('/usdt_deposit-delete-{id}',[UsdtDepositController::class, 'usdt_deposit_delete'])->name('usdt_deposit.delete');
    Route::post('/usdt_deposit-update-{id}', [UsdtDepositController::class, 'usdt_deposit_update'])->name('usdt_deposit.update');


	///// USDT Withdraw ///////
    Route::get('/usdt_widthdrawl/{id}',[UsdtWidthdrawController::class,'usdt_widthdrawl_index'])->name('usdt_widthdrawl');
    Route::post('/usdt_widthdrawl',[UsdtWidthdrawController::class,'usdt_widthdrawl_store'])->name('usdt_widthdrawl.store');
    Route::get('/usdt_widthdrawl-delete-{id}',[UsdtWidthdrawController::class, 'usdt_widthdrawl_delete'])->name('usdt_widthdrawl.delete');
    Route::post('/usdt_widthdrawl-update-{id}', [UsdtWidthdrawController::class, 'usdt_widthdrawl_update'])->name('usdt_widthdrawl.update');
    //Route::get('/usdt_withdraw/{id}', [UsdtWidthdrawController::class,'usdt_success'])->name('usdt_widthdrawl.success');
    Route::post('/usdt_withdraw/{id}', [UsdtWidthdrawController::class, 'usdt_success'])->name('usdt_widthdrawl.success');
    Route::get('/usdt_widthdrawl-inactive-{id}',[UsdtWidthdrawController::class, 'usdt_reject'])->name('usdt_widthdrawl.reject');
    Route::post('/usdt_widthdrawl-all-success',[UsdtWidthdrawController::class, 'usdt_all_success'])->name('usdt_widthdrawl.all_success');

	Route::get('/usdt_qr',[UsdtController::class, 'usdt_view'])->name('usdtqr');
   	Route::post('/update_usdtqr/{id}', [UsdtController::class, 'update_usdtqr'])->name('usdtqr.update');
   	
   	/////////////////manualDepositstarat ////////////
 	Route::get('/manual_deposit/{id}',[ManualDepositController::class,'manual_deposit_index'])->name('manual_deposit');
    Route::post('/manual_deposit',[ManualDepositController::class,'manual_deposit_store'])->name('manual_deposit.store');
    Route::get('/manual_success/{id}',[ManualDepositController::class,'manual_success'])->name('manual_success');
    Route::get('/manual_reject/{id}',[ManualDepositController::class,'manual_reject'])->name('manual_reject');
    Route::get('/manual_deposit-active-{id}', [ManualDepositController::class,'manual_deposit_active'])->name('manual_deposit.active');
    Route::get('/manual_deposit-inactive-{id}',[ManualDepositController::class, 'manual_deposit_inactive'])->name('manual_deposit.inactive');
    Route::get('/manual_deposit-delete-{id}',[ManualDepositController::class, 'manual_deposit_delete'])->name('manual_deposit.delete');
    Route::post('/manual_deposit-update-{id}', [ManualDepositController::class, 'manual_deposit_update'])->name('manual_deposit.update');


	///// USDT Withdraw ///////
    Route::get('/manual_widthdrawl/{id}',[ManualWidthdrawController::class,'manual_widthdrawl_index'])->name('manual_widthdrawl');
    Route::post('/manual_widthdrawl',[ManualWidthdrawController::class,'manual_widthdrawl_store'])->name('manual_widthdrawl.store');
    Route::get('/manual_widthdrawl-delete-{id}',[ManualWidthdrawController::class, 'manual_widthdrawl_delete'])->name('manual_widthdrawl.delete');
    Route::post('/manual_widthdrawl-update-{id}', [ManualWidthdrawController::class, 'manual_widthdrawl_update'])->name('manual_widthdrawl.update');
    //Route::get('/manual_withdraw/{id}', [ManualWidthdrawController::class,'manual_success'])->name('manual_widthdrawl.success');
    Route::post('/manual_withdraw/{id}', [ManualWidthdrawController::class, 'manual_success'])->name('manual_widthdrawl.success');
    Route::post('/manual_widthdrawl-inactive-{id}',[ManualWidthdrawController::class, 'manual_reject'])->name('manual_widthdrawl.reject');
    Route::post('/manual_widthdrawl-all-success',[ManualWidthdrawController::class, 'manual_all_success'])->name('manual_widthdrawl.all_success');
   	
    //TripleChanceController
    Route::controller(TripleChanceController::class)->group(function () {
       
        Route::any('/triplechance_bets_history', 'bets')->name('triplechance.bets');
        Route::any('/triplechance_results', 'results')->name('triplechance.results');
    });
    
    Route::controller(FunTargetController::class)->group(function () { /////funtarget controller
       
        Route::any('/fun_adminresults', 'fun_adminresults')->name('fun.adminresults');
        Route::any('/fun_bets_history', 'fun_bets')->name('fun.bets');
        Route::any('/fun_results', 'fun_results')->name('fun.results');
        Route::post('/admin_prediction', 'admin_prediction2')->name('admin_prediction2');
        Route::post('/fun_fetch2', 'fun_fetch_data2')->name('funfetch_data2');
    });
    Route::get('/profit_summary', [FunTargetController::class, 'getProfitSummary']);
    Route::any('/funwin', [FunTargetController::class, 'fun_index'])->name('fun.index');
    Route::post('/fun-update',[FunTargetController::class, 'fun_update'])->name('fun.update');
    Route::post('/future-result/store', [FunTargetController::class, 'store'])->name('funfuture_result.store');


    Route::get('/andar_bahar/{gameid}',[AndarbaharGameController::class, 'ab_create'])->name('andar_bahar');
	Route::get('/ab_fetch/{gameid}', [AndarbaharGameController::class, 'ab_fetchData'])->name('ab_fetch_data');

    Route::post('/ab-store',[AndarbaharGameController::class, 'ab_store'])->name('ab.store');
    Route::post('/ab_scheduleFutureResult', [AndarbaharGameController::class, 'ab_scheduleFutureResult'])->name('ab_scheduleFutureResult');
    Route::post('ab_future_result_store', [AndarbaharGameController::class, 'ab_future_result_store'])->name('ab_future_result_store');

    // Dragon tiger ///
    Route::get('/dragon/{gameid}',[DragonAdminController::class, 'dragon_create'])->name('dragon');
    	 Route::get('/dragon_fetch/{gameid}', [DragonAdminController::class, 'dragon_fetchData'])->name('dragon_fetch_data');
    
         Route::post('/dragon-store',[DragonAdminController::class, 'dragon_store'])->name('dragon.store');
     Route::post('/dragon_scheduleFutureResult', [DragonAdminController::class, 'dragon_scheduleFutureResult'])->name('dragon_scheduleFutureResult');
     Route::post('dragon_future_result_store', [DragonAdminController::class, 'dragon_future_result_store'])->name('dragon_future_result_store');

    // Head And Tails ///
    Route::get('/head&tail/{gameid}',[HeadtailGameController::class, 'ht_create'])->name('headtail');
    	 Route::get('/ht_fetch/{gameid}', [HeadtailGameController::class, 'ht_fetchData'])->name('ht_fetch_data');
    
         Route::post('/head&tail-store',[HeadtailGameController::class, 'ht_store'])->name('ht.store');
     Route::post('/head&tail_scheduleFutureResult', [HeadtailGameController::class, 'ht_scheduleFutureResult'])->name('ht_scheduleFutureResult');
     Route::post('ht_future_result_store', [HeadtailGameController::class, 'ht_future_result_store'])->name('ht_future_result_store');





    //Lucky12Controller
    Route::controller(Lucky12Controller::class)->group(function () {
       
        Route::any('/lucky12_bets_histroy', 'bets')->name('lucky12.bets');
        Route::any('/lucky12_results', 'results')->name('lucky12.results');
        //Route::any('/lucky12.index', 'lucky12')->name('lucky12.index');
        //Route::post('/admin_prediction','admin_prediction')->name('admin_prediction');
        Route::post('/fetch','fetch_data12')->name('fetch_data12');
    });

    Route::any('/lucky12', [Lucky12Controller::class, 'index'])->name('lucky12.index');
    Route::post('/game_setting', [Lucky12Controller::class, 'game_setting'])->name('game_setting');
    Route::post('/admin_prediction', [Lucky12Controller::class, 'admin_prediction'])->name('admin_prediction');
    Route::post('/lucky12-update',[Lucky12Controller::class, 'lucky12_update'])->name('lucky12.update');


    //Lucky16 
    Route::controller(Lucky16Controller::class)->group(function () {
       
    Route::any('/lucky16_bets_histroy', 'bets')->name('lucky16.bets');
    Route::any('/lucky16_results', 'results')->name('lucky16.results');
    //Route::post('/admin_prediction1', 'admin_prediction1')->name('admin_prediction1');
    Route::post('/fetch1', 'fetch_data1')->name('fetch_data1');
    });
    Route::any('/lucky16', [Lucky16Controller::class, 'index'])->name('lucky16.index');
    Route::get('/fetch_lucky_16', [Lucky16Controller::class, 'fetch_lucky_16'])->name('fetch_lucky_16');
    Route::post('/admin_prediction', [Lucky16Controller::class, 'admin_prediction'])->name('admin_prediction');
    
    Route::post('/lucky16-update',[Lucky16Controller::class, 'lucky16_update'])->name('lucky16.update');


		Route::controller(miniroulleteadminController::class)->group(function(){
        	Route::any('mini_admin_result', 'mini_winneradmin')->name('MiniRoulete.adminwinresult');
			Route::any('mini_winner_results', 'MiniRoulete_update')->name('miniroullete');
			Route::any('mini_bet_results', 'MiniRoulete_betresult')->name('MiniRoulete_betresult');
			Route::any('mini_bet_history', 'MiniRoulete_bethistory')->name('MiniRoulete_bethistory');
		});
		Route::controller(TitliadminController::class)->group(function(){
			 Route::get('game-manage', 'game_manage')->name('titli.index');
			 Route::get('game-result', 'game')->name('titli.result');
			Route::get('admin-result', 'admin_result')->name('titli.index2');
        	Route::post('winner_result', 'admin_winner')->name('titli.add');
			Route::any('winner_resultsss', 'update')->name('updateData');
		});

		Route::controller(hotairballoonController::class)->group(function(){
				   Route::get('hotairballoonss', 'hotairballoon')->name('hotairballoon.hotairbethistory');  //bet history
				   Route::get('hotairballoon_resultss', 'hotairballoon_result')->name('hotairballoon.hotairbetresult'); // bet result
			});
		Route::any('/hotair_store/{game_id}',[hotairballoonController::class, 'hotair_store'])->name('hotairballoon.stores',23);
		Route::any('/hotair_percentage_update/{game_id}', [hotairballoonController::class, 'hotair_update'])->name('hotair_percentage.update');
        Route::get('/hotair/{game_id}',[hotairballoonController::class, 'hotair_prediction_create'])->name('hotairresult');
		//Route::any('/bet-history/{game_id}', [hotairballoonController::class, 'hotair_bet_history'])->name('bet_history');

		Route::controller(jackpotController::class)->group(function(){
				   Route::get('jackpot', 'jackpot')->name('jackpot.jackpotbethistory'); ///bethistory
				   Route::get('jackpot_result', 'jackpot_result')->name('jackpot.jackpotbetresult');//
				   Route::any('jckpt_winner', 'jckpt_winner')->name('jackpot.jackpotadminresults');
        		   Route::post('jackpot_win_update', 'jack_update')->name('jackpot.jackpotadminWinner');
			});

		Route::controller(highlowController::class)->group(function(){
				   Route::get('hilo', 'hilo')->name('hilo.hilobethistory');//bet history
				   Route::get('hilo_result', 'hilo_result')->name('hilo.hiloresult');//result
				   Route::any('hilo_winner', 'hilo_winner')->name('hilo.hiloadminresult');
        		   Route::any('result', 'update_winner')->name('adminWinner.adds');
			});
	Route::controller(teenadminController::class)->group(function(){
        	Route::any('admin_result', 'teen_winner')->name('teen.adminwinresult');
			Route::any('teen_winner_results', 'teenupdate_winner')->name('teenpatti');
		
			Route::any('teen_bet_results', 'teen_betresult')->name('teen_betresult');
			Route::any('teen_bet_history', 'teen_bethistory')->name('teen_bethistory');
		});



		 Route::controller(jhandimundaController::class)->group(function(){
	 	 	 Route::any('jm_winner', 'jm_winner')->name('jm_jmadminresult');
        	 Route::any('jhandi_win', 'jhandi_win')->name('jm.jm1');  //adminwinner result
		  	 Route::get('jhandimunda', 'jhandimunda')->name('jm.jmbethistory'); //bet_history
        	 Route::any('jhandimunda_result', 'jhandimunda_result')->name('jm.jmresult'); //jm bet result
	 	});

		Route::controller(SpinController::class)->group(function () { /////funtarget controller

			Route::any('/spin_adminresults', 'adminresults')->name('spin.adminresults');
			Route::any('/spin_bets_history', 'bets')->name('spin.bets');
			Route::any('/spin_results', 'results')->name('spin.results');
			//Route::post('/admin_prediction', 'admin_prediction2')->name('admin_prediction2');
			Route::post('/fetch2', 'fetch_data2')->name('fetch_data2');
		});
		Route::any('/spin2win', [SpinController::class, 'index'])->name('spin.index');
		Route::post('/spin-update',[SpinController::class, 'spin_update'])->name('spin.update');



	 Route::controller(UpAndDownController::class)->group(function(){
	 	 	Route::get('7updown', 'updown')->name('7updown.bets');
		    Route::get('7updown_result', 'updown_result')->name('7updown.results');
		  	Route::get('updown_winner', 'updown_winner')->name('7updown.admin.result');
        	Route::post('updown_update', 'updown_update')->name('7updown.updown_update');
	 });

        Route::controller(RedBlackController::class)->group(function(){
			  Route::get('redBlack', 'redBlack')->name('redblack.bets');
			  Route::get('redBlack_result', 'redBlack_result')->name('redblack.results');
        	  Route::get('rb_winner', 'rb_winner')->name('redblack.admin.result');
        	  Route::post('redblack_win', 'redblack_win')->name('adminWinner.redblack_win');
		});

		Route::controller(KinoController::class)->group(function(){
				   Route::get('kino', 'kino')->name('kino.bets');//bet history
				   Route::get('kino_result', 'kino_result')->name('kino.results');//result
				   Route::get('kino_winner', 'kino_winner')->name('kino.admin.result');
        		   Route::post('result_kino', 'update_winner')->name('adminWinner.addkino');
			});
		Route::controller(DiceController::class)->group(function(){
				   Route::get('Dice', 'Dice')->name('Dice.bets');//bet history
				   Route::get('Dice_result', 'Dice_result')->name('Dice.results');//result
				   Route::get('Dice_winner', 'Dice_winner')->name('Dice.admin.result');
        		   Route::post('Dice_result', 'Dice_win')->name('DiceadminWinner.dice_win');
			
				   Route::get('Dice_nextGameNo', 'Dice_nextGameNo')->name('dice.nextGameNo');
			});

				




