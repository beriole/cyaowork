<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ---- Public ----
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('password/forgot', [PasswordResetController::class, 'forgot']);
    Route::post('password/reset', [PasswordResetController::class, 'reset']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('offers', [OfferController::class, 'index']);
    Route::get('offers/{offer}', [OfferController::class, 'show']);
    Route::get('workers', [SearchController::class, 'workers']);

    // Webhooks Mobile Money (appelés par l'agrégateur).
    Route::post('payments/callback', [PaymentController::class, 'callback']);
    Route::post('payments/fapshi/webhook', [PaymentController::class, 'fapshiWebhook']);

    // ---- Protégé (Sanctum) ----
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('worker/profile', [ProfileController::class, 'updateWorker']);

        Route::post('offers', [OfferController::class, 'store']);
        Route::post('offers/{offer}/apply', [OfferController::class, 'apply']);

        Route::get('applications', [ApplicationController::class, 'index']);
        Route::patch('applications/{application}', [ApplicationController::class, 'updateStatus']);

        Route::get('conversations', [ConversationController::class, 'index']);
        Route::get('conversations/{conversation}', [ConversationController::class, 'show']);
        Route::post('conversations/{conversation}/messages', [ConversationController::class, 'storeMessage']);

        Route::post('reviews', [ReviewController::class, 'store']);
        Route::get('recommendations', [RecommendationController::class, 'index']);

        Route::get('payments', [PaymentController::class, 'index']);
        Route::post('payments/initiate', [PaymentController::class, 'initiate']);

        Route::post('applications/{application}/contract', [ContractController::class, 'store']);
        Route::post('contracts/{contract}/sign', [ContractController::class, 'sign']);
        Route::get('contracts/{contract}/pdf', [ContractController::class, 'pdf']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

        // Uploads (travailleur)
        Route::post('worker/photo', [UploadController::class, 'photo']);
        Route::post('worker/documents', [UploadController::class, 'document']);
        Route::get('worker/documents', [UploadController::class, 'myDocuments']);

        // Back-office admin (vérification d'identité)
        Route::get('admin/verifications', [AdminController::class, 'verifications']);
        Route::post('admin/documents/{document}/approve', [AdminController::class, 'approve']);
        Route::post('admin/documents/{document}/reject', [AdminController::class, 'reject']);
        Route::get('admin/documents/{document}/preview', [AdminController::class, 'preview'])->name('documents.preview');
    });
});
