<?php

use App\Http\Controllers\PrenotazioneController;
use App\Http\Controllers\RistoranteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('restaurant', [RistoranteController::class, 'index']);
Route::post('restaurant', [RistoranteController::class, 'NewRestaurant']);
Route::get('restaurant/{social_region}', [RistoranteController::class, 'showRestaurantsByName']);
Route::post('restaurant/details', [RistoranteController::class, 'showDetails']);
Route::post('restaurant/updatecucina', [RistoranteController::class, 'updateCucina']);
Route::post('restaurant/updateaddress', [RistoranteController::class, 'updateIndirizzo']);
Route::post('restaurant/updatename', [RistoranteController::class, 'updateRegioneSociale']);
Route::post('restaurant/updateseating', [RistoranteController::class, 'updateNumeroPosti']);
Route::put('restaurant', [RistoranteController::class, 'updateRestaurant']);
Route::delete('restaurant', [RistoranteController::class, 'deleteRestaurant']);
Route::post('reservation', [PrenotazioneController::class, 'createNewBooking']);
Route::post('reservation/restaurant/allreservation', [PrenotazioneController::class, 'getAllReservationOfRestaurant']);
Route::post('reservation/user/reservation', [PrenotazioneController::class, 'getAllReservetionOfUser']);
Route::delete('reservation', [PrenotazioneController::class, 'cancelReservation']);