<?php

namespace App\Http\Controllers;

use App\Models\Prenotazione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\RistoranteController;
use App\Http\Controllers\UtenteController;
use App\Http\Controllers\FasceController;

class PrenotazioneController extends Controller
{
    public function index()
    {
        $ristoranti = Prenotazione::all();
        if ($ristoranti->count() > 0) {
            return response()->json($ristoranti, 200);
        } else {
            return response()->json([
                "error" => "Non ci sono prenotazioni"
            ], 404);
        }
    }

    public function checkBook($id_utente, $id_ristorante, $data)
    {

        $result = Prenotazione::where("id_utente", $id_utente)
            ->where("id_ristorante", $id_ristorante)
            ->where("data_prenotazione", $data)->get();
        if ($result->count() > 0) {
            return response()->json([
                "prenotato" => true
            ], 200);
        } else {
            return response()->json([
                "prenotato" => false
            ], 404);
        }

    }
    public function createNewBooking(Request $request)
    {
        $ristoranti = app()->make(RistoranteController::class);
        $utenti = app()->make(UtenteController::class);
        $validator = Validator::make($request->all(), [
            "nome" => "required|string",
            "cognome" => "required|string",
            "numero_telefono" => "required|string",
            "regione_sociale" => "required|string|max:255",
            "indirizzo" => "required|string|max:255",
            "data_prenotazione" => "required|date",
            "numero_posti" => "required|int",
            "fascia_prenotazione" => "required|string",
        ]);
        //input non validi
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()
            ], 400);
        } else { //input validi

            $utenteJSON = $utenti->show($request); //mostra l'utente se esiste
            $ristoranteJSON = $ristoranti->showDetailsBook($request); //mostra il ristorante se esiste

            if (!$ristoranteJSON) {
                return response()->json([
                    "error" => "Non esiste questo ristorante"
                ], 404);
            } else {
                $ristorante = json_decode($ristoranteJSON->content(), true);
                $orari = explode('/', $request->fascia_prenotazione);
                if (isset($ristorante['ristorante'])) {
                    $disponibile = false;
                    if (isset($ristorante['ristorante'][0]["regione_sociale"])) {
                        $disponibile = true;
                    }
                    if ($disponibile) {
                        $utente = json_decode($utenteJSON->content(), true);
                        if (isset($utente['utente'][0]) && ($ristorante['posti_disponibili'] - $request->numero_posti) >= 0) { //se esiste l'utente e il ristorante e ci sono abbastanza posti
                            $id_utente = $utente['utente'][0]['id'];
                            $id_ristorante = $ristorante["ristorante"][0]["id"];
                            $gia_prenotatoJSON = $this->checkBook($id_utente, $id_ristorante, $request->data_prenotazione);
                            $gia_prenotato = json_decode($gia_prenotatoJSON->content(), true);

                            if ($gia_prenotato['prenotato']) {
                                return response()->json([
                                    "message" => "Prenotazione già effettuata"
                                ], 400);
                            } else {
                                $fasce = app()->make(FasceController::class);
                                $fasceJSON = $fasce->subAvailableSeats($id_ristorante, $orari[0], $orari[1], $request->numero_posti);
                                $id_fascia = json_decode($fasceJSON->content(), true);
                                $prenotazione = Prenotazione::create([
                                    "id_utente" => $id_utente,
                                    "id_ristorante" => $id_ristorante,
                                    "data_prenotazione" => $request->data_prenotazione,
                                    "numero_persone" => $request->numero_posti,
                                    "id_fascia" => $id_fascia['id_fascia'],
                                ]);
                                if ($prenotazione) {
                                    return response()->json([
                                        "message" => "Prenotazione effettuata con successo"
                                    ], 200);
                                } else {
                                    return response()->json([
                                        "error" => "Errore nella prenotazione"
                                    ], 500);
                                }
                            }
                        } else {
                            //creazione prima di utente
                            $utenti->create($request);
                            $utenteJSON = $utenti->show($request);
                            $utente = json_decode($utenteJSON->content(), true);

                            if (isset($utente['utente']) && ($ristorante['posti_disponibili'] - $request->numero_posti) >= 0) {
                                $id_utente = $utente['utente'][0]['id'];
                                $id_ristorante = $ristorante['ristorante'][0]["id"];

                                $fasce = app()->make(FasceController::class);
                                $fasceJSON = $fasce->subAvailableSeats($id_ristorante, $orari[0], $orari[1], $request->numero_posti);
                                $id_fascia = json_decode($fasceJSON->content(), true);

                                $prenotazione = Prenotazione::create([
                                    "id_utente" => $id_utente,
                                    "id_ristorante" => $id_ristorante,
                                    "data_prenotazione" => $request->data_prenotazione,
                                    "numero_persone" => $request->numero_posti,
                                    "id_fascia" => $id_fascia['id_fascia'],
                                ]);
                                if ($prenotazione) {
                                    return response()->json([
                                        "message" => "Prenotazione effettuata con successo"
                                    ], 200);
                                } else {
                                    return response()->json([
                                        "error" => "Errore nella prenotazione"
                                    ], 500);
                                }
                            } else {
                                return response()->json([
                                    "error" => "Non ci sono abbastanza posti disponibili"
                                ], 500);
                            }

                        }
                    } else {
                        return response()->json([
                            "error" => "Fascia oraria non disponibile"
                        ], 400);
                    }
                } else {
                    return response()->json([
                        "response" => $ristorante
                    ], 200);
                }

            }
        }
    }
    public function availabilityCheck($id_ristorante)
    {
        $ristorante = Prenotazione::leftJoin('ristorante', 'prenotazione.id_ristorante', '=', 'ristorante.id')
            ->select(
                'prenotazione.numero_persone',
                'prenotazione.fascia_prenotazione',
                Prenotazione::raw('ristorante.numero_posti - prenotazione.numero_persone as posti_rimanenti')
            )
            ->where('prenotazione.id_ristorante', $id_ristorante)
            ->get();

        return response()->json([
            'ristorante' => $ristorante
        ], 200);

    }
    public function getAllReservationOfResturant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "regione_sociale" => "required|string|max:255",
            "indirizzo" => "required|string|max:255",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()
            ], 400);
        } else {
            $ristoranti = app()->make(RistoranteController::class);
            $ristoranteJSON = $ristoranti->getResturantId($request);
            $id_ristorante = json_decode($ristoranteJSON->content(), true);
            $user_and_slot_json = $this->getUserAndSlot($id_ristorante['id_ristorante']);
            $user_and_slot = json_decode($user_and_slot_json->content(), true);

            $utenti = app()->make(UtenteController::class);
            $fasce = app()->make(FasceController::class);

            $ristorante = [];
            $utentiArray = [];

            foreach ($user_and_slot['result'] as $prenotazione) {
                $user_id = $prenotazione['id_utente'];
                $slot_id = $prenotazione['id_fascia'];

                $utente_json = $utenti->getUtenteById($user_id);
                $fascia_json = $fasce->getInfoFasciaById($slot_id);

                $utente = json_decode($utente_json->content(), true);
                $fascia = json_decode($fascia_json->content(), true);

                $utentiArray[] = [
                    'id' => $utente['utente'][0]['id'],
                    'nome' => $utente['utente'][0]['nome'],
                    'cognome' => $utente['utente'][0]['cognome'],
                    'numero_telefono' => $utente['utente'][0]['numero_chiaro'],
                    'inizio' => $fascia['fascia'][0]['inizio'],
                    'fine' => $fascia['fascia'][0]['fine'],
                    'posti_disponibili' => $fascia['fascia'][0]['posti_disponibili']
                ];
            }

            $ristorante_json = $ristoranti->getResturantById($id_ristorante['id_ristorante']);
            $ristorante = json_decode($ristorante_json->content(), true);

            return response()->json([
                'ristorante' => $ristorante['ristorante'],
                'utenti' => $utentiArray
            ], 200);


        }
    }

    protected function getUserAndSlot($id_ristorante)
    {
        $result = Prenotazione::select('id_utente', 'id_fascia')->where('id_ristorante', $id_ristorante)->get();
        if ($result->count() > 0) {
            return response()->json([
                'result' => $result
            ], 200);
        } else {
            return response()->json([
                'result' => 'nessuna prenotazione'
            ], 404);
        }
    }

    public function getAllReservetionOfUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "nome" => "required|string|max:255",
            "cognome" => "required|string|max:255",
            "numero_telefono" => "required|string|max:255",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()
            ], 400);
        } else {
            $utenti = app()->make(UtenteController::class);
            $utenteJSON = $utenti->show($request);
            $utente = json_decode($utenteJSON->content(), true);
            $id_utente = $utente['utente'][0]['id'];
            $result = Prenotazione::select('id_ristorante', 'data_prenotazione', 'numero_persone', 'id_fascia')->where('id_utente', $id_utente)->get();
            if ($result->count() > 0) {
                return response()->json([
                    'prenotazioni_utente' => $result
                ], 200);
            } else {
                return response()->json([
                    'result' => 'nessuna prenotazione'
                ], 404);
            }
        }
    }

    public function cancelReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_utente' => 'required|int',
            'id_ristorante' => 'required|int',
            'id_fascia' => 'required|int',
            'data_prenotazione' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        } else {
            $numero_posti = Prenotazione::select('numero_persone')->where('id_utente', $request->id_utente)
                ->where('id_ristorante', $request->id_ristorante)
                ->where('id_fascia', $request->id_fascia)
                ->where('data_prenotazione', $request->data_prenotazione)->get();
            $fasce = app()->make(FasceController::class);
            $slots_json = $fasce->getSlot($request->id_ristorante, $request->id_fascia);
            $slots = json_decode($slots_json->content(), true);
            $addSeats_json = $fasce->addAvailableSeats($request->id_ristorante, $slots['fascia'][0]['inizio'], $slots['fascia'][0]['fine'], $numero_posti[0]['numero_persone']);
            $addSeats = json_decode($addSeats_json->content(), true);
            if ($addSeats['posti_disponibili']) {
                $result = Prenotazione::where('id_utente', $request->id_utente)
                    ->where('id_ristorante', $request->id_ristorante)
                    ->where('id_fascia', $request->id_fascia)
                    ->where('data_prenotazione', $request->data_prenotazione)
                    ->delete();
                if ($result) {
                    return response()->json([
                        'message' => 'Prenotazione cancellata con successo'
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Prenotazione non trovata'
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'Errore nell\'aggiunta dei posti'
                ], 500);
            }

        }
    }
}
