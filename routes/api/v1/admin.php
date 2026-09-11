<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminRoomController;
use App\Http\Controllers\Api\Admin\AdminFinanceController;
use App\Http\Controllers\Api\Admin\AdminContentController;
use App\Http\Controllers\Api\Admin\AdminSupportController;
use App\Http\Controllers\Api\Admin\AdminAccessController;
use App\Http\Controllers\Api\Admin\AdminSystemController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminPlatformController;
use App\Http\Controllers\Api\Admin\AdminBrokerController;

Route::post('/admin/auth/login', [AdminAuthController::class, 'login'])->middleware('throttle:strict_login');
Route::middleware(['auth:sanctum', 'role:admin', 'admin.permission', 'admin.activity'])->prefix('admin')->group(function () {
    Route::get('/auth/me', [AdminAuthController::class, 'me']);
    Route::post('/auth/logout', [AdminAuthController::class, 'logout']);

    // ── Dashboard & Analytics ────────────────
    Route::get('/dashboard',        [AdminDashboardController::class, 'index']);
    Route::get('/analytics',        [AdminDashboardController::class, 'analytics']);
    Route::get('/search-analytics', [AdminDashboardController::class, 'searchAnalytics']);
    Route::get('/home-page',        [AdminPlatformController::class, 'homePage']);
    Route::put('/home-page',        [AdminPlatformController::class, 'updateHomePage']);

    Route::get('/notifications', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markAllRead']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'unreadCount']);
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'destroy']);
    Route::post('/broadcast/send', [\App\Http\Controllers\Admin\AdminBroadcastController::class, 'send']);

    // ── User & Owner Management ─────────────
    Route::get('/users',                    [AdminUserController::class, 'users']);
    Route::post('/users',                   [AdminUserController::class, 'createUser']);
    Route::get('/users/{id}',               [AdminUserController::class, 'userDetail']);
    Route::put('/users/{id}',               [AdminUserController::class, 'updateUser']);
    Route::delete('/users/{id}',            [AdminUserController::class, 'deleteUser']);
    Route::post('/users/{id}/toggle-block', [AdminUserController::class, 'toggleBlockUser']);
    
    Route::get('/owners',                   [AdminUserController::class, 'owners']);
    Route::post('/owners',                  [AdminUserController::class, 'createOwner']);
    Route::get('/owners/{id}',              [AdminUserController::class, 'ownerDetail']);
    Route::put('/owners/{id}',              [AdminUserController::class, 'updateOwner']);
    Route::delete('/owners/{id}',           [AdminUserController::class, 'deleteOwner']);
    Route::post('/owners/{id}/toggle-block', [AdminUserController::class, 'toggleBlockOwner']);
    Route::put('/members/{id}/notes',        [AdminUserController::class, 'updateMemberNotes']);
    Route::post('/members/{id}/restore',     [AdminUserController::class, 'restoreMember']);

    // ── Broker Management ────────────────────
    Route::get('/brokers',                          [AdminBrokerController::class, 'index']);
    Route::get('/brokers/{id}',                     [AdminBrokerController::class, 'show']);
    Route::post('/brokers/{id}/approve',            [AdminBrokerController::class, 'approve']);
    Route::post('/brokers/{id}/reject',             [AdminBrokerController::class, 'reject']);
    Route::post('/brokers/{id}/suspend',            [AdminBrokerController::class, 'suspend']);
    Route::post('/brokers/{id}/activate',           [AdminBrokerController::class, 'activate']);
    Route::post('/brokers/{id}/toggle-featured',    [AdminBrokerController::class, 'toggleFeatured']);

    // ── Room Management ─────────────────────
    Route::get('/rooms',                    [AdminRoomController::class, 'index']);
    Route::post('/rooms/{id}/approve',      [AdminRoomController::class, 'approve']);
    Route::post('/rooms/{id}/reject',       [AdminRoomController::class, 'reject']);
    Route::delete('/rooms/{id}',            [AdminRoomController::class, 'destroy']);
    
    // Rejection Reasons
    Route::get('/rejection-reasons',           [AdminRoomController::class, 'getReasons']);
    Route::post('/rejection-reasons',          [AdminRoomController::class, 'storeReason']);
    Route::put('/rejection-reasons/{id}',      [AdminRoomController::class, 'updateReason']);
    Route::post('/rejection-reasons/{id}/toggle', [AdminRoomController::class, 'toggleReason']);
    Route::delete('/rejection-reasons/{id}',   [AdminRoomController::class, 'deleteReason']);

    // ── Finance & Plans ──────────────────────
    Route::get('/payments',                 [AdminFinanceController::class, 'payments']);
    
    Route::get('/plans',                    [AdminFinanceController::class, 'plans']);
    Route::post('/plans',                   [AdminFinanceController::class, 'storePlan']);
    Route::put('/plans/{id}',               [AdminFinanceController::class, 'updatePlan']);
    Route::post('/plans/{id}/toggle',       [AdminFinanceController::class, 'togglePlan']);
    Route::delete('/plans/{id}',            [AdminFinanceController::class, 'destroyPlan']);

    // ── Content & Settings ───────────────────
    Route::get('/settings',                 [AdminContentController::class, 'getSettings']);
    Route::post('/settings',                [AdminContentController::class, 'updateSettings']);
    Route::get('/cities',                   [AdminPlatformController::class, 'cities']);
    Route::post('/cities',                  [AdminPlatformController::class, 'storeCity']);
    Route::put('/cities/{city}',            [AdminPlatformController::class, 'updateCity']);
    Route::post('/cities/{city}/toggle',    [AdminPlatformController::class, 'toggleCity']);
    Route::delete('/cities/{city}',         [AdminPlatformController::class, 'destroyCity']);

    Route::get('/property-types',                    [AdminPlatformController::class, 'propertyTypes']);
    Route::post('/property-types',                   [AdminPlatformController::class, 'storePropertyType']);
    Route::put('/property-types/{propertyType}',     [AdminPlatformController::class, 'updatePropertyType']);
    Route::post('/property-types/{propertyType}/toggle', [AdminPlatformController::class, 'togglePropertyType']);
    Route::delete('/property-types/{propertyType}',  [AdminPlatformController::class, 'destroyPropertyType']);

    Route::get('/property-categories',                    [AdminPlatformController::class, 'propertyCategories']);
    Route::post('/property-categories',                   [AdminPlatformController::class, 'storePropertyCategory']);
    Route::put('/property-categories/{propertyCategory}', [AdminPlatformController::class, 'updatePropertyCategory']);
    Route::post('/property-categories/{propertyCategory}/toggle', [AdminPlatformController::class, 'togglePropertyCategory']);
    Route::delete('/property-categories/{propertyCategory}', [AdminPlatformController::class, 'destroyPropertyCategory']);
    
    Route::get('/blogs',                    [AdminContentController::class, 'blogs']);
    Route::post('/blogs',                   [AdminContentController::class, 'storeBlog']);
    Route::put('/blogs/{id}',               [AdminContentController::class, 'updateBlog']);
    Route::post('/blogs/{id}/toggle',       [AdminContentController::class, 'toggleBlog']);
    Route::delete('/blogs/{id}',            [AdminContentController::class, 'destroyBlog']);

    Route::get('/offers',                   [AdminContentController::class, 'offers']);
    Route::post('/offers',                  [AdminContentController::class, 'storeOffer']);
    Route::put('/offers/{id}',              [AdminContentController::class, 'updateOffer']);
    Route::post('/offers/{id}/toggle',      [AdminContentController::class, 'toggleOffer']);
    Route::delete('/offers/{id}',           [AdminContentController::class, 'destroyOffer']);

    Route::get('/city-alerts',              [AdminContentController::class, 'allCityAlerts']);
    Route::delete('/city-alerts/{id}',      [AdminContentController::class, 'destroyCityAlert']);
    
    Route::get('/subscribers',              [AdminContentController::class, 'subscribers']);
    Route::delete('/subscribers/{id}',      [AdminContentController::class, 'destroySubscriber']);
    
    Route::put('/pages/{slug}',             [AdminContentController::class, 'updatePage']);
    Route::get('/pages/{slug}',             [AdminContentController::class, 'page']);
    Route::get('/room-options',             [AdminContentController::class, 'roomOptions']);
    Route::post('/room-options',            [AdminContentController::class, 'storeRoomOption']);
    Route::put('/room-options/{option}',     [AdminContentController::class, 'updateRoomOption']);
    Route::post('/room-options/{option}/toggle', [AdminContentController::class, 'toggleRoomOption']);
    Route::delete('/room-options/{option}',  [AdminContentController::class, 'destroyRoomOption']);

    // Support operations
    Route::get('/complaint-options',                 [AdminSupportController::class, 'complaintOptions']);
    Route::get('/complaints',                        [AdminSupportController::class, 'complaints']);
    Route::get('/complaints/{complaint}',            [AdminSupportController::class, 'complaintShow']);
    Route::put('/complaints/{complaint}',            [AdminSupportController::class, 'complaintUpdate']);
    Route::post('/complaints/{complaint}/reply',     [AdminSupportController::class, 'complaintReply']);
    Route::post('/complaints/{complaint}/reopen',    [AdminSupportController::class, 'complaintReopen']);
    Route::get('/contact-messages',                  [AdminSupportController::class, 'contactMessages']);
    Route::post('/contact-messages/{message}/read',  [AdminSupportController::class, 'markContactRead']);
    Route::delete('/contact-messages/{message}',     [AdminSupportController::class, 'deleteContact']);

    // Staff access control and audit
    Route::get('/permission-catalog',           [AdminAccessController::class, 'catalog']);
    Route::get('/staff',                        [AdminAccessController::class, 'staff']);
    Route::post('/staff',                       [AdminAccessController::class, 'staffStore']);
    Route::put('/staff/{staff}',                [AdminAccessController::class, 'staffUpdate']);
    Route::post('/staff/{staff}/toggle',        [AdminAccessController::class, 'staffToggle']);
    Route::delete('/staff/{staff}',             [AdminAccessController::class, 'staffDestroy']);
    Route::post('/staff/{id}/restore',          [AdminAccessController::class, 'staffRestore']);
    Route::get('/roles',                        [AdminAccessController::class, 'roles']);
    Route::post('/roles',                       [AdminAccessController::class, 'roleStore']);
    Route::put('/roles/{role}',                 [AdminAccessController::class, 'roleUpdate']);
    Route::get('/activity-logs',                [AdminAccessController::class, 'activityLogs']);
    Route::put('/owners/{owner}/verification',  [AdminAccessController::class, 'ownerVerification']);

    // Platform operations and reporting
    Route::get('/maintenance',                  [AdminSystemController::class, 'maintenance']);
    Route::put('/maintenance',                  [AdminSystemController::class, 'updateMaintenance']);
    Route::get('/reports/overview',             [AdminSystemController::class, 'reports']);
    Route::post('/rooms/bulk-action',           [AdminSystemController::class, 'bulkRooms']);
    Route::delete('/search-logs/{searchLog}',   [AdminSystemController::class, 'deleteSearchLog']);
    Route::delete('/search-logs',               [AdminSystemController::class, 'deleteSearchLogs']);
});
