<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Chats\ChatController;
use App\Http\Controllers\Designs\CommentController;
use App\Http\Controllers\Designs\DesignController;
use App\Http\Controllers\Designs\UploadController;
use App\Http\Controllers\Teams\InvitationsController;
use App\Http\Controllers\Teams\TeamsController;
use App\Http\Controllers\User\MeController;
use App\Http\Controllers\User\SettingsController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('me', [MeController::class, 'getMe']);

// Get designs
Route::get('designs', [DesignController::class, 'index']);
Route::get('designs/{id}', [DesignController::class, 'findDesign']);
Route::get('designs/slug/{slug}', [DesignController::class, 'findBySlug']);

// Users
Route::get('user/{username}', [UserController::class, 'findByUsername']);
Route::get('users', [UserController::class, 'index']);
Route::get('users/{id}/designs', [DesignController::class, 'getForUser']);

// Teams
Route::get('teams/slug/{slug}', [TeamsController::class, 'findBySlug']);
Route::get('teams/{id}/designs', [DesignController::class, 'getForTeam']);

// Search Designs
Route::get('search/designs', [DesignController::class, 'search']);
Route::get('search/designers', [UserController::class, 'search']);

// Route group for authenticated users only
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout', [LoginController::class, 'logout']);
    Route::put('settings/profile', [SettingsController::class, 'updateProfile']);
    Route::put('settings/password', [SettingsController::class, 'updatePassword']);

    // Upload Designs
    Route::post('designs', [UploadController::class, 'upload']);
    Route::put('designs/{id}', [DesignController::class, 'update']);
    Route::get('designs/{id}/byUser', [DesignController::class, 'userOwnsDesign']);
    Route::delete('designs/{id}', [UploadController::class, 'destroy']);

    // Likes and Unlikes
    Route::post('designs/{id}/like', [DesignController::class, 'like']);
    Route::get('designs/{id}/liked', [DesignController::class, 'checkIfUserHasLiked']);

    // Comments
    Route::post('designs/{id}/comments', [CommentController::class, 'store']);
    Route::put('comments/{id}', [CommentController::class, 'update']);
    Route::delete('comments/{id}', [CommentController::class, 'destroy']);

    // Teams
    Route::post('teams', [TeamsController::class, 'store']);
    Route::get('teams/{id}', [TeamsController::class, 'findById']);
    Route::get('teams', 'Teams\TeamsController@index');
    Route::get('users/teams', [TeamsController::class, 'fetchUserTeams']);
    Route::put('teams/{id}', [TeamsController::class, 'update']);
    Route::delete('teams/{id}', [TeamsController::class, 'destroy']);
    Route::delete('teams/{team_id}/users/{user_id}', [TeamsController::class, 'removeFromTeam']);

    // Invitations
    Route::post('invitations/{teamId}', [InvitationsController::class, 'invite']);
    Route::post('invitations/{id}/resend', [InvitationsController::class, 'resend']);
    Route::post('invitations/{id}/respond', [InvitationsController::class, 'respond']);
    Route::delete('invitations/{id}', [InvitationsController::class, 'destroy']);

    // Chats
    Route::post('chats', [ChatController::class, 'sendMessage']);
    Route::get('chats', [ChatController::class, 'getUserChats']);
    Route::get('chats/{id}/messages', [ChatController::class, 'getChatMessages']);
    Route::put('chats/{id}/markAsRead', [ChatController::class, 'markAsRead']);
    Route::delete('messages/{id}', [ChatController::class, 'destroyMessage']);

});

// Route group for guests only
Route::group(['middleware' => ['guest:api']], function () {
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('verification/verify/{user}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('verification/resend', [VerificationController::class, 'resend']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('password/reset', [ResetPasswordController::class, 'reset']);
});
