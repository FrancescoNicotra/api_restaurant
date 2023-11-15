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
Route::post('ristorante', [RistoranteController::class, 'NewResturant']);
Route::get('ristorante/{regione_sociale}', [RistoranteController::class, 'showResturantsByName']);
Route::get('fasce', [FasceController::class, 'index']);
Route::post('fasce', [FasceController::class, 'esempio']);
Route::post('ristorante/details', [RistoranteController::class, 'showDetails']);
Route::post('ristorante/updatecucina', [RistoranteController::class, 'updateCucina']);
Route::post('ristorante/timeslot', [RistoranteController::class, 'updateFasciaPrenotazioni']);
Route::post('ristorante/updateaddress', [RistoranteController::class, 'updateIndirizzo']);
Route::post('ristorante/updatename', [RistoranteController::class, 'updateRegioneSociale']);
Route::post('ristorante/updateseating', [RistoranteController::class, 'updateNumeroPosti']);
Route::post('ristorante/updateBooked', [RistoranteController::class, 'updatePostiPrenotati']);
Route::put('ristorante', [RistoranteController::class, 'updateResturant']);
Route::delete('ristorante', [RistoranteController::class, 'deleteResturant']);
Route::get('prenotazione', [PrenotazioneController::class, 'index']);
Route::post('prenotazione', [PrenotazioneController::class, 'createNewBooking']);
Route::post('prenotazione/resturant/reservation', [PrenotazioneController::class, 'getAllReservationOfResturant']);
Route::post('prenotazione/user/reservation', [PrenotazioneController::class, 'getAllReservetionOfUser']);
Route::delete('prenotazione', [PrenotazioneController::class, 'cancelReservation']);