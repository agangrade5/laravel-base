<?php

use App\Http\Controllers\Backend\Auth\{
    LoginController,
    TwoFactorController,
    RegisterController,
    ForgotPasswordController
};
use App\Http\Controllers\Backend\{
    ActivityLogController,
    DashboardController,
    UserController,
    SettingController,
};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shared Route Closures (Reusable for Admin and User)
|--------------------------------------------------------------------------
| These route closures are defined only once and reused for both
| Admin and User route groups.
|
| When used inside the Admin group, the URL will be:
| /admin/settings
| because the parent group already has the 'admin' prefix.
|
| When used inside the User group, the URL will be:
| /settings
| because the User group does not have a URL prefix.
|--------------------------------------------------------------------------
*/
$settingsRoutes = function (bool $isAdmin = false) {
    Route::prefix('settings')
        ->name('settings.')
        ->group(function () use ($isAdmin) {

            // Common routes - Admin + User dono
            Route::get('/', [
                SettingController::class,
                'index'
            ])->name('index');

            // Profile Routes
            Route::post('/profile', [
                UserController::class,
                'updateProfile',
            ])->name('profile.update');

            // Admin-only routes
            if ($isAdmin) {
                // Change Password Routes
                Route::post('/change-password', [
                    UserController::class,
                    'changePassword',
                ])->name('change-password');

                // OTP Routes
                Route::post('/otp', [
                    SettingController::class,
                    'updateOtpSettings'
                ])->name('otp');

                // SMS Routes
                Route::post('/twilio', [
                    SettingController::class,
                    'updateTwilioSettings'
                ])->name('twilio');

                // Email Routes
                Route::post('/email', [
                    SettingController::class,
                    'updateEmailSettings'
                ])->name('email');

                // AWS Routes
                Route::post('/aws', [
                    SettingController::class,
                    'updateAwsSettings'
                ])->name('aws');

                // 2FA Routes
                Route::prefix('two-fa')->name('twoFa.')->group(function () {
                    Route::get('/setup', [
                        SettingController::class,
                        'twoFaSetup'
                    ])->name('setup');

                    Route::post('/enable', [
                        SettingController::class,
                        'twoFaEnable'
                    ])->name('enable');

                    Route::post('/disable', [
                        SettingController::class,
                        'twoFaDisable'
                    ])->name('disable');
                });

                /* general settings */
                Route::post('/general', [
                    SettingController::class,
                    'updateGeneralSettings'
                ])->name('general');

                Route::post('/maintenance-mode', [
                    SettingController::class,
                    'toggleMaintenanceMode'
                ])->name('maintenanceMode');

                Route::post('/optimize-clear', [
                    SettingController::class,
                    'optimizeClear'
                ])->name('optimizeClear');

                Route::post('/config-cache', [
                    SettingController::class,
                    'configCache'
                ])->name('configCache');

                Route::post('/migrate', [
                    SettingController::class,
                    'runMigrate'
                ])->name('migrate');

                Route::post('/migrate-fresh-seed', [
                    SettingController::class,
                    'runMigrateFreshSeed'
                ])->name('migrateFreshSeed');
            }

        });
};

/*
|--------------------------------------------------------------------------
| Shared Route Closures (Reusable for Admin and User)
|--------------------------------------------------------------------------
| These route closures are defined only once and reused for both
| Admin and User route groups.
|
| When used inside the Admin group, the URL will be:
| /admin/activity-logs
| because the parent group already has the 'admin' prefix.
|
| When used inside the User group, the URL will be:
| /activity-logs
| because the User group does not have a URL prefix.
|--------------------------------------------------------------------------
*/
$activityLogRoutes = function (string $indexPermission) {
    Route::prefix('activity-logs')
        ->name('activity-logs.')
        ->group(function () use ($indexPermission) {

            Route::middleware("permission:{$indexPermission}")
                ->get('/', [
                    ActivityLogController::class,
                    'index',
                ])
                ->name('index');

            Route::middleware('permission:activity-logs.view')
                ->get('/{id}', [
                    ActivityLogController::class,
                    'show',
                ])
                ->name('show');

            Route::middleware([
                'permission:activity-logs.delete',
                'role:admin',
            ])->delete('/{id}', [
                ActivityLogController::class,
                'destroy',
            ])->name('destroy');

        });
};

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('guest')->group(function () {
            /*
            |--------------------------------------------------------------------------
            | Login Routes
            |--------------------------------------------------------------------------
            */
            Route::get('/login', [
                LoginController::class,
                'index',
            ])->name('login');

            Route::post('/login', [
                LoginController::class,
                'login',
            ])->name('login.submit');

            /*
            |--------------------------------------------------------------------------
            | 2FA Routes
            |--------------------------------------------------------------------------
            */
            Route::get('twoFa-show', [
                TwoFactorController::class,
                'twoFaShow'
            ])->name('twoFa.show');
            Route::post('twoFa-verify', [
                TwoFactorController::class,
                'twoFaVerify'
            ])->name('twoFa.verify');

            /*
            |--------------------------------------------------------------------------
            | Forgot Password Routes
            |--------------------------------------------------------------------------
            */
            Route::get('/forgot-password', [
                ForgotPasswordController::class,
                'index',
            ])->name('password.request');

            Route::post('/forgot-password', [
                ForgotPasswordController::class,
                'sendResetLink',
            ])->name('password.email');

            Route::get('/reset-password/{token}', [
                ForgotPasswordController::class,
                'showResetForm',
            ])->name('password.reset');

            Route::post('/reset-password', [
                ForgotPasswordController::class,
                'resetPassword',
            ])->name('password.update');
        });
    });

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    /*
    |--------------------------------------------------------------------------
    | Register Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/register', [
        RegisterController::class,
        'index',
    ])->name('register');

    Route::post('/register', [
        RegisterController::class,
        'register',
    ])->name('register.submit');

    Route::prefix('login')->group(function () {
        Route::get('/', [
            LoginController::class,
            'userLogin',
        ])->name('login');

        Route::post('/send-otp', [
            LoginController::class,
            'sendOtp',
        ])->name('login.send-otp');

        Route::get('/verify-otp', [
            LoginController::class,
            'showVerifyOtp',
        ])->name('login.verify');

        Route::post('/verify-otp', [
            LoginController::class,
            'verifyOtp',
        ])->name('login.verify.submit');

        Route::post('/resend-otp', [
            LoginController::class,
            'resendOtp',
        ])->name('login.resend-otp');
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () use ($activityLogRoutes, $settingsRoutes) {
    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () use ($activityLogRoutes, $settingsRoutes) {
            /*
            |--------------------------------------------------------------------------
            | Dashboard Routes
            |--------------------------------------------------------------------------
            */
            Route::get('/dashboard', [
                DashboardController::class,
                'admin',
            ])->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | Users Routes
            |--------------------------------------------------------------------------
            */
            Route::prefix('users')->name('users.')->group(function () {

                Route::get('/', [
                    UserController::class,
                    'allUsers',
                ])->name('index');

                Route::post('/', [
                    UserController::class,
                    'storeUser',
                ])->name('store');

                Route::get('/{id}/edit', [
                    UserController::class,
                    'editUser',
                ])->name('edit');

                Route::post('/update/{id}', [
                    UserController::class,
                    'updateUser',
                ])->name('update');

                Route::delete('/{id}', [
                    UserController::class,
                    'destroyUser',
                ])->name('destroy');

            });

            /*
            |--------------------------------------------------------------------------
            | Settings Routes -> Common + Admin-only (otp, twilio, email, aws, two-fa)
            |--------------------------------------------------------------------------
            */
            $settingsRoutes(true);

            /*
            |--------------------------------------------------------------------------
            | Activity Logs -> URL: /admin/activity-logs
            |--------------------------------------------------------------------------
            */
            $activityLogRoutes('activity-logs.view-all');
        });

    /*
    |--------------------------------------------------------------------------
    | User Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:user')
        ->group(function () use ($activityLogRoutes, $settingsRoutes) {
            /*
            |--------------------------------------------------------------------------
            | Dashboard Routes
            |--------------------------------------------------------------------------
            */
            Route::get('/dashboard', [
                DashboardController::class,
                'user',
            ])->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | Settings Routes -> Common only (index)
            |--------------------------------------------------------------------------
            */
            $settingsRoutes();

            /*
            |--------------------------------------------------------------------------
            | Activity Logs -> URL: /activity-logs
            |--------------------------------------------------------------------------
            */
            $activityLogRoutes('activity-logs.view');
        });

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [
        LoginController::class,
        'logout',
    ])->name('logout');
});
