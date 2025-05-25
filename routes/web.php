<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    LoginController,
    RegisterController,
    ForgotPasswordController,
    ResetPasswordController,
    VerificationController,
    TwoFactorController
};
use App\Http\Controllers\{
    DashboardController,
    TaskController,
    TaskUpdateController,
    TeamController,
    ProfileController,
    NotificationController,
    AdminController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Email verification routes
Route::middleware(['auth'])->group(function () {
    Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

// Two-factor authentication routes
Route::middleware(['auth'])->group(function () {
    Route::get('two-factor/setup', [TwoFactorController::class, 'show'])->name('two-factor.setup');
    Route::post('two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::get('two-factor/challenge', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('two-factor/challenge', [TwoFactorController::class, 'verifyChallenge']);
});

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tasks
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::patch('/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        
        // Task updates and comments
        Route::post('/{task}/updates', [TaskUpdateController::class, 'store'])->name('tasks.updates.store');
        Route::delete('/{task}/updates/{update}', [TaskUpdateController::class, 'destroy'])->name('tasks.updates.destroy');
        
        // Task attachments
        Route::post('/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
        Route::get('/{task}/attachments/{attachment}', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');
        Route::delete('/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');

        // Task subtasks
        Route::post('/{task}/subtasks', [TaskController::class, 'storeSubtask'])->name('tasks.subtasks.store');
        Route::patch('/subtasks/{subtask}/toggle', [TaskController::class, 'toggleSubtask'])->name('tasks.subtasks.toggle');
        Route::delete('/subtasks/{subtask}', [TaskController::class, 'destroySubtask'])->name('tasks.subtasks.destroy');

        // Task view preference
        Route::post('/set-view', [TaskController::class, 'setView'])->name('tasks.set-view');
    });

    // Teams
    Route::prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/create', [TeamController::class, 'create'])->name('teams.create');
        Route::post('/', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/{team}', [TeamController::class, 'show'])->name('teams.show');
        Route::get('/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
        Route::patch('/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

        // Team members
        Route::post('/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.add');
        Route::patch('/{team}/members/{user}', [TeamController::class, 'updateMember'])->name('teams.members.update');
        Route::delete('/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.members.remove');

        // Team invitations
        Route::post('/{team}/invitations', [TeamController::class, 'invite'])->name('teams.invitations.store');
        Route::get('/invitations/{invitation}', [TeamController::class, 'acceptInvitation'])->name('teams.invitations.accept');
        Route::delete('/{team}/invitations/{invitation}', [TeamController::class, 'cancelInvitation'])->name('teams.invitations.cancel');
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/{notification}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });
});

// Admin routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // User management
    Route::resource('users', 'AdminUserController');
    
    // Role management
    Route::resource('roles', 'AdminRoleController');
    
    // Permission management
    Route::resource('permissions', 'AdminPermissionController');
    
    // Activity logs
    Route::get('logs/activity', [AdminController::class, 'activityLogs'])->name('admin.logs.activity');
    Route::get('logs/security', [AdminController::class, 'securityLogs'])->name('admin.logs.security');
    
    // Settings
    Route::get('settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::patch('settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
});
