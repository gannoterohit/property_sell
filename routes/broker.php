<?php

use App\Http\Controllers\BrokerDashboardController;
use App\Http\Controllers\BrokerRoomController;
use App\Http\Controllers\BrokerRoomDraftController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:broker'])->prefix('agent')->name('agent.')->group(function () {
    // Read-only / Overview pages (Accessible even when verification is pending)
    Route::get('/dashboard', [BrokerDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/pending', [BrokerDashboardController::class, 'pending'])->name('pending');
    Route::get('/properties', [BrokerDashboardController::class, 'properties'])->name('properties');
    Route::get('/enquiries', [BrokerDashboardController::class, 'enquiries'])->name('enquiries');
    Route::get('/rooms/drafts', [BrokerRoomDraftController::class, 'index'])->name('rooms.drafts');
    Route::get('/rooms/drafts/latest', [BrokerRoomDraftController::class, 'latest'])->name('rooms.drafts.latest');
    Route::get('/rooms/drafts/{id}', [BrokerRoomDraftController::class, 'load'])->name('rooms.drafts.load');
    Route::get('/plans', [PlanController::class, 'index'])->name('plans');
    Route::get('/payments', [BrokerDashboardController::class, 'payments'])->name('payments');
    Route::get('/transactions', [BrokerDashboardController::class, 'transactions'])->name('transactions');
    Route::get('/profile', [BrokerDashboardController::class, 'profile'])->name('profile');
    Route::patch('/profile', [BrokerDashboardController::class, 'updateProfile'])->name('profile.update');

    // Mutating actions / Property listing features (Requires approved & active broker account)
    Route::middleware('broker.active')->group(function () {
        Route::get('/rooms/create', [BrokerRoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::post('/rooms/{room}/duplicate', [BrokerRoomController::class, 'duplicate'])->name('rooms.duplicate');
        Route::post('/rooms/{room}/featured', [RoomController::class, 'makeFeatured'])->name('rooms.featured');
        Route::post('/rooms/{room}/booked', [RoomController::class, 'markBooked'])->name('rooms.markBooked');
        Route::post('/rooms/{room}/available', [RoomController::class, 'markAvailable'])->name('rooms.markAvailable');
        Route::post('/rooms/drafts/save', [BrokerRoomDraftController::class, 'save'])->name('rooms.drafts.save');
        Route::delete('/rooms/drafts/{id}', [BrokerRoomDraftController::class, 'destroy'])->name('rooms.drafts.destroy');
        Route::patch('/enquiries/{enquiry}/status', [BrokerDashboardController::class, 'updateEnquiryStatus'])->name('enquiries.update-status');
    });

    // View specific room (Defined after /rooms/create to prevent wildcard collision)
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
});

