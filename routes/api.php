<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    TaskController,
    TeamController,
    UserController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('user', [UserController::class, 'show']);
    Route::patch('user', [UserController::class, 'update']);
    Route::post('user/avatar', [UserController::class, 'updateAvatar']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Tasks
    Route::apiResource('tasks', TaskController::class);
    Route::prefix('tasks')->group(function () {
        Route::post('{task}/attachments', [TaskController::class, 'storeAttachment']);
        Route::delete('{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment']);
        Route::post('{task}/subtasks', [TaskController::class, 'storeSubtask']);
        Route::patch('subtasks/{subtask}/toggle', [TaskController::class, 'toggleSubtask']);
        Route::delete('subtasks/{subtask}', [TaskController::class, 'destroySubtask']);
        Route::post('{task}/comments', [TaskController::class, 'storeComment']);
        Route::delete('{task}/comments/{comment}', [TaskController::class, 'destroyComment']);
    });

    // Teams
    Route::apiResource('teams', TeamController::class);
    Route::prefix('teams')->group(function () {
        Route::post('{team}/members', [TeamController::class, 'addMember']);
        Route::patch('{team}/members/{user}', [TeamController::class, 'updateMember']);
        Route::delete('{team}/members/{user}', [TeamController::class, 'removeMember']);
        Route::post('{team}/invitations', [TeamController::class, 'invite']);
        Route::delete('{team}/invitations/{invitation}', [TeamController::class, 'cancelInvitation']);
    });

    // Task filters and search
    Route::get('tasks/search', [TaskController::class, 'search']);
    Route::get('tasks/filters', [TaskController::class, 'getFilters']);

    // Dashboard statistics
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/recent-activity', [DashboardController::class, 'recentActivity']);
    Route::get('dashboard/upcoming-tasks', [DashboardController::class, 'upcomingTasks']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{notification}', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('stats', [AdminController::class, 'stats']);
        Route::apiResource('users', AdminUserController::class);
        Route::apiResource('roles', AdminRoleController::class);
        Route::get('logs/activity', [AdminController::class, 'activityLogs']);
        Route::get('logs/security', [AdminController::class, 'securityLogs']);
        Route::get('settings', [AdminController::class, 'settings']);
        Route::patch('settings', [AdminController::class, 'updateSettings']);
    });
});
