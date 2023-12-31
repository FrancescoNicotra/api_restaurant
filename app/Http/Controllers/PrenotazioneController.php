<?php

namespace App\Http\Controllers;

use App\Models\Prenotazione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\RistoranteController;
use App\Http\Controllers\UtenteController;
use App\Http\Controllers\FasceController;

/**
 * @OA\Schema(
 *     schema="reservation",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="id_utente", type="integer", example="1"),
 *     @OA\Property(property="id_fascia", type="integer", example="1"),
 *     @OA\Property(property="id_ristorante", type="integer", example="1"),
 *     @OA\Property(property="data_prenotazione", type="date", example="2023-11-15"),
 *     @OA\Property(property="numero_persone", type="integer", example=3),
 *    @OA\Property(property="created_at", type="date", example="2021-05-15 12:00:00"),
 *   @OA\Property(property="updated_at", type="date", example="2021-05-15 12:00:00"),
 * )
 */
/**
 * @OA\Schema(
 *     schema="reservationUser",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nome", type="string", example="John"),
 *     @OA\Property(property="cognome", type="string", example="Doe"),
 *     @OA\Property(property="numero_telefono", type="string", example="123456789"),
 *     @OA\Property(property="inizio", type="string", example="09:00:00"),
 *     @OA\Property(property="fine", type="string", example="11:00:00"),
 *     @OA\Property(property="posti_disponibili", type="integer", example=3),
 * )
 */


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
    /**
     * @OA\Post(
     *     path="/api/reservation",
     *     summary="Create a new booking",
     *     description="Create a new booking for a user at a restaurant within a specified time slot",
     *     tags={"reservation"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Booking details",
     *         @OA\JsonContent(
     *             required={"nome", "cognome", "numero_telefono", "regione_sociale", "indirizzo", "data_prenotazione", "numero_posti", "fascia_prenotazione"},
     *             @OA\Property(property="nome", type="string", example="John"),
     *             @OA\Property(property="cognome", type="string", example="Doe"),
     *             @OA\Property(property="numero_telefono", type="string", example="123456789"),
     *             @OA\Property(property="regione_sociale", type="string", example="ristorante1"),
     *             @OA\Property(property="indirizzo", type="string", example="via roma 1"),
     *             @OA\Property(property="data_prenotazione", type="date", example="2023-11-15"),
     *             @OA\Property(property="numero_posti", type="integer", example=3),
     *             @OA\Property(property="fascia_prenotazione", type="string", example="12:00/13:00"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Prenotazione effettuata con successo"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation error message or Non esiste questo ristorante"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Errore nella prenotazione or Non ci sono abbastanza posti disponibili"),
     *         ),
     *     ),
     *     @OA\Schema(
     *         schema="reservation",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="id_utente", type="integer", example=1),
     *         @OA\Property(property="id_fascia", type="integer", example=1),
     *         @OA\Property(property="id_ristorante", type="integer", example=1),
     *         @OA\Property(property="data_prenotazione", type="date", example="2023-11-15"),
     *         @OA\Property(property="numero_persone", type="integer", example=3),
     *         @OA\Property(property="created_at", type="date", example="2021-05-15 12:00:00"),
     *         @OA\Property(property="updated_at", type="date", example="2021-05-15 12:00:00"),
     *     )
     * )
     */
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
    /**
     * @OA\Post(
     *     path="/api/reservation/restaurant/allreservation",
     *     summary="Get all reservations of a restaurant",
     *     description="Retrieve details of all reservations made at a specific restaurant",
     *     tags={"reservation"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Restaurant details",
     *         @OA\JsonContent(
     *             required={"regione_sociale", "indirizzo"},
     *             @OA\Property(property="regione_sociale", type="string", example="ristorante1"),
     *             @OA\Property(property="indirizzo", type="string", example="via roma 1"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="ristorante", type="object", ref="#/components/schemas/Ristorante"),
     *             @OA\Property(property="utenti", type="array", @OA\Items(ref="#/components/schemas/reservationUser")),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation error message"),
     *         ),
     *     ),
     * )
     */
    public function getAllReservationOfRestaurant(Request $request)
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
            $ristoranteJSON = $ristoranti->getrestaurantId($request);
            $id_ristorante = json_decode($ristoranteJSON->content(), true);
            $user_and_slot_json = $this->getUserAndSlot($id_ristorante['id_ristorante']);
            $user_and_slot = json_decode($user_and_slot_json->content(), true);

            $utenti = app()->make(UtenteController::class);
            $fasce = app()->make(FasceController::class);

            $ristorante = [];
            $utentiArray = [];

            if ($user_and_slot['result'] === "nessuna prenotazione") {
                return response()->json([
                    'message' => $user_and_slot['result']
                ], 404);
            } else {
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

                $ristorante_json = $ristoranti->getRestaurantById($id_ristorante['id_ristorante']);
                $ristorante = json_decode($ristorante_json->content(), true);

                return response()->json([
                    'ristorante' => $ristorante['ristorante'],
                    'utenti' => $utentiArray
                ], 200);

            }
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
    /**
     * @OA\Post(
     *     path="/api/reservation/user/reservation",
     *     tags={"reservation"},
     *     summary="Ottieni tutte le prenotazioni di un utente",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "cognome", "numero_telefono"},
     *             @OA\Property(property="nome", type="string", example="John"),
     *             @OA\Property(property="cognome", type="string", example="Doe"),
     *             @OA\Property(property="numero_telefono", type="string", example="123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Prenotazioni dell'utente ottenute con successo",
     *         @OA\JsonContent(
     *             @OA\Property(property="prenotazioni_utente", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id_ristorante", type="integer", example=1),
     *                     @OA\Property(property="data_prenotazione", type="date", example="2023-11-15"),
     *                     @OA\Property(property="numero_persone", type="integer", example=3),
     *                     @OA\Property(property="id_fascia", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Nessuna prenotazione trovata per l'utente",
     *         @OA\JsonContent(
     *             @OA\Property(property="result", type="string", example="nessuna prenotazione")
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Errore di validazione dei parametri di input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object", example={"nome": {"Il campo nome è obbligatorio"}})
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/reservation",
     *     tags={"reservation"},
     *     summary="Cancella una prenotazione",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_utente", "id_ristorante", "id_fascia", "data_prenotazione"},
     *             @OA\Property(property="id_utente", type="integer", example=1),
     *             @OA\Property(property="id_ristorante", type="integer", example=1),
     *             @OA\Property(property="id_fascia", type="integer", example=1),
     *             @OA\Property(property="data_prenotazione", type="date", example="2023-11-15")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Prenotazione cancellata con successo",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Prenotazione cancellata con successo")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Prenotazione non trovata",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Prenotazione non trovata")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore nell'aggiunta dei posti",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Errore nell'aggiunta dei posti")
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Errore di validazione dei parametri di input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object", example={"id_utente": {"Il campo id_utente è obbligatorio"}})
     *         )
     *     )
     * )
     */
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
