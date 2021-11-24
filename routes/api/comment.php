<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\ChatsController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['jwt.verify']], function() {

    Route::post('create-comment', [CommentController::class,'commentcreate']);
    Route::get('/send-notification', [NotificationController::class, 'sendCommentNotification']);
    Route::post('update-comment', [CommentController::class,'commentupdate']);
    Route::delete('delete-comment', [CommentController::class,'commentdelete']);
    Route::get('viewCommentpost', [CommentController::class,'commentpost']);
});
