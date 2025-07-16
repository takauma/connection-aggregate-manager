<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\AggregateController;
use App\Http\Controllers\AggregateSettingController;
use App\Http\Controllers\BoundController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [LoginController::class, "index"])->name("get.login.index");
Route::get('/login', [LoginController::class, "index"])->name("get.login.login");
Route::post('/login', [LoginController::class, "login"])->name("post.login.login");

Route::group(["middleware" => ["auth"]], function () {
	Route::get("/aggregate", [AggregateController::class, "index"])->name("get.aggregate.index");
	Route::post("/aggregateActive", [AggregateController::class, "active"])->name("post.aggregate.active");
	Route::post("/aggregateExist", [AggregateController::class, "exist"])->name("post.aggregate.exist");
	Route::post("/aggregateData", [AggregateController::class, "data"])->name("post.aggregate.data");

	Route::group(["middleware" => ["auth" => "can:onlyBoundAdminOrSystemAdmin"]], function() {
		Route::get("/bound", [BoundController::class, "index"])->name("get.bound.index");
		Route::post("/boundList", [BoundController::class, "list"])->name("post.bound.list");
		Route::post("/boundRegist", [BoundController::class, "regist"])->name("post.bound.regist");
		Route::post("/boundUpdate", [BoundController::class, "update"])->name("post.bound.update");
		Route::post("/boundDelete", [BoundController::class, "delete"])->name("post.bound.delete");
	
		Route::get("/aggregateSetting", [AggregateSettingController::class, "index"])->name("get.aggregate.setting.index");
		Route::post("/aggregateSettingList", [AggregateSettingController::class, "list"])->name("post.aggregate.setting.list");
		Route::post("/aggregateSettingChange", [AggregateSettingController::class, "change"])->name("post.aggregate.setting.change");
	});

	Route::group(["middleware" => ["auth" => "can:onlySystemAdmin"]], function() {
		Route::get("/user", [UserController::class, "index"])->name("get.user.index");
		Route::post("/userList", [UserController::class, "list"])->name("post.user.list");
		Route::post("/userRegist", [UserController::class, "regist"])->name("post.user.regist");
		Route::post("/userUpdate", [UserController::class, "update"])->name("post.user.update");
		Route::post("/userDelete", [UserController::class, "delete"])->name("post.user.delete");
		Route::post("/role", [UserController::class, "role"])->name("post.user.role");		
	});

	Route::get('/logout', [LogoutController::class, "logout"])->name("get.logout.logout");
});
