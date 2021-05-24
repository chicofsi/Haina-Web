<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Post\PostController;
use App\Http\Controllers\Api\Post\LocationController;
use App\Http\Controllers\Api\Post\Jobs\JobsController;
use App\Http\Controllers\Api\Post\Jobs\JobsVacancyController;
use App\Http\Controllers\Api\Company\CompanyController;
use App\Http\Controllers\Api\Company\AddressController;
use App\Http\Controllers\Api\Company\PhotoController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CovidController;
use App\Http\middleware\SanctumConfigForUser;

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
Route::post('register', [UserController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('detail', [UserController::class, 'detail']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('photo', [UserController::class, 'updatePhoto']);
    Route::post('profile', [UserController::class, 'updateProfile']);
    Route::post('password', [UserController::class, 'changePassword']);
    
	Route::post('jobs/vacancy/post'  , [JobsVacancyController::class, 'postJobsVacancy']);
	Route::post('post/my'  , [PostController::class, 'getMyPost']);

	Route::post('company/register'  , [CompanyController::class, 'registerCompany']);
	Route::post('company'  , [CompanyController::class, 'getCompany']);
	Route::post('company/address/register'  , [AddressController::class, 'registerCompanyAddress']);
	Route::post('company/photo/register'  , [PhotoController::class, 'registerCompanyPhoto']);
	Route::post('company/photo/delete'  , [PhotoController::class, 'deleteCompanyPhoto']);
});





Route::get('news'  , [NewsController::class, 'index']);
Route::get('news/category'  , [NewsController::class, 'getNewsCategory']);
Route::post('news/get'  , [NewsController::class, 'getNews']);
Route::post('currency'  , [CurrencyController::class, 'index']);
Route::get('currency/list'  , [CurrencyController::class, 'getList']);
Route::get('covid/jkt'  , [CovidController::class, 'index']);
Route::get('covid'  , [CovidController::class, 'all']);


Route::get('post/category'  , [PostController::class, 'getPostCategory']);
Route::post('post/subcategory'  , [PostController::class, 'getPostSubCategory']);
Route::post('post'  , [PostController::class, 'getPost']);


Route::get('jobs/category'  , [JobsController::class, 'getJobsCategory']);
Route::post('jobs/vacancy'  , [JobsVacancyController::class, 'getJobVacancy']);
Route::get('location'  , [LocationController::class, 'getLocation']);

