<?php

use App\Http\Controllers\Api\ChampionshipController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\TitleReignController;
use App\Http\Controllers\Api\WrestlerAliasController;
use App\Http\Controllers\Api\WrestlerController;
use App\Http\Controllers\Api\WrestlerPromotionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/user', static fn (Request $request) => $request->user())->middleware('auth:sanctum');

//Route::get('/wrestlers', [WrestlerController::class, 'index']);
Route::get('/wrestlers/{wrestler}', [WrestlerController::class, 'show'])->name('wrestlers.show');

Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/promotions/{identifier}', [PromotionController::class, 'show'])->name('promotions.show');

Route::get('/championships/{identifier}', [ChampionshipController::class, 'show'])->name('championships.show');


// Change request routes (for moderators/admins)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/change-requests', [App\Http\Controllers\Api\ChangeRequestController::class, 'index']);
    Route::get('/change-requests/{changeRequest}', [App\Http\Controllers\Api\ChangeRequestController::class, 'show']);
    Route::post('/change-requests/{changeRequest}/approve', [App\Http\Controllers\Api\ChangeRequestController::class, 'approve']);
    Route::post('/change-requests/{changeRequest}/reject', [App\Http\Controllers\Api\ChangeRequestController::class, 'reject']);
    Route::post('/change-requests/bulk-approve', [App\Http\Controllers\Api\ChangeRequestController::class, 'bulkApprove']);
});


// Change request routes (for moderators/admins)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/change-requests', [App\Http\Controllers\Api\ChangeRequestController::class, 'index']);
    Route::get('/change-requests/{changeRequest}', [App\Http\Controllers\Api\ChangeRequestController::class, 'show']);
    Route::post('/change-requests/{changeRequest}/approve', [App\Http\Controllers\Api\ChangeRequestController::class, 'approve']);
    Route::post('/change-requests/{changeRequest}/reject', [App\Http\Controllers\Api\ChangeRequestController::class, 'reject']);
    Route::post('/change-requests/bulk-approve', [App\Http\Controllers\Api\ChangeRequestController::class, 'bulkApprove']);
});

// Protected routes requiring authentication
Route::middleware('auth:sanctum')->group(function () {
    // Wrestlers
    Route::post('/wrestlers', [WrestlerController::class, 'store'])->name('wrestlers.store');
    Route::put('/wrestlers/{wrestler}', [WrestlerController::class, 'update']);
    Route::patch('/wrestlers/{wrestler}/promotions', [WrestlerPromotionController::class, 'update']);
    Route::post('/wrestlers/{wrestler}/aliases', [WrestlerAliasController::class, 'store']);
    Route::delete('/wrestlers/{wrestler}/aliases/{alias}', [WrestlerAliasController::class, 'destroy']);

    // Promotions
    Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');

    Route::post('/promotions/{promotion}/championships', [ChampionshipController::class, 'store'])->name(
        'championships.store'
    );

    // Update full championship details (PUT or PATCH both acceptable)
    Route::match(['put', 'patch'], '/championships/{identifier}', [ChampionshipController::class, 'update'])->name('championships.update');

    // Toggle championship active/inactive (simplified PATCH if needed)
    Route::patch('/championships/{championship}/toggle-active', [ChampionshipController::class, 'toggleActive'])->name('championships.toggleActive');


    // Title reigns
    Route::post('/wrestlers/{wrestler}/title-reigns', [TitleReignController::class, 'store']);
    Route::patch('/title-reigns/{reign}', [TitleReignController::class, 'update']);
    Route::delete('/title-reigns/{reign}', [TitleReignController::class, 'destroy']);
});
