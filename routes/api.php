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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('verifyMail/{email}', [AuthController::class, 'verify']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('viewUser', [AuthController::class,'list']);
    Route::post('create-post', [PostController::class,'postcreate']);
    Route::post('view_post_public', [PostController::class,'showpost_public']);
    Route::post('view_post_user', [PostController::class,'showpost_user']);
    Route::post('view_singlepost_user', [PostController::class,'showsinglepost_user']);
    Route::post('view_post_private_user', [PostController::class,'showpost_private_user']);
    Route::put('Post_update', [PostController::class,'update_post']);
    Route::delete('Post_delete', [PostController::class,'remove_post']);


    Route::post('create-comment', [CommentController::class,'commentcreate']);
    Route::get('/send-notification', [NotificationController::class, 'sendCommentNotification']);
    Route::post('update-comment', [CommentController::class,'commentupdate']);
    Route::delete('delete-comment', [CommentController::class,'commentdelete']);
    Route::get('viewCommentpost', [CommentController::class,'commentpost']);

    Route::post('addfriend', [FriendController::class,'addfriend']);
    Route::delete('removefriend', [FriendController::class,'removefriend']);
    Route::get('viewfriend', [FriendController::class,'viewfriend']);

    Route::get('messages', [ChatsController::class,'fetchMessages']);
    Route::post('messages', [ChatsController::class,'sendMessage']);
});
