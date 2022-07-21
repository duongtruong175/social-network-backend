<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\RelationController;

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

// /api/auth/v1.0/
Route::prefix('auth/v1.0')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('resent-otp', [AuthController::class, 'resentOtp']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('forgot-password-verify-otp', [AuthController::class, 'forgotPasswordVerifyOtp']);
    Route::post('forgot-password-complete', [AuthController::class, 'forgotPasswordComplete']);
    Route::post('register-verify-otp', [AuthController::class, 'registerVerifyOtp']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    // /api/auth/v1.0/
    Route::prefix('auth/v1.0')->group(function () {
        Route::post('update-password', [AuthController::class, 'updatePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // /api/media/v1.0/
    Route::prefix('media/v1.0')->group(function () {
        Route::post('upload', [MediaController::class, 'uploadMedia']);
    });

    // /api/story/v1.0
    Route::prefix('story/v1.0')->group(function () {
        Route::post('create', [StoryController::class, 'createStory']);
        Route::post('view-story/{id}', [StoryController::class, 'viewStory']);
        Route::get('viewer-story/{id}', [StoryController::class, 'getViewerStory']);
        Route::delete('delete/{id}', [StoryController::class, 'deleteStory']);  
    });

    // /api/post/v1.0
    Route::prefix('post/v1.0')->group(function () {
        Route::get('view/{id}', [PostController::class, 'getPostDetail']);
        Route::post('create', [PostController::class, 'createPost']);
        Route::post('update/{id}', [PostController::class, 'updatePost']);
        Route::delete('delete/{id}', [PostController::class, 'deletePost']); 
        Route::get('total-react/{id}', [PostController::class, 'getTotalReactPost']);  
        Route::get('list-react-user/{id}', [PostController::class, 'getReactUserPost']);  
        Route::post('react/{id}', [PostController::class, 'reactPost']);  
    });

    // /api/comment/v1.0
    Route::prefix('comment/v1.0')->group(function () {
        Route::get('comments', [CommentController::class, 'getComments']);
        Route::post('create', [CommentController::class, 'createComment']);
        Route::post('update/{id}', [CommentController::class, 'updateCommentt']);
        Route::delete('delete/{id}', [CommentController::class, 'deleteComment']);  
        Route::get('total-react/{id}', [CommentController::class, 'getTotalReactComment']);  
        Route::get('list-react-user/{id}', [CommentController::class, 'getReactUserComment']);  
        Route::post('react/{id}', [CommentController::class, 'reactComment']);  
    });

    // /api/notification/v1.0
    Route::prefix('notification/v1.0')->group(function () {
        Route::get('notifications', [NotificationController::class, 'getNotifications']);
        Route::post('create', [NotificationController::class, 'createNotification']);
        Route::post('read-all', [NotificationController::class, 'readAllNotification']);
        Route::post('update/{id}', [NotificationController::class, 'updateNotification']);
        Route::delete('delete/{id}', [NotificationController::class, 'deleteNotification']);
    });

    // /api/search/v1.0
    

    // /api/feed/v1.0
    Route::prefix('feed/v1.0')->group(function () {
        Route::get('main/stories', [FeedController::class, 'getStories']);
        Route::get('main/posts', [FeedController::class, 'getFeedPosts']);
    });

    // /api/group/v1.0
    Route::prefix('group/v1.0')->group(function () {
        Route::get('groups', [GroupController::class, 'getGroups']);
        Route::get('suggest-groups', [GroupController::class, 'getSuggestGroups']);
        Route::get('groups/feed', [GroupController::class, 'getFeedGroup']);
        Route::get('view/{id}', [GroupController::class, 'getGroupDetail']);
        Route::get('{id}/posts', [GroupController::class, 'getGroupPosts']);
        Route::get('{id}/members', [GroupController::class, 'getMemberGroups']);
        Route::get('{id}/requests', [GroupController::class, 'getGroupRequests']);
        Route::post('create', [GroupController::class, 'createGroup']);
        Route::post('{id}/update-cover-image', [GroupController::class, 'updateCoverImageGroup']);
        Route::post('update/{id}', [GroupController::class, 'updateGroup']);
        Route::post('update-type/{id}', [GroupController::class, 'updateTypeGroup']);
        Route::delete('delete/{id}', [GroupController::class, 'deleteGroup']);
        Route::post('send-request', [GroupController::class, 'sendRequestJoinGroup']);
        Route::post('get-request', [GroupController::class, 'getMemberGroupRequest']);
        Route::post('accept-request/{id}', [GroupController::class, 'acceptMemberGroupRequest']);
        Route::delete('delete-request/{id}', [GroupController::class, 'deleteMemberGroupRequest']);
    });

    // /api/chat/v1.0
    Route::prefix('chat/v1.0')->group(function () {
        Route::get('conversations', [ChatController::class, 'getConversations']);
        Route::post('create-conversation', [ChatController::class, 'createConversation']);
    });

    // /api/user-profile/v1.0
    Route::prefix('user-profile/v1.0')->group(function () {
        Route::get('profile/{id}', [UserProfileController::class, 'profile']);
        Route::post('profile/{id}/update-avatar', [UserProfileController::class, 'updateAvatar']);
        Route::post('profile/{id}/update-cover-image', [UserProfileController::class, 'updateCoverImage']);
        Route::post('profile/{id}/update-profile', [UserProfileController::class, 'updateProfile']);  
        Route::get('profile/{id}/top-friend', [UserProfileController::class, 'getTopFriends']);
        Route::get('profile/{id}/all-friend', [UserProfileController::class, 'getAllFriends']);  
        Route::get('profile/{id}/photo-album', [UserProfileController::class, 'getPhotoAlbum']);  
        Route::get('profile/{id}/video-album', [UserProfileController::class, 'getVideoAlbum']);  
        Route::get('profile/{id}/posts', [UserProfileController::class, 'getPosts']);  
    });

    // /api/relation/v1.0
    Route::prefix('relation/v1.0')->group(function () {
        Route::get('relation/{id}', [RelationController::class, 'getRelation']);
        Route::get('all-relation', [RelationController::class, 'getAllRelations']);
        Route::post('create', [RelationController::class, 'createRelation']);
        Route::post('{id}/update', [RelationController::class, 'updateRelation']);
        Route::delete('{id}/delete', [RelationController::class, 'deleteRelation']);
    });
});
