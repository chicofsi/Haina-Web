<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Post\PostController;
use App\Http\Controllers\Api\Post\LocationController;
use App\Http\Controllers\Api\Post\Jobs\JobsController;
use App\Http\Controllers\Api\Post\Jobs\JobsVacancyController;
use App\Http\Controllers\Api\Post\Jobs\JobsApplicationController;
use App\Http\Controllers\Api\Post\Jobs\Skill\JobsSkillController;
use App\Http\Controllers\Api\Company\CompanyController;
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
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\Forum\ForumController;

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

Route::post('login', [UserController::class, 'login']);
Route::post('check', [UserController::class, 'check']);
Route::post('login/google', [UserController::class, 'loginWithGoogle']);
Route::post('register', [UserController::class, 'register']);

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
		Route::post('vacancy/delete'  , [JobVacancyController::class, 'deleteVacancy']);

		Route::post('applicant/', [JobVacancyController::class, 'showApplicant']);
		Route::post('applicant/update', [JobVacancyController::class, 'changeApplicantStatus']);

		//sisi user
		Route::post('vacancy/apply'  , [JobApplicantController::class, 'applyJob']);
		Route::post('vacancy/withdraw'  , [JobApplicantController::class, 'withdrawApplication']);
		

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
		Route::post('/photo/register'  , [PhotoController::class, 'registerCompanyPhoto']);
		Route::post('/photo/delete'  , [PhotoController::class, 'deleteCompanyPhoto']);
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
	});

	Route::group(['prefix' => 'work_exp'], function() {
		Route::get('/'  , [UserQualificationController::class, 'showWorkExperience']);
		Route::post('/add'  , [UserQualificationController::class, 'addWorkExperience']);
		Route::post('/delete'  , [UserQualificationController::class, 'deleteWorkExperience']);
	});

	Route::post('notification'  , [NotificationController::class, 'getUserNotification']);

	Route::post('/payment/method'  , [PulsaController::class, 'getPaymentMethod']);
	
	Route::group(['prefix' => 'pulsa'],function ()
	{
		
		Route::post('/inquiry'  , [PulsaController::class, 'getInquiry']);
		Route::post('/transaction'  , [PulsaController::class, 'addTransaction']);
		Route::post('/list'  , [PulsaController::class, 'transactionList']);
	});

	Route::group(['prefix' => 'bills'],function ()
	{
		Route::post('/inquiry'  , [PulsaController::class, 'getInquiryBills']);
		Route::post('/transaction'  , [PulsaController::class, 'addBillsTransaction']);
		//Route::post('/amountbill'  , [PulsaController::class, 'getAmountBills']);
		Route::post('/directbill'  , [PulsaController::class, 'getDirectBills']);
	});

	Route::post('/pending_transaction'  , [PulsaController::class, 'pendingTransactionList']);

	Route::get('/post_category',[PostCategoryController::class, 'getCategory']);

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


	});

	Route::group(['prefix' => 'hotel_darma'], function(){
		//HotelDarmaController

		Route::post('/issue_booking', [HotelDarmaController::class, 'issueBooking']);
		Route::post('/create_booking', [HotelDarmaController::class, 'createBooking']);
		Route::post('/price_policy', [HotelDarmaController::class, 'showPricePolicy']);
		Route::post('/search_room', [HotelDarmaController::class, 'searchRoom']);
		Route::post('/search_hotel', [HotelDarmaController::class, 'searchHotel']);

		Route::post('/hotel_name', [HotelDarmaController::class, 'searchByHotelName']);

		Route::post('/booking_list', [HotelDarmaController::class, 'getBookingList']);
		Route::post('/booking_detail', [HotelDarmaController::class, 'getBookingDetail']);

		Route::post('/cancel', [HotelDarmaController::class, 'cancel']);

		Route::post('/all_cities', [HotelDarmaController::class, 'getIndoCities']);
		Route::post('/cities', [HotelDarmaController::class, 'getCity']);
		Route::post('/countries', [HotelDarmaController::class, 'getCountry']);
		Route::post('/passports', [HotelDarmaController::class, 'getPassport']);

		Route::post('/testimage', [HotelDarmaController::class, 'testImage']);
	});

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
		Route::post('/subforum', [ForumController::class, 'showAllSubforum']);

		Route::post('/post_list', [ForumController::class, 'showAllPost']);
		Route::post('/post_detail', [ForumController::class, 'showPost']);
		Route::post('/comment', [ForumController::class, 'showComment']);

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

		Route::post('/all_post', [ForumController::class, 'showAllThreads']);
		Route::get('/hot_post', [ForumController::class, 'showHotThreads']);
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

		Route::post('/user_profile', [ForumController::class, 'showProfile']);
		Route::post('/search', [ForumController::class, 'search']);
		Route::post('/share', [ForumController::class, 'sharePost']);
		Route::get('/my_role', [ForumController::class, 'myRoles']);
		Route::get('/my_ban', [ForumController::class, 'myBans']);
	});
	
});

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
//Route::post('news/get'  , [NewsController::class, 'getNews']);
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

