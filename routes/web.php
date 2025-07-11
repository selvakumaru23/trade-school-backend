<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AirtableController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// POST routes to do stuff in the backend
Route::middleware('auth')->group(function () {
    Route::post('/admin/airtableupdates/allbutunits', [AirtableController::class, 'allButUnits']);
    Route::post('/admin/airtableupdates/units', [AirtableController::class, 'updateUnits']);
    Route::post('/admin/genai/resetcampaigncopy/{id}', [AdminController::class, 'resetcampaigncopy']);
    Route::post('/admin/genai/resetcampaignimages/{id}', [AdminController::class, 'resetcampaignimages']);
    Route::post('/admin/genai/resetunitcopy/{id}', [AdminController::class, 'resetunitcopy']);
    Route::post('/admin/genai/resetunitimages/{id}', [AdminController::class, 'resetunitimages']);
});

// UI routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::get('/clients/{id}', [ClientController::class, 'show'])->name('client');
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns');
    Route::get('/campaigns/{id}', [CampaignController::class, 'show'])->name('campaign');
    Route::get('/campaigns/{id}/fetch_units', [CampaignController::class, 'fetchUnits'])->name('fetch_units');
    Route::get('/units', [UnitController::class, 'index'])->name('units');
    Route::get('/units/{id}', [UnitController::class, 'show'])->name('unit');
    Route::get('/units/{id}/fetch_unit', [UnitController::class, 'fetchUnit'])->name('fetch_unit');
    Route::get('/units/{id}/prompt', [UnitController::class, 'prompt'])->name('prompt');
    Route::get('/admin', [AdminController::class, 'show'])->name('admin');
    Route::get('/admin/genaihistory', [AdminController::class, 'genaihistory']);
});

// User admin routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
