<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Post\PostController;
use App\Http\Controllers\Api\Post\LocationController;
use App\Http\Controllers\Api\Post\Jobs\JobsController;
use App\Http\Controllers\Api\Post\Jobs\JobsVacancyController;
use App\Http\Controllers\Api\Post\Jobs\JobsApplicationController;
use App\Http\Controllers\Api\Post\Jobs\Skill\JobsSkillController;
use App\Http\Controllers\Api\Company\CompanyController;
use App\Http\Controllers\Api\Company\CompanyItemController;
use App\Http\Controllers\Api\Company\AddressController;
use App\Http\Controllers\Api\Company\PhotoController;
use App\Http\Controllers\Api\UserDocs\DocsCategoryController;
use App\Http\Controllers\Api\UserDocs\UserDocsController;
use App\Http\Controllers\Api\Skill\UserSkillController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\HowToPayController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CovidController;
use App\Http\Controllers\Pulsa\PulsaController;
use App\Http\Controllers\Pulsa\ServiceCategoryController;
use App\Http\Controllers\Pulsa\ProductGroupController;
use App\Http\middleware\SanctumConfigForUser;

use App\Http\Controllers\Api\Hotel\HotelController;
use App\Http\Controllers\Api\Hotel\HotelBookingController;
use App\Http\Controllers\Api\Hotel\HotelImageController;
use App\Http\Controllers\Api\Hotel\HotelRatingController;
use App\Http\Controllers\Api\Hotel\HotelRoomController;
use App\Http\Controllers\Api\Hotel\HotelRoomImageController;
use App\Http\Controllers\Api\Hotel\HotelRoomBedTypeController;
use App\Http\Controllers\Api\Hotel\FacilitiesController;
use App\Http\Controllers\Api\Hotel\HotelDarmaController;
use App\Http\Controllers\Api\Ticket\TicketController;
use App\Http\Controllers\Api\Property\PropertyDataController;
use App\Http\Controllers\Api\PostCategoryController;
use App\Http\Controllers\Api\Search\SearchController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\Midtrans\MidtransController;
use App\Http\Controllers\Api\Forum\ForumController;
use App\Http\Controllers\Api\Report\ReportController;
use App\Http\Controllers\Api\Restaurant\RestaurantController;

use App\Http\Controllers\Api\Post\Jobs\v2\JobVacancyController;
use App\Http\Controllers\Api\Post\Jobs\v2\JobApplicantController;
use App\Http\Controllers\Api\Post\Jobs\v2\UserQualificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('server-status', [UserController::class, 'serverStatus']);

Route::post('login', [UserController::class, 'login']);
Route::post('check', [UserController::class, 'check']);
Route::post('login/google', [UserController::class, 'loginWithGoogle']);
Route::post('register', [UserController::class, 'register']);
Route::post('search', [SearchController::class, 'searchAll']);
Route::get('checkbalance', [MidtransController::class, 'espayCheckBalance']);


Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('detail', [UserController::class, 'detail']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('photo', [UserController::class, 'updatePhoto']);
    Route::post('profile', [UserController::class, 'updateProfile']);
    Route::post('password', [UserController::class, 'changePassword']);
    
	//old job
	Route::group(['prefix' => 'jobs'], function() {
		Route::post('application/post'  , [JobsApplicationController::class, 'postJobsApplication']);
		Route::post('application/my'  , [JobsApplicationController::class, 'getMyJobApplication']);
		Route::post('vacancy/post'  , [JobsVacancyController::class, 'postJobsVacancy']);
		Route::post('vacancy/addskill'  , [JobsSkillController::class, 'addJobsSkill']);
		Route::post('vacancy/getskill'  , [JobsSkillController::class, 'getJobsSkill']);
		Route::post('vacancy/removeskill'  , [JobsSkillController::class, 'removeJobsSkill']);

		Route::post('check'  , [JobsApplicationController::class, 'checkApplied']);

	});
	//

	//new job//
	Route::group(['prefix' => 'job'], function() {
		//sisi company
		Route::get('vacancy'  , [JobVacancyController::class, 'showVacancy']);
		Route::post('vacancy/post'  , [JobVacancyController::class, 'createVacancy']);
		Route::post('vacancy/update'  , [JobVacancyController::class, 'updateVacancy']);
		Route::post('vacancy/delete'  , [JobVacancyController::class, 'deleteVacancy']);

		Route::get('vacancy/data', [JobVacancyController::class, 'getVacancyData']);

		Route::post('applicant', [JobVacancyController::class, 'showApplicant']);
		Route::post('applicant/detail', [JobVacancyController::class, 'showApplicantDetail']);
		Route::post('applicant/shortlisted', [JobVacancyController::class, 'showShortlist']);
		Route::post('applicant/accepted', [JobVacancyController::class, 'showAcceptedList']);
		Route::post('applicant/interview', [JobVacancyController::class, 'showInterviewList']);

		Route::post('applicant/invite_interview', [JobVacancyController::class, 'interviewInvite']);

		Route::post('applicant/update', [JobVacancyController::class, 'changeApplicantStatus']);

		//sisi user
		Route::post('vacancy/apply'  , [JobApplicantController::class, 'applyJob']);
		Route::post('vacancy/withdraw'  , [JobApplicantController::class, 'withdrawApplication']);
		Route::get('vacancy/docs', [JobApplicantController::class, 'getDocs']);
		Route::post('vacancy/delete_docs', [JobApplicantController::class, 'deleteDocs']);
		Route::get('vacancy/my_applications', [JobApplicantController::class, 'myJobApplications']);
		Route::post('vacancy/application_detail', [JobApplicantController::class, 'getApplicationDetail']);
		Route::get('vacancy/show_all', [JobApplicantController::class, 'showAvailableVacancy']);

		Route::post('vacancy/search', [JobApplicantController::class, 'searchVacancy']);

		Route::post('vacancy/add_bookmark', [JobApplicantController::class, 'addVacancyBookmark']);
		Route::post('vacancy/remove_bookmark', [JobApplicantController::class, 'removeVacancyBookmark']);
		Route::get('vacancy/my_bookmark', [JobApplicantController::class, 'showVacancyBookmark']);
	});
	////

	Route::group(['prefix' => 'company'], function() {

		Route::group(['prefix' => 'jobs'], function() {
			Route::post('/'  , [JobsVacancyController::class, 'getMyJobVacancy']);
			Route::post('/applicant'  , [JobsVacancyController::class, 'getMyJobApplicant']);
			Route::post('/applicant/status'  , [JobsVacancyController::class, 'changeApplicantStatus']);
			

		});

		Route::post('/'  , [CompanyController::class, 'getCompany']);
		Route::post('/register'  , [CompanyController::class, 'registerCompany']);
		Route::post('/applicant'  , [JobsApplicationController::class, 'getCompanyJobApplication']);
		Route::post('/applicant/status'  , [JobsApplicationController::class, 'getJobApplicationStatus']);
		Route::post('/address/register'  , [AddressController::class, 'registerCompanyAddress']);
		Route::post('/photo/register'  , [PhotoController::class, 'registerCompanyMedia']);
		Route::post('/photo/delete'  , [PhotoController::class, 'deleteCompanyMedia']);

		//Route::post('/show'  , [CompanyController::class, 'showCompanyList']);
		Route::post('/show'  , [CompanyController::class, 'listCompanyByDistance']);
		Route::get('/category'  , [CompanyController::class, 'getCompanyCategory']);

		Route::post('/search'  , [CompanyItemController::class, 'globalSearch']);

		//items
		Route::post('item/company', [CompanyItemController::class, 'getCompanyData']);

		Route::post('item/add', [CompanyItemController::class, 'addNewItem']);
		Route::post('item/detail', [CompanyItemController::class, 'showItemDetail']);
		Route::post('item/update', [CompanyItemController::class, 'updateItem']);
		Route::get('item/category/', [CompanyItemController::class, 'getItemCategory']);

		Route::post('item/category/add', [CompanyItemController::class, 'addItemCatalog']);
		Route::post('item/catalog/', [CompanyItemController::class, 'getAllItemCatalog']);
		Route::post('item/catalog/update', [CompanyItemController::class, 'updateCatalog']);
		Route::post('item/catalog/delete', [CompanyItemController::class, 'deleteCatalog']);
		Route::post('item/show/', [CompanyItemController::class, 'showCompanyItem']);
		Route::post('item/show/category', [CompanyItemController::class, 'getItemByCategory']);
		Route::post('item/promoted/', [CompanyItemController::class, 'getPromotedItem']);
		Route::post('item/promoted/update', [CompanyItemController::class, 'updatePromotedItem']);
		Route::post('item/promoted/toggle', [CompanyItemController::class, 'togglePromotedItem']);
		Route::post('item/media/add', [CompanyItemController::class, 'addNewItemMedia']);
		Route::post('item/media/delete', [CompanyItemController::class, 'deleteMedia']);
		Route::post('item/delete', [CompanyItemController::class, 'deleteItem']);
		Route::post('item/search', [CompanyItemController::class, 'searchItem']);

		Route::post('item/suggested', [CompanyItemController::class, 'getCompanyItemSuggestion']);
		Route::post('item/suggested_global', [CompanyItemController::class, 'getGlobalItemSuggestion']);
		
	});
	
	Route::group(['prefix' => 'docs'], function() {

		Route::post('/'  , [UserDocsController::class, 'getUserDocs']);
		Route::post('/add'  , [UserDocsController::class, 'addUserDocs']);
		Route::post('/delete'  , [UserDocsController::class, 'deleteUserDocs']);
	});

	Route::group(['prefix' => 'skill'], function() {
		Route::post('/'  , [UserSkillController::class, 'getUserSkill']);
		Route::post('/add'  , [UserSkillController::class, 'addUserSkill']);
		Route::post('/delete'  , [UserSkillController::class, 'deleteUserSkill']);
	});

	Route::group(['prefix' => 'education'], function() {
		Route::get('/'  , [UserQualificationController::class, 'showLastEducation']);
		Route::post('/add'  , [UserQualificationController::class, 'addLastEducation']);
		Route::post('/delete'  , [UserQualificationController::class, 'deleteLastEducation']);
		Route::post('/update'  , [UserQualificationController::class, 'updateLastEducation']);
	});

	Route::group(['prefix' => 'work_exp'], function() {
		Route::get('/'  , [UserQualificationController::class, 'showWorkExperience']);
		Route::post('/add'  , [UserQualificationController::class, 'addWorkExperience']);
		Route::post('/delete'  , [UserQualificationController::class, 'deleteWorkExperience']);
		Route::post('/update'  , [UserQualificationController::class, 'updateWorkExperience']);
	});

	Route::post('notification'  , [NotificationController::class, 'getUserNotification']);
	Route::post('open-notification'  , [NotificationController::class, 'openNotification']);

	Route::post('/payment/method'  , [PulsaController::class, 'getPaymentMethod']);
	
	//verified email only
	Route::middleware('email.verified')->group(function () {
		Route::group(['prefix' => 'pulsa'],function ()
		{
			
			Route::post('/inquiry'  , [PulsaController::class, 'getInquiry']);
			Route::post('/transaction'  , [PulsaController::class, 'addTransaction']);
			Route::post('/cancel', [PulsaController::class, 'cancelTransaction']);
			Route::post('/list'  , [PulsaController::class, 'transactionList']);
		});

		Route::group(['prefix' => 'bills'],function ()
		{
			Route::post('/inquiry'  , [PulsaController::class, 'getInquiryBills']);
			Route::post('/transaction'  , [PulsaController::class, 'addBillsTransaction']);
			//Route::post('/amountbill'  , [PulsaController::class, 'getAmountBills']);
			Route::post('/directbill'  , [PulsaController::class, 'getDirectBills']);
			Route::post('/cancel', [PulsaController::class, 'cancelTransaction']);
			Route::post('/bill_detail', [PulsaController::class, 'getTransactionDetail']);
		});

		Route::group(['prefix' => 'ticket'], function() {

			Route::post('/airport',[TicketController::class, 'getAirport']);
			Route::post('/airline',[TicketController::class, 'getAirline']);
			Route::post('/schedule',[TicketController::class, 'getAirlineSchedule']);
			Route::post('/price',[TicketController::class, 'getAirlinePrice']);
			Route::post('/route',[TicketController::class, 'getRoute']);
			Route::post('/test',[TicketController::class, 'testOCR']);
			Route::get('/addons',[TicketController::class, 'getAirlineAddons']);
			Route::get('/seat',[TicketController::class, 'getAirlineSeat']);
			Route::post('/setaddons',[TicketController::class, 'setPassengerAddons']);
			Route::post('/passenger',[TicketController::class, 'setPassenger']);
			Route::post('/booking',[TicketController::class, 'setAirlineBooking']);
	
			Route::get('/nationality',[TicketController::class, 'getNationality']);
	
			Route::post('/testbooking',[TicketController::class, 'setBookingManual']);
	
			Route::post('/history',[TicketController::class, 'getBookingList']);
	
	
	
		});
	
		Route::group(['prefix' => 'hotel_darma'], function(){
			Route::post('/search', [HotelDarmaController::class, 'searchHotelQuery']);
			//HotelDarmaController
	
			Route::post('/issue_booking', [HotelDarmaController::class, 'issueBooking']);
			Route::post('/testissue_booking', [HotelDarmaController::class, 'testIssueBooking']);
			Route::post('/create_booking', [HotelDarmaController::class, 'createBooking']);
			Route::post('/price_policy', [HotelDarmaController::class, 'showPricePolicy']);
			Route::post('/search_room', [HotelDarmaController::class, 'searchRoom']);
			Route::post('/search_hotel', [HotelDarmaController::class, 'searchHotel']);
	
			Route::post('/hotel_name', [HotelDarmaController::class, 'searchByHotelName']);
	
			Route::post('/booking_list', [HotelDarmaController::class, 'getBookingList']);
			Route::post('/booking_detail', [HotelDarmaController::class, 'getBookingDetail']);
			Route::post('/booking_data', [HotelDarmaController::class, 'bookingData']);
	
			Route::post('/cancel', [HotelDarmaController::class, 'cancel']);
	
			Route::post('/all_cities', [HotelDarmaController::class, 'getIndoCities']);
			Route::post('/cities', [HotelDarmaController::class, 'getCity']);
			Route::post('/countries', [HotelDarmaController::class, 'getCountry']);
			Route::post('/passports', [HotelDarmaController::class, 'getPassport']);
	
			Route::post('/testimage', [HotelDarmaController::class, 'testImage']);
		});
	});
	

	Route::post('/pending_transaction'  , [PulsaController::class, 'pendingTransactionList']);

	Route::get('/post_category',[PostCategoryController::class, 'getCategory']);

	

	Route::group(['prefix' => 'property'], function(){
		Route::get('/facility', [PropertyDataController::class, 'listFacility']);
		Route::post('/my_property', [PropertyDataController::class, 'showMyProperty']);
		Route::post('/show_property', [PropertyDataController::class, 'showAvailableProperty']);
		Route::post('/new_property', [PropertyDataController::class, 'addProperty']);
		Route::post('/view_property', [PropertyDataController::class, 'getPropertyDetail']);
		Route::post('/update_property', [PropertyDataController::class, 'updatePropertyDetail']);
		Route::post('/bookmark', [PropertyDataController::class, 'changeBookmark']);
		Route::post('/upload_image', [PropertyDataController::class, 'storeImage']);
		Route::post('/new_transaction', [PropertyDataController::class, 'createTransaction']);
		Route::post('/update_transaction', [PropertyDataController::class, 'updateTransaction']);
		Route::post('/my_transaction_list', [PropertyDataController::class, 'showPropertyTransactionList']);
		Route::post('/my_property_transaction_list', [PropertyDataController::class, 'showMyPropertyTransactionList']);
		Route::post('/delete', [PropertyDataController::class, 'deleteProperty']);
	});

	Route::group(['prefix' => 'forum'], function(){
		Route::get('/category', [ForumController::class, 'showCategory']);
		

		Route::post('/new_subforum', [ForumController::class, 'createSubforum']);
		Route::post('/new_comment', [ForumController::class, 'createComment']);
		Route::post('/new_post', [ForumController::class, 'createPost']);
		Route::post('/upvote', [ForumController::class, 'giveUpvote']);
		Route::post('/cancel_upvote', [ForumController::class, 'cancelUpvote']);
		Route::post('/delete_comment', [ForumController::class, 'deleteComment']);
		Route::post('/delete_post', [ForumController::class, 'deletePost']);

		Route::post('/assign_mod', [ForumController::class, 'assignMod']);
		Route::post('/remove_mod', [ForumController::class, 'removeMod']);		
		Route::post('/mod_list', [ForumController::class, 'showModList']);
		Route::post('/ban_user', [ForumController::class, 'banUser']);
		Route::post('/ban_remove', [ForumController::class, 'removeBan']);
		Route::post('/mod_log', [ForumController::class, 'checkModLog']);

		
		Route::get('/my_subforum', [ForumController::class, 'showMySubforum']);
		Route::get('/my_post', [ForumController::class, 'showMyPost']);

		//Route::post('/follow', [ForumController::class, 'followUser']);
		//Route::post('/unfollow', [ForumController::class, 'unfollowUser']);
		Route::post('/follow_subforum', [ForumController::class, 'followSubforum']);
		Route::post('/unfollow_subforum', [ForumController::class, 'unfollowSubforum']);
		//Route::get('/following', [ForumController::class, 'myFollowingList']);
		//Route::get('/followers', [ForumController::class, 'myFollowersList']);
		Route::get('/following_subforum', [ForumController::class, 'myFollowingSubforum']);
		Route::post('/following_subforum', [ForumController::class, 'userFollowingSubforum']);

		Route::post('/update_subforum', [ForumController::class, 'updateSubforumData']);
		
		Route::post('/share', [ForumController::class, 'sharePost']);
		Route::get('/my_role', [ForumController::class, 'myRoles']);
		Route::get('/my_ban', [ForumController::class, 'myBans']);

		Route::get('/my_bookmark', [ForumController::class, 'showPostBookmark']);
		Route::post('/add_bookmark', [ForumController::class, 'addPostBookmark']);
		Route::post('/remove_bookmark', [ForumController::class, 'removePostBookmark']);

		Route::post('/banlist', [ForumController::class, 'showBanList']);
		Route::post('/search_user', [ForumController::class, 'searchForumFollowers']);

	});

	Route::group(['prefix' => 'report'], function(){
		Route::post('/new', [ReportController::class, 'fileReport']);
	});

	Route::group(['prefix' => 'restaurant'],function (){
		Route::post('/add_new', [RestaurantController::class, 'registerNewRestaurant']);
		Route::post('/my_restaurant', [RestaurantController::class, 'myRestaurant']);
		Route::post('/show_restaurant', [RestaurantController::class, 'showRestaurants']);
		Route::post('/update_restaurant', [RestaurantController::class, 'updateRestaurant']);

		Route::post('/restaurant_detail', [RestaurantController::class, 'detailRestaurant']);
		Route::post('/restaurant_review', [RestaurantController::class, 'reviewRestaurant']);
		Route::post('/restaurant_menu', [RestaurantController::class, 'menuRestaurant']);

		Route::post('/add_menu', [RestaurantController::class, 'addNewMenu']);
		Route::post('/add_new_photo', [RestaurantController::class, 'addNewPhotos']);
		
		Route::post('/add_review', [RestaurantController::class, 'addReview']);

		Route::post('/add_bookmark', [RestaurantController::class, 'addRestaurantBookmark']);
		Route::post('/show_bookmark', [RestaurantController::class, 'restaurantBookmark']);

		Route::get('/show_cuisine_type', [RestaurantController::class, 'getAllCuisine']);
		Route::get('/show_restaurant_type', [RestaurantController::class, 'getAllType']);

		Route::post('/delete_menu', [RestaurantController::class, 'deleteMenu']);
		Route::post('/delete_photo', [RestaurantController::class, 'deletePhoto']);
		Route::post('/delete_review', [RestaurantController::class, 'deleteReview']);
		Route::post('/delete_bookmark', [RestaurantController::class, 'removeRestaurantBookmark']);
	});
	
});

Route::group(['prefix' => 'forum'], function(){
	Route::post('/subforum', [ForumController::class, 'showAllSubforum']);
	Route::post('/subforum_data', [ForumController::class, 'showSubforumData']);

	Route::post('/post_list', [ForumController::class, 'showAllPost']);
	Route::post('/post_detail', [ForumController::class, 'showPost']);
	Route::post('/comment', [ForumController::class, 'showComment']);

	Route::post('/user_profile', [ForumController::class, 'showProfile']);
	Route::post('/user_profile_post', [ForumController::class, 'showProfilePost']);
	Route::post('/user_profile_comment', [ForumController::class, 'showProfileComment']);

	Route::post('/search', [ForumController::class, 'search']);

	Route::post('/all_post', [ForumController::class, 'showAllThreads']);
	Route::get('/hot_post', [ForumController::class, 'showHotThreads']);
	Route::post('/home_post', [ForumController::class, 'showHomeThreads']);
});

//nanti hapus

//

//Reset pass email
Route::get('/email/verify', function () {
	return view('auth.verify-email');
})->middleware('auth:api')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
	$request->fulfill();

	return redirect('/home');
})->middleware(['auth:api', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
	$request->user()->sendEmailVerificationNotification();

	return back()->with('message', 'Verification link sent!');
})->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password',[UserController::class, 'resetPassword'])->middleware('guest')->name('password.update');
//

Route::post('/cityList', [CityController::class, 'getCity']);
Route::post('/provinceList', [CityController::class, 'getProvince']);

Route::post('/providers'  , [PulsaController::class, 'getProviders']);
Route::post('/category'  , [PulsaController::class, 'getProductCategory']);
Route::post('/group'  , [PulsaController::class, 'getProductGroup']);
Route::post('/product'  , [PulsaController::class, 'getProduct']);

Route::group(['prefix' => 'category'],function () {
	Route::post('/service', [ServiceCategoryController::class, 'getServiceCategory']);
	//Route::post('/group'  , [ServiceCategoryController::class, 'getProductGroup']);
});

Route::post('test/notification'  , [NotificationController::class, 'notifSend']);
//Route::post('test/notification'  , [NotificationController::class, 'sendMessage']);

Route::get('news'  , [NewsController::class, 'index']);
Route::get('news/category'  , [NewsController::class, 'getNewsCategory']);
Route::get('news/get'  , [NewsController::class, 'getArticle']);
Route::post('news/get-article'  , [NewsController::class, 'getNews']);
Route::post('currency'  , [CurrencyController::class, 'index']);
Route::get('currency/list'  , [CurrencyController::class, 'getList']);
Route::get('covid/jkt'  , [CovidController::class, 'index']);
Route::get('covid'  , [CovidController::class, 'all']);


Route::get('docs/category'  , [DocsCategoryController::class, 'getCategory']);

Route::get('jobs/category'  , [JobsController::class, 'getJobsCategory']);
Route::post('jobs/vacancy'  , [JobsVacancyController::class, 'getJobVacancy']);
Route::get('location'  , [LocationController::class, 'getLocation']);

Route::post('how_to_pay'  , [HowToPayController::class, 'instruction']);

Route::group(['prefix' => 'hotel'], function() {
	Route::group(['prefix' => 'book'], function() {
	    Route::post('new',[HotelBookingController::class, 'store']);
	    Route::post('cancel',[HotelBookingController::class, 'cancel']);
	    Route::post('get_booking',[HotelBookingController::class, 'getBooking']);
	    Route::post('user_booking',[HotelBookingController::class, 'getBookingByUser']);
	    Route::get('/',[HotelBookingController::class, 'index']);
	});
	Route::group(['prefix' => 'image'], function() {

		Route::post('/get_image',[HotelImageController::class, 'getImageByHotel']);
		Route::resource('/', HotelImageController::class);
	});
	Route::group(['prefix' => 'rating'], function() {
		Route::post('/get_user_rating',[HotelRatingController::class, 'getRatingByUser']);
		Route::post('/get_rating',[HotelRatingController::class, 'getRatingByHotel']);
		Route::resource('/', HotelRatingController::class);
	});

	Route::post('by_city',[HotelController::class, 'getHotelByCity']);
    Route::post('by_name',[HotelController::class, 'getHotelByName']);
    Route::get('/{id}',[HotelController::class, 'show']);
    Route::get('/',[HotelController::class, 'index']);
    Route::put('/{id}',[HotelController::class, 'update']);

	Route::group(['prefix' => 'room'], function() {
	    Route::get('/',[HotelRoomController::class, 'index']);
	    Route::get('/{id}',[HotelRoomController::class, 'show']);
	    Route::put('/{id}',[HotelRoomController::class, 'update']);
		Route::resource('/bed_type', HotelRoomBedTypeController::class);

		Route::resource('/image', HotelRoomImageController::class);
	});
});

