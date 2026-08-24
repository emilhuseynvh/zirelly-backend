<?php

use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PromocodeController;
use App\Http\Controllers\Api\BasketController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\LegalPageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PopupController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\ProductsPageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecentViewController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\Crm\AuditLogController as CrmAuditLogController;
use App\Http\Controllers\Api\Crm\AuthController as CrmAuthController;
use App\Http\Controllers\Api\Crm\ContactController as CrmContactController;
use App\Http\Controllers\Api\Crm\OrderController as CrmOrderController;
use App\Http\Controllers\Api\Crm\ReportController as CrmReportController;
use App\Http\Controllers\Api\Crm\UserController as CrmUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::post('verify-registration', [AuthController::class, 'verifyRegistration'])->middleware('throttle:10,1');
    Route::post('resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:6,1');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('verify-token', [AuthController::class, 'verifyToken']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
    });
});

Route::apiResource('languages', LanguageController::class)->only(['index', 'show']);

Route::get('blogs/slug/{slug}', [BlogController::class, 'showBySlug']);
Route::apiResource('blogs', BlogController::class)->only(['index', 'show']);

Route::get('home', [HomeController::class, 'show']);
Route::get('about', [AboutController::class, 'show']);
Route::get('contact', [ContactController::class, 'show']);
Route::get('products-page', [ProductsPageController::class, 'show']);
Route::get('popup', [PopupController::class, 'show']);
Route::get('legal/{slug}', [LegalPageController::class, 'show']);
Route::post('contact/messages', [ContactMessageController::class, 'store'])->middleware('throttle:5,1');

Route::get('payments/united/return/{transaction}', [PaymentController::class, 'handleReturn'])
    ->name('payments.united.return')
    ->middleware('throttle:30,1')
    ->whereNumber('transaction');
Route::post('payments/united/webhook', [PaymentController::class, 'webhook'])
    ->name('payments.united.webhook')
    ->middleware('throttle:60,1');

Route::get('products/slug/{slug}', [ProductController::class, 'showBySlug']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::get('products/{product}/reviews', [ProductReviewController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('products/{product}/reviews', [ProductReviewController::class, 'store']);
    Route::put('reviews/{review}', [ProductReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ProductReviewController::class, 'destroy']);

    Route::get('recent-views', [RecentViewController::class, 'index']);

    Route::prefix('basket')->group(function () {
        Route::get('/', [BasketController::class, 'index']);
        Route::delete('/', [BasketController::class, 'clear']);
        Route::post('items', [BasketController::class, 'storeItem']);
        Route::put('items/{item}', [BasketController::class, 'updateItem']);
        Route::delete('items/{item}', [BasketController::class, 'destroyItem']);
    });

    Route::post('checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');
    Route::post('promocodes/preview', [PromocodeController::class, 'preview'])->middleware('throttle:20,1');
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show'])->whereNumber('order');
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('languages', LanguageController::class)->except(['index', 'show']);
    Route::apiResource('blogs', BlogController::class)->except(['index', 'show']);
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);

    Route::put('home', [HomeController::class, 'update']);
    Route::put('about', [AboutController::class, 'update']);
    Route::put('contact', [ContactController::class, 'update']);
    Route::put('products-page', [ProductsPageController::class, 'update']);
    Route::put('popup', [PopupController::class, 'update']);
    Route::put('legal/{slug}', [LegalPageController::class, 'update']);

    Route::get('contact/messages', [ContactMessageController::class, 'index']);
    Route::put('contact/messages/{message}/read', [ContactMessageController::class, 'markRead']);
    Route::delete('contact/messages/{message}', [ContactMessageController::class, 'destroy']);

    Route::post('uploads', [UploadController::class, 'store']);
    Route::delete('uploads/{upload}', [UploadController::class, 'destroy']);

    Route::apiResource('promocodes', PromocodeController::class);

    Route::get('admin/users/export', [AdminUserController::class, 'export']);
    Route::get('admin/users', [AdminUserController::class, 'index']);
    Route::get('admin/orders/stats', [AdminOrderController::class, 'stats']);
    Route::get('admin/orders/export', [AdminOrderController::class, 'export']);
    Route::get('admin/orders', [AdminOrderController::class, 'index']);
    Route::get('admin/orders/{order}', [AdminOrderController::class, 'show'])->whereNumber('order');
    Route::put('admin/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
});
Route::prefix('crm')->group(function () {
    Route::post('auth/login', [CrmAuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'crm'])->group(function () {
        Route::get('auth/me', [CrmAuthController::class, 'me']);
        Route::post('auth/logout', [CrmAuthController::class, 'logout']);

        Route::get('dashboard', [CrmReportController::class, 'summary'])
            ->middleware('crm.section:dashboard');

        Route::middleware('crm.section:reports')->group(function () {
            Route::get('reports', [CrmReportController::class, 'summary']);
            Route::get('reports/export', [CrmReportController::class, 'export']);
        });

        Route::middleware('crm.section:contacts')->group(function () {
            Route::get('contacts/export', [CrmContactController::class, 'export']);
            Route::get('contacts/check-phone', [CrmContactController::class, 'checkPhone']);
            Route::get('contacts', [CrmContactController::class, 'index']);
            Route::post('contacts', [CrmContactController::class, 'store']);
            Route::get('contacts/{contact}', [CrmContactController::class, 'show'])->whereNumber('contact');
            Route::put('contacts/{contact}', [CrmContactController::class, 'update'])->whereNumber('contact');
            Route::post('contacts/{contact}/notes', [CrmContactController::class, 'storeNote'])->whereNumber('contact');
            Route::delete('contacts/{contact}', [CrmContactController::class, 'destroy'])
                ->whereNumber('contact')
                ->middleware('crm.superadmin');
        });

        Route::middleware('crm.section:orders')->group(function () {
            Route::get('orders/export', [CrmOrderController::class, 'export']);
            Route::get('orders', [CrmOrderController::class, 'index']);
            Route::post('orders', [CrmOrderController::class, 'store']);
            Route::get('orders/{order}', [CrmOrderController::class, 'show'])->whereNumber('order');
            Route::put('orders/{order}', [CrmOrderController::class, 'update'])->whereNumber('order');
            Route::put('orders/{order}/status', [CrmOrderController::class, 'updateStatus'])->whereNumber('order');
            Route::delete('orders/{order}', [CrmOrderController::class, 'destroy'])
                ->whereNumber('order')
                ->middleware('crm.superadmin');
        });

        Route::middleware('crm.superadmin')->group(function () {
            Route::get('users', [CrmUserController::class, 'index']);
            Route::post('users', [CrmUserController::class, 'store']);
            Route::put('users/{user}', [CrmUserController::class, 'update'])->whereNumber('user');
            Route::delete('users/{user}', [CrmUserController::class, 'destroy'])->whereNumber('user');
        });

        Route::get('audit-logs', [CrmAuditLogController::class, 'index'])
            ->middleware('crm.section:audit');
    });
});
