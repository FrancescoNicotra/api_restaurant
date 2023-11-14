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

Route::get('utente', [UtenteController::class, 'index']);
Route::post('utente', [UtenteController::class, 'create']);
Route::post('utente/show', [UtenteController::class, 'show']);
Route::get('ristorante', [RistoranteController::class, 'index']);
Route::post('ristorante', [RistoranteController::class, 'NewResturant']);
Route::get('ristorante/{regione_sociale}', [RistoranteController::class, 'showResturantsByName']);
Route::get('fasce', [FasceController::class, 'index']);
Route::post('fasce', [FasceController::class, 'addNewSlot']);
Route::post('fasce/slots', [FasceController::class, 'getAllSlotOfResturant']);
Route::post('ristorante/details', [RistoranteController::class, 'showDetails']);
Route::post('ristorante/updatecucina', [RistoranteController::class, 'updateCucina']);
Route::post('ristorante/timeslot', [RistoranteController::class, 'updateFasciaPrenotazioni']);
Route::post('ristorante/updateaddress', [RistoranteController::class, 'updateIndirizzo']);
Route::post('ristorante/updateseating', [RistoranteController::class, 'updateNumeroPosti']);
Route::post('ristorante/updateBooked', [RistoranteController::class, 'updatePostiPrenotati']);
Route::put('ristorante', [RistoranteController::class, 'updateResturant']);
Route::delete('ristorante', [RistoranteController::class, 'deleteResturant']);
Route::get('prenotazione', [PrenotazioneController::class, 'index']);
Route::post('prenotazione', [PrenotazioneController::class, 'createNewBooking']);
Route::post('prenotazione/resturant/reservetion', [PrenotazioneController::class, 'getAllReservationOfResturant']);
Route::post('prenotazione/user/reservetion', [PrenotazioneController::class, 'getAllReservetionOfUser']);
Route::delete('prenotazione', [PrenotazioneController::class, 'cancelReservation']);