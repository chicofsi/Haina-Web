<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Menu\ManageMenu;
use App\Http\Controllers\News\ManageNews;
use App\Http\Controllers\News\ManageNewsCategory;
use App\Http\Controllers\Post\ManagePost;
use App\Http\Controllers\Post\ManagePostCategory;
use App\Http\Controllers\Post\ManagePostSubCategory;


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

Route::get('/', function () {
   	return redirect()->intended('/login');
});

Route::get('/login', [LoginController::class, 'getLogin'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'postLogin']);

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

});

Route::group(['prefix' => 'service-dashboard','middleware' =>'auth:service_admin'], function() {

    Route::get('/', function() {
        return view('admin\dashboard');
    })->name('service-dashboard');


});
