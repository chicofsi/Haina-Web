<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Menu\ManageMenu;
use App\Http\Controllers\Admin\News\ManageNews;
use App\Http\Controllers\Admin\News\ManageNewsCategory;
use App\Http\Controllers\Admin\Post\ManagePost;
use App\Http\Controllers\Admin\Post\ManagePostCategory;
use App\Http\Controllers\Admin\Post\ManagePostSubCategory;
use App\Http\Controllers\Admin\Jobs\ManageJobCategory;
use App\Http\Controllers\Admin\Jobs\ManageJobs;
use App\Http\Controllers\Admin\Company\ManageCompany;
use App\Http\Controllers\Admin\User\ManageUser;
use App\Http\Controllers\Api\WebHooks;
use App\Http\Controllers\Admin\UserNotification\ManageNotificationCategory;
use App\Http\Controllers\Admin\UserNotification\ManageNotification;
use App\Http\Controllers\Api\Midtrans\MidtransController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// email verified controller
use App\Http\Controllers\Api\EmailVerifiedController;
// reset password controller
use App\Http\Controllers\Api\ResetPasswordController;
// privacy policy controller
use App\Http\Controllers\PrivacyPolicyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('notif', [MidtransController::class, 'notificationHandler']);
Route::post('hook', [WebHooks::class, 'index']);


Route::get('/', function () {
   	return redirect()->intended('/login');
});

// verified email
Route::get('/email-verified', [EmailVerifiedController::class, 'verified_get']);
Route::get('/resend-verified-email', [EmailVerifiedController::class, 'resend_verified_get']);

// reset password
Route::get('/reset-password', [ResetPasswordController::class, '_get']);
Route::post('/reset-password', [ResetPasswordController::class, '_post']);

// accept terms and conditions
Route::get('/accept-terms-and-conditions', [PrivacyPolicyController::class, 'accept_terms_and_condition']);
Route::get('/policy', [PrivacyPolicyController::class, 'get_policy']);

Route::get('/login', [LoginController::class, 'getLogin'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'postLogin']);

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::group(['prefix' => 'dashboard','middleware' =>'auth:admin'], function() {

    Route::get('/', function() {
        return view('admin.dashboard');
    })->name('dashboard');



    Route::get('/menu'  , [ManageMenu::class, 'index']);
    Route::post('/menu/store'  , [ManageMenu::class, 'store']);
    Route::post('/menu/edit'  , [ManageMenu::class, 'edit']);
    Route::post('/menu/delete'  , [ManageMenu::class, 'destroy']);

    Route::get('/news'  , [ManageNews::class, 'index']);
    Route::post('/news/store'  , [ManageNews::class, 'store']);
    Route::post('/news/edit'  , [ManageNews::class, 'edit']);
    Route::post('/news/delete'  , [ManageNews::class, 'destroy']);


    Route::get('/news/category'  , [ManageNewsCategory::class, 'index']);
    Route::post('/news/category/store'  , [ManageNewsCategory::class, 'store']);
    Route::post('/news/category/edit'  , [ManageNewsCategory::class, 'edit']);
    Route::post('/news/category/delete'  , [ManageNewsCategory::class, 'destroy']);


    Route::get('/notification/category'  , [ManageNotificationCategory::class, 'index']);
    Route::post('/notification/category/store'  , [ManageNotificationCategory::class, 'store']);
    Route::post('/notification/category/edit'  , [ManageNotificationCategory::class, 'edit']);
    Route::post('/notification/category/delete'  , [ManageNotificationCategory::class, 'destroy']);

    Route::get('/notification'  , [ManageNotification::class, 'index']);
    Route::post('/notification/store'  , [ManageNotification::class, 'store']);


    Route::get('/post/category'  , [ManagePostCategory::class, 'index']);
    Route::post('/post/category/store'  , [ManagePostCategory::class, 'store']);
    Route::post('/post/category/edit'  , [ManagePostCategory::class, 'edit']);
    Route::post('/post/category/delete'  , [ManagePostCategory::class, 'destroy']);


    Route::post('/post/category/subcategory'  , [ManagePostSubCategory::class, 'index']);
    Route::post('/post/category/subcategory/store'  , [ManagePostSubCategory::class, 'store']);
    Route::post('/post/category/subcategory/edit'  , [ManagePostSubCategory::class, 'edit']);
    Route::post('/post/category/subcategory/delete'  , [ManagePostSubCategory::class, 'destroy']);


    Route::get('/post'  , [ManagePost::class, 'index']);
    Route::post('/post/accept'  , [ManagePost::class, 'accept']);
    Route::post('/post/block'  , [ManagePost::class, 'block']);
    Route::post('/post/close'  , [ManagePost::class, 'close']);

    Route::get('/jobs'  , [ManageJobs::class, 'index']);
    Route::post('/jobs/accept'  , [ManageJobs::class, 'accept']);
    Route::post('/jobs/block'  , [ManageJobs::class, 'block']);
    Route::post('/jobs/close'  , [ManageJobs::class, 'close']);


    Route::get('/jobs/category'  , [ManageJobCategory::class, 'index']);
    Route::post('/jobs/category/store'  , [ManageJobCategory::class, 'store']);
    Route::post('/jobs/category/edit'  , [ManageJobCategory::class, 'edit']);
    Route::post('/jobs/category/delete'  , [ManageJobCategory::class, 'destroy']);

    Route::get('/company'  , [ManageCompany::class, 'index']);
    Route::post('/company/detail'  , [ManageCompany::class, 'show']);
    Route::post('/company/accept'  , [ManageCompany::class, 'accept']);
    Route::post('/company/suspend'  , [ManageCompany::class, 'suspend']);


    Route::get('/user'  , [ManageUser::class, 'index']);
    Route::post('/user/detail'  , [ManageUser::class, 'show']);
    Route::post('/user/accept'  , [ManageUser::class, 'accept']);
    Route::post('/user/suspend'  , [ManageUser::class, 'suspend']);

});

Route::group(['prefix' => 'service-dashboard','middleware' =>'auth:service_admin'], function() {

    Route::get('/', function() {
        return view('admin\dashboard');
    })->name('service-dashboard');


});
