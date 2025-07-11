<?php


use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/clients', [ApiController::class, 'clients_index']);
//Route::get('/clients/{client}', [ApiController::class, 'clients_show']);
Route::get('/clients/{client}/campaigns', [ApiController::class, 'campaigns_index']);
Route::get('/campaigns/{campaign}', [ApiController::class, 'campaigns_show']);


// Bind the paths to the resource so it automatically handles GET /api/clients/12/campaigns for example
//Route::get('clients', ClientController::class)->only(['index']);
//Route::get('clients.campaigns', CampaignController::class)->only(['index']);
//Route::get('campaigns.units', UnitController::class)->only(['index']);


