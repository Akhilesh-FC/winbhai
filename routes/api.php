<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\PayinController;
use App\Http\Controllers\Api\GameApiController;
use App\Http\Controllers\Api\AgencyPromotionController;
use App\Http\Controllers\Api\VipController;
use App\Http\Controllers\Api\ZiliApiController;
use App\Http\Controllers\Api\{TrxApiController,IfscApiController,ChikangameController};
use App\Http\Controllers\WidthdrawlController;
use App\Http\Controllers\Api\AviatorApiController;
use App\Http\Controllers\Api\ThirdpartyApiController;
use App\Http\Controllers\Api\jiliApiController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryLanguageController;
use App\Http\Controllers\Api\CommissionController;


Route::get('/admin_notifications', [CategoryLanguageController::class, 'admin_notifications']);



Route::get('/game_subcat_sliders', [CategoryLanguageController::class, 'game_subcat_sliders']);

Route::post('/history/store', [CategoryLanguageController::class, 'storeGameHistory']);
Route::post('/history/latest', [CategoryLanguageController::class, 'getRecentHistory']);


Route::get('/get_casino_lobby', [CategoryLanguageController::class, 'get_casino_lobby']);

Route::get('/all-subcategories', [BrandController::class, 'getFullSubCategoryList']);
Route::post('/subcategories-by-cat', [BrandController::class, 'getSubCategoryByCatId']);



Route::get('/category-language-data', [CategoryLanguageController::class, 'getData']);
Route::get('/get-category-language-data', [CategoryLanguageController::class, 'getFilteredData']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get_brands', [BrandController::class, 'get_Brands']);
Route::get('/brand-details/{brand_id}', [BrandController::class, 'getBrandDetails']);


Route::post('/betSummaryProfit_loss', [PublicApiController::class, 'betSummaryProfit_loss']);

Route::post('/campaign_click', [PublicApiController::class,  'trackClick']);

Route::post('/campaign_create', [PublicApiController::class, 'campaign_create']);
Route::post('/campaign_list', [PublicApiController::class, 'campaign_list']);
Route::post('/campaign_summary', [PublicApiController::class, 'campaign_summary']);
Route::post('/campaign_analytics', [PublicApiController::class, 'campaign_analytics']);
//Route::get('/campaign-summary/download', [PublicApiController::class, 'downloadCampaignSummary']);
Route::get(
    '/campaign-analytics/download',
    [PublicApiController::class, 'downloadCampaignSummary']
);

Route::post('/campaign_commission_summary', [PublicApiController::class, 'campaign_commission_summary']);
//Route::get('/calculateAndCreditAllCampaignsCommission',[PublicApiController::class, 'calculateAndCreditAllCampaignsCommission']);
Route::get('/run-all-campaign-commission',
    [CommissionController::class, 'runAllCampaignCommission']
);




Route::post('/pending-bets', [PublicApiController::class, 'getPendingBets']);
Route::post('/betHistory_winbhai', [PublicApiController::class, 'betHistory_winbhai']);
Route::post('/AccountStatement', [PublicApiController::class, 'AccountStatement']);
Route::post('/crypto', [PublicApiController::class, 'crypto']);

Route::controller(jiliApiController::class)->group(function () {
    // Route::post('/jilliGame', 'jilliGame');   
    // Route::get('/getJilliGames', 'getJilliGames');  
   // Route::get('/games/{brand}', 'getGames');        // ✅ Get games list by brand
   Route::get('/get-games/{brand_id}', 'getGames');
   // ✅ Get games list by brand
    Route::post('/openGame', 'openGame');  
    //Route::get('/brands',  'getBrands');
    Route::get('/brands_selected', 'getSelectedBrands');
    
    // Example: /api/game-history-filter?user_id=34&game_id=3
    Route::get('/game-history-filter', 'filterGameHistory');
    // 2️⃣ USER FULL GAME HISTORY
    // Example: /api/game-history/34
    Route::get('/game-history/{user_id}','userGameHistory');
    
    Route::post('/game-callback',  'gameCallback');

});




Route::controller(ThirdpartyApiController::class)->group(function () {
   Route::get('/test-api', 'hitApi'); 
    // Callback API (game result aayega yaha)
    Route::post('/callback', [ThirdpartyApiController::class, 'handleCallback']);
    // GGR Billing Check
    Route::get('/billing/{token}', [ThirdpartyApiController::class, 'checkBilling']);
});

Route::controller(ChikangameController::class)->group(function () {
    Route::post('/bet', 'bet');                 // POST /bet
    Route::get('/bet_values', 'bet_values');     // GET /bet/values
    Route::get('/history', 'betHistory');    // GET /bet/history
    Route::post('/cashout', 'cashout');      // POST /bet/cashout
    Route::get('/multiplier', 'multiplier');           // GET /multiplier
    Route::get('/getGameRules', 'getGameRules'); // GET /multiplier/getGameRules
    
    Route::get('/chicken/auto-loss',  'autoLossChicken30Sec');
});



Route::post('/update-wallet', [WidthdrawlController::class, 'deductAmount']);

//Route::post('/payzaaar',[PayinController::class,'payzaaar']);
Route::post('/bappa_venture',[PayinController::class,'bappa_venture']);
Route::post('/decode_data',[PayinController::class,'decode_data']);
Route::post('/payzaaar-callback', [PayinController::class, 'payzaaarCallback']);
Route::get('/check-payzaar-payment', [PayinController::class, 'checkPayzaaarPayment']);
Route::get('/checkPayment', [PayinController::class, 'checkPayment']);

Route::post('/usdt_payin',[PayinController::class,'usdt_payin']);
Route::get('/show_qr',[PayinController::class,'qr_view']);
Route::get('/payin-successfully',[PayinController::class,'redirect_success'])->name('payin.successfully');

Route::post('/manual_payin',[PayinController::class,'manual_payin']);
Route::get('/get-qr-codes', [PayinController::class, 'getAllQrCodes']);
 


// PublicApiController
Route::get('/gameSerialNo',[GameApiController::class,'gameSerialNo']);
Route::get('/fetch_admin_set_Results/{id}',[GameApiController::class,'fetch_admin_set_Results'])->name('fetch_admin_set_Results');

Route::get('get-ifsc-details', [IfscApiController::class, 'getIfscDetails']);
Route::get('/getUrlIp',[PublicApiController::class,'getUrlIp']);
Route::get('/getAllNotices',[PublicApiController::class,'getAllNotices']);
Route::post('/login',[PublicApiController::class,'login']);
Route::post('/register',[PublicApiController::class,'register']);
Route::post('/country',[PublicApiController::class,'country']);
Route::post('/forget_pass',[PublicApiController::class,'forget_pass']);
Route::post('/forget_username',[PublicApiController::class,'forget_username']);
Route::get('/profile',[PublicApiController::class,'profile']);
Route::post('/update_profile',[PublicApiController::class,'update_profile']);
Route::get('/slider_image_view',[PublicApiController::class,'slider_image_view']);
Route::get('/deposit_history',[PublicApiController::class,'deposit_history']);
Route::get('/withdraw_history',[PublicApiController::class,'withdraw_history']);
Route::get('/privacy_policy',[PublicApiController::class,'Privacy_Policy']);
Route::get('/about_us',[PublicApiController::class,'about_us']);
Route::get('/Terms_Condition',[PublicApiController::class,'Terms_Condition']);
Route::get('/contact_us',[PublicApiController::class,'contact_us']);
Route::get('/support',[PublicApiController::class,'support']);
Route::get('/attendance_List',[PublicApiController::class,'attendance_List']);
Route::get('/image_all',[PublicApiController::class,'image_all']);
Route::get('/transaction_history_list',[PublicApiController::class,'transaction_history_list']);
Route::get('/transaction_history',[PublicApiController::class,'transaction_history']);
Route::get('/Status_list',[PublicApiController::class,'Status_list']);
Route::get('/pay_modes',[PublicApiController::class,'pay_modes']);
Route::post('/add_account',[PublicApiController::class,'add_account']);
Route::post('/add_usdt_wallet_address',[PublicApiController::class,'add_usdt_wallet_address']);
Route::post('/view_usdt_wallet_address', [PublicApiController::class, 'view_usdt_wallet_address']);

Route::get('/Account_view',[PublicApiController::class,'Account_view']);
Route::post('/withdraw',[PublicApiController::class,'withdraw']);
Route::post('/affiliation_wallet_add',[PublicApiController::class,'affiliation_wallet_add']);
Route::post('/affiliate_withdraw',[PublicApiController::class,'affiliate_withdraw']);
Route::get('/notification',[PublicApiController::class,'notification']);
Route::post('/feedback',[PublicApiController::class,'feedback']);
Route::post('/gift_cart_apply',[PublicApiController::class,'giftCartApply']);
Route::get('/coupon_show',[PublicApiController::class,'coupon_show']);
Route::get('/gift_redeem_list',[PublicApiController::class,'claim_list']);
Route::post('/bonus_info',[PublicApiController::class,'bonusInfo']); /// 
Route::get('/customer_service',[PublicApiController::class,'customer_service']);
Route::post('/contact_info',[PublicApiController::class,'contactInfo']);
Route::post('/update_avatar',[PublicApiController::class,'update_profile']);
Route::post('/changePassword',[PublicApiController::class,'changePassword']);
Route::post('/main_wallet_transfer',[PublicApiController::class,'main_wallet_transfer']);
Route::get('/activity_rewards',[PublicApiController::class,'activity_rewards']);
Route::Post('/activity_rewards_claim',[PublicApiController::class,'activity_rewards_claim']);
Route::get('/activity_rewards_history',[PublicApiController::class,'activity_rewards_history']);
Route::get('/invitation_bonus_list',[PublicApiController::class,'invitation_bonus_list']);
Route::get('/Invitation_reward_rule',[PublicApiController::class,'Invitation_reward_rule']);
Route::get('/Invitation_records',[PublicApiController::class,'Invitation_records']);
Route::get('/extra_first_deposit_bonus',[PublicApiController::class,'extra_first_deposit_bonus']);
Route::post('/extra_first_deposit',[PublicApiController::class,'extra_first_deposit']);
Route::get('/attendance_List',[PublicApiController::class,'attendance_List']);
Route::get('/attendance_history',[PublicApiController::class,'attendance_history']);
Route::post('/attendance_claim',[PublicApiController::class,'attendance_claim']);
Route::get('/level_getuserbyrefid',[PublicApiController::class,'level_getuserbyrefid']);
Route::get('/commission_details',[PublicApiController::class,'commission_details']);
Route::get('/all_rules',[PublicApiController::class,'all_rules']);
Route::get('/subordinate_userlist',[PublicApiController::class,'subordinate_userlist']);
Route::get('/betting_rebate',[PublicApiController::class,'betting_rebate']);
Route::get('/betting_rebate_history',[PublicApiController::class,'betting_rebate_history']);
Route::get('/version_apk_link', [PublicApiController::class, 'versionApkLink']);
Route::post('/extra_first_payin',[PublicApiController::class,'extra_first_payin']);
Route::get('/checkPayment1',[PublicApiController::class,'checkPayment1']);
Route::post('/invitation_bonus_claim',[PublicApiController::class,'invitation_bonus_claim']);
Route::get('/invitation_bonus_list_old',[PublicApiController::class,'invitation_bonus_list_old']);
Route::post('/usdtwithdraw',[PublicApiController::class,'usdtwithdraw']);
Route::post('/affiliation_usdtwithdraw',[PublicApiController::class,'affiliation_usdtwithdraw']);
Route::post('/indianpay_withdraw',[PublicApiController::class,'indianpay_withdraw']);
Route::post('/affiliation_indianpay_withdraw',[PublicApiController::class,'affiliation_indianpay_withdraw']);
Route::get('/total_bet_details',[PublicApiController::class,'total_bet_details']);
Route::get('/getPaymentLimits',[PublicApiController::class,'getPaymentLimits']);
Route::get('/commission_distribution',[PublicApiController::class,'commission_distribution']);

//// VIP Routes////
Route::get('/vip_level',[VipController::class,'vip_level']);
Route::get('/vip_level_history',[VipController::class,'vip_level_history']);
Route::post('/add_money',[VipController::class,'receive_money']);

//Game Controller//
Route::controller(GameApiController::class)->group(function () {
     Route::post('/bets', 'bet'); //wingo,HT,TRX
     Route::post('/bets_new', 'bet_new');
     Route::post('/dragon_bet', 'dragon_bet'); //DT, AB, 7updown , red7black
     Route::get('/win-amount', 'win_amount');
     Route::get('/results','results');
     Route::get('/last_five_result','lastFiveResults');
     Route::get('/last_result','lastResults');
     Route::post('/bet_history','bet_history');
     Route::get('/cron/{game_id}/','cron');
     Route::get('/get_results','get_result');
	 Route::post('/auto_wingo_result_insert','auto_wingo_result_insert')->name('auto_wingo_result_insert');
	 Route::get('/fetchhistory','fetchhistory')->name('fetchhistory');


Route::get('/trx/result',[TrxApiController::class, 'trx_result_new']);
Route::get('/trx/results',[TrxApiController::class, 'trx_results']);
Route::get('/trx/results_by_periodno',[TrxApiController::class, 'get_result_by_periodno']);
Route::get('/trx/update_result_cron',[TrxApiController::class, 'trx_cron_result_update']);

    // Plinko Game Route /////
Route::post('/plinko_bet','plinkoBet');
Route::post('/plinko_bet_new','plinkoBet_new');
Route::get('/plinko_index_list','plinko_index_list');
Route::get('/plinko_result','plinko_result');
Route::get('/plinko_cron','plinko_cron');
Route::post('/plinko_multiplier','plinko_multiplier'); 
});


Route::post('/aviator_bet',[AviatorApiController::class, 'aviator_bet']);
Route::get('/aviator_bet_new',[AviatorApiController::class, 'aviator_bet_new']);
Route::post('/aviator_cashout',[AviatorApiController::class, 'aviator_cashout']);
Route::post('/aviator_history',[AviatorApiController::class, 'aviator_history']);

Route::get('/aviator_last_five_result',[AviatorApiController::class, 'last_five_result']);
Route::get('/aviator_bet_cancel',[AviatorApiController::class, 'bet_cancel']);

//mine
Route::post('/mine_bet',[GameApiController::class, 'mine_bet']);
Route::post('/mine_cashout',[GameApiController::class, 'mine_cashout']);
Route::get('/mine_result',[GameApiController::class,'mine_result']);
Route::get('/mine_multiplier',[GameApiController::class,'mine_multiplier']);
//otp
Route::get('/sendSMS',[PublicApiController::class,'sendSMS']);
Route::get('/verifyOTP',[PublicApiController::class,'verifyOTP']);
Route::post('/updatePassword',[PublicApiController::class,'updatePassword']);

Route::controller(AgencyPromotionController::class)->group(function () {
    Route::get('/agency-promotion-data-{id}', 'promotion_data');
	Route::get('/new-subordinate', 'new_subordinate');
	Route::get('/tier', 'tier');
	Route::get('/subordinate-data','subordinate_data');
	Route::get('/turnovers','turnover_new');
	
});


//  //// Zili Api ///
// Route::post('/user_register',[ZiliApiController::class,'user_register']);  //not in use for registration
// Route::post('/all_game_list',[ZiliApiController::class,'all_game_list']);
// Route::post('/all_game_list_test',[ZiliApiController::class,'all_game_list_test']);
// Route::post('/get_game_url',[ZiliApiController::class,'get_game_url']);
// Route::post('/get_jilli_transactons_details',[ZiliApiController::class,'get_jilli_transactons_details']);
// Route::post('/jilli_deduct_from_wallet',[ZiliApiController::class,'jilli_deduct_from_wallet']);
// Route::post('/jilli_get_bet_history',[ZiliApiController::class,'jilli_get_bet_history']);
// Route::post('/add_in_jilli_wallet ',[ZiliApiController::class,'add_in_jilli_wallet']);
// Route::post('/update_main_wallet ',[ZiliApiController::class,'update_main_wallet']);
// Route::post('/get_jilli_wallet ',[ZiliApiController::class,'get_jilli_wallet']);
// Route::post('/update_jilli_wallet ',[ZiliApiController::class,'update_jilli_wallet']);
// Route::post('/update_jilli_to_user_wallet ',[ZiliApiController::class,'update_jilli_to_user_wallet']);

// Route::get('/test_get_user_info ',[ZiliApiController::class,'test_get_user_info']);
// Route::get('/get-reseller-info/{manager_key?}',[ZiliApiController::class,'get_reseller_info']);


 
 