<?php

use App\Http\Controllers\PrenotazioneController;
use App\Http\Controllers\RistoranteController;
use App\Http\Controllers\UtenteController;
use App\Http\Controllers\FasceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('ristorante', [RistoranteController::class, 'index']);
Route::post('ristorante', [RistoranteController::class, 'NewRestaurant']);
Route::get('ristorante/{regione_sociale}', [RistoranteController::class, 'showRestaurantsByName']);
Route::post('ristorante/details', [RistoranteController::class, 'showDetails']);
Route::post('ristorante/updatecucina', [RistoranteController::class, 'updateCucina']);
Route::post('ristorante/updateaddress', [RistoranteController::class, 'updateIndirizzo']);
Route::post('ristorante/updatename', [RistoranteController::class, 'updateRegioneSociale']);
Route::post('ristorante/updateseating', [RistoranteController::class, 'updateNumeroPosti']);
Route::put('ristorante', [RistoranteController::class, 'updateRestaurant']);
Route::delete('ristorante', [RistoranteController::class, 'deleteRestaurant']);
Route::post('prenotazione', [PrenotazioneController::class, 'createNewBooking']);
Route::post('prenotazione/restaurant/reservation', [PrenotazioneController::class, 'getAllReservationOfRestaurant']);
Route::post('prenotazione/user/reservation', [PrenotazioneController::class, 'getAllReservetionOfUser']);
Route::delete('prenotazione', [PrenotazioneController::class, 'cancelReservation']);