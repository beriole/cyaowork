<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WorkerController;
use Illuminate\Support\Facades\Route;

// ---- Public ----
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::view('/menu', 'hub')->name('hub');
Route::get('/offres', [\App\Http\Controllers\OfferController::class, 'index'])->name('offers.index');
Route::get('/offres/{offer}', [\App\Http\Controllers\OfferController::class, 'show'])->whereNumber('offer')->name('offers.show');
Route::get('/travailleurs/{worker}', [\App\Http\Controllers\WorkerProfileController::class, 'show'])->name('workers.show');
Route::post('/travailleurs/{worker}/contacter', [\App\Http\Controllers\WorkerProfileController::class, 'contact'])
    ->middleware('auth')->name('workers.contact');

// ---- Authentification (invités) ----
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login']);
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register']);
    Route::get('/inscription/verification', [AuthController::class, 'showOtp'])->name('otp.show');
    Route::post('/inscription/verification', [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('/inscription/renvoyer', [AuthController::class, 'resendOtp'])->name('otp.resend');
});

Route::post('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---- Espaces protégés par rôle ----
Route::middleware(['auth', 'role:worker'])->group(function () {
    Route::get('/worker', [WorkerController::class, 'dashboard'])->name('worker.dashboard');
    Route::get('/worker/profil', [WorkerController::class, 'editProfile'])->name('worker.profile.edit');
    Route::put('/worker/profil', [WorkerController::class, 'updateProfile'])->name('worker.profile.update');
    Route::get('/worker/contrats', [WorkerController::class, 'contracts'])->name('worker.contracts');
    Route::post('/offres/{offer}/postuler', [WorkerController::class, 'apply'])->name('worker.apply');
    Route::post('/worker/photo', [WorkerController::class, 'uploadPhoto'])->name('worker.photo');
    Route::post('/worker/documents', [WorkerController::class, 'uploadDocument'])->name('worker.documents');
});

Route::middleware(['auth', 'role:employer'])->group(function () {
    Route::get('/employer', [EmployerController::class, 'dashboard'])->name('employer.dashboard');
    Route::get('/employer/profil', [EmployerController::class, 'editProfile'])->name('employer.profile.edit');
    Route::put('/employer/profil', [EmployerController::class, 'updateProfile'])->name('employer.profile.update');
    Route::get('/employer/search', [SearchController::class, 'index'])->name('employer.search');
    Route::get('/offres/creer', [EmployerController::class, 'createOffer'])->name('employer.offer.create');
    Route::post('/offres', [EmployerController::class, 'storeOffer'])->name('employer.offer.store');
    Route::get('/offres/{offer}/modifier', [EmployerController::class, 'editOffer'])->name('employer.offer.edit');
    Route::put('/offres/{offer}', [EmployerController::class, 'updateOffer'])->name('employer.offer.update');
    Route::patch('/offres/{offer}/archiver', [EmployerController::class, 'archiveOffer'])->name('employer.offer.archive');
    Route::delete('/offres/{offer}', [EmployerController::class, 'destroyOffer'])->name('employer.offer.destroy');
    Route::get('/offres/{offer}/candidats', [EmployerController::class, 'candidates'])->name('employer.offer.candidates');
    Route::post('/candidatures/{application}/{decision}', [EmployerController::class, 'updateApplication'])
        ->whereIn('decision', ['accepter', 'refuser'])->name('employer.application.decision');
    Route::post('/candidatures/{application}/contrat', [EmployerController::class, 'generateContract'])->name('employer.contract');
    Route::post('/offres/{offer}/boost', [EmployerController::class, 'boostOffer'])->name('employer.boost');
    Route::post('/abonnement/renouveler', [EmployerController::class, 'renewSubscription'])->name('employer.subscription.renew');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/verifications/{profile}/approve', [AdminController::class, 'approveProfile'])->name('admin.verifications.approve');
    Route::post('/admin/verifications/{profile}/reject', [AdminController::class, 'rejectProfile'])->name('admin.verifications.reject');
});

Route::middleware('auth')->group(function () {
    Route::post('/notifications/lues', [\App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.read');
    Route::post('/notifications/{id}/lue', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read.one');
    Route::get('/messagerie', [MessagingController::class, 'index'])->name('messaging.index');
    Route::post('/messagerie/{conversation}/messages', [MessagingController::class, 'store'])->name('messaging.store');
    // Contrats (parties : employeur & travailleur)
    Route::get('/contrats/{contract}', [\App\Http\Controllers\ContractController::class, 'show'])->name('contracts.show');
    Route::post('/contrats/{contract}/signer', [\App\Http\Controllers\ContractController::class, 'sign'])->name('contracts.sign');
    // Téléchargement PDF du contrat depuis le web (réutilise la logique API).
    Route::get('/contrats/{contract}/pdf', [\App\Http\Controllers\Api\ContractController::class, 'pdf'])->name('contracts.pdf');
});
