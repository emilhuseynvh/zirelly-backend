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

    Route::get('admin/users', [AdminUserController::class, 'index']);
    Route::get('admin/orders/stats', [AdminOrderController::class, 'stats']);
    Route::get('admin/orders/export', [AdminOrderController::class, 'export']);
    Route::get('admin/orders', [AdminOrderController::class, 'index']);
    Route::get('admin/orders/{order}', [AdminOrderController::class, 'show'])->whereNumber('order');
    Route::put('admin/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
});