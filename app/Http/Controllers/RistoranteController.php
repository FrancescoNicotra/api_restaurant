<?php

namespace App\Http\Controllers;

use App\Models\Ristorante;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\PrenotazioneController;
use App\Http\Controllers\FasceController;


/**
 * @OA\Schema(
 *     schema="Ristorante",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="regione_sociale", type="string", example="ristorante1"),
 *     @OA\Property(property="indirizzo", type="string", example="via roma 1"),
 *     @OA\Property(property="tipo_cucina", type="string", example="italiana"),
 *     @OA\Property(property="numero_posti", type="integer", example=200),
 * )
 */

class RistoranteController extends Controller
{
    //mostra tutti i ristoranti
    /**
     * @OA\Get(
     *     path="/api/restaurant",
     *     summary="Get all restaurants",
     *     description="Retrieve a list of all restaurants",
     *     tags={"restaurant"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="ristoranti", type="array", @OA\Items(ref="#/components/schemas/Ristorante")),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No time slots found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="nessun ristorante trovato"),
     *         ),
     *     ),
     * )
     */
    public function index()
    {
        $ristoranti = Ristorante::select('regione_sociale', 'indirizzo', 'tipo_cucina')->get();

        if ($ristoranti->count() > 0) {
            return response()->json([
                "status" => 200,
                "ristoranti" => $ristoranti
            ], 200);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "nessun ristorante trovato"
            ], 404);
        }
    }


    /**
     * @OA\Post(
     *    path="/api/restaurant",
     *   summary="Create a new restaurant",
     *  description="Create a new restaurant",
     * operationId="createrestaurant",
     * tags={"restaurant"},
     * @OA\RequestBody(
     *   required=true,
     * description="Create a new restaurant",
     * @OA\JsonContent(
     *  required={"regione_sociale","indirizzo","tipo_cucina","numero_posti","inizio","fine"},
     * @OA\Property(property="regione_sociale", type="string", example="ristorante1"),
     * @OA\Property(property="indirizzo", type="string", example="via roma 1"),
     * @OA\Property(property="tipo_cucina", type="string", example="italiana"),
     * @OA\Property(property="numero_posti", type="integer", example=200),
     * @OA\Property(property="inizio", type="string", format="H:i", example="12:00"),
     * @OA\Property(property="fine", type="string", format="H:i", example="13:00"),
     * ),
     * ),
     * @OA\Response(
     *   response=201,
     *  description="Success",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=201),
     * @OA\Property(property="message", type="string", example="ristorante creato con successo"),
     * ),
     * ),
     * @OA\Response(
     *  response=400,
     * description="Bad Request",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=400),
     * @OA\Property(property="message", type="string", example="dati non validi"),
     * ),
     * ),
     * )
     */
    //crea un nuovo ristorante
    public function NewRestaurant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'regione_sociale' => 'required|string|max:255',
            'indirizzo' => 'required|string|max:255',
            'tipo_cucina' => 'required|string|max:255',
            'numero_posti' => 'required|integer',
            'inizio' => 'required|date_format:H:i',
            'fine' => 'required|date_format:H:i|after:inizio',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => "dati non validi"
            ], 400);
        } else {
            $fasce = app()->make(FasceController::class);

            $ristorante = Ristorante::create([
                'regione_sociale' => $request->regione_sociale,
                'indirizzo' => $request->indirizzo,
                'tipo_cucina' => $request->tipo_cucina,
                'numero_posti' => $request->numero_posti,
            ]);
            if (isset($ristorante) && $ristorante->count() > 0) {
                $id_ristorante = Ristorante::select('id')->where('regione_sociale', $request->regione_sociale)->where('indirizzo', $request->indirizzo)->get();
                if ($id_ristorante->count() > 0) {
                    $fasce->addNewSlot($id_ristorante[0]['id'], $request->inizio, $request->fine, $request->numero_posti);
                    if (isset($fasce)) {
                        if ($ristorante->count() > 0) {
                            return response()->json([
                                "status" => 201,
                                "message" => "ristorante creato con successo"
                            ], 201);
                        } else {
                            return response()->json([
                                "status" => 500,
                                "message" => "errore nella creazione del ristorante"
                            ], 500);
                        }
                    }
                } else {
                    return response()->json([
                        "status" => 500,
                        "message" => "errore nella creazione del ristorante"
                    ], 500);
                }
            }
        }
    }

    /**
     * @OA\Get(
     *    path="/api/restaurant/{social_region}",
     *    summary="Get all restaurants by name",
     *    description="Retrieve a list of all restaurants by name",
     *   tags={"restaurant"},
     * @OA\Parameter(
     *   description="Name of restaurant",
     *  in="path",
     * name="regione_sociale",
     * required=true,
     *  @OA\Schema(
     * type="string"
     *  )
     * ),
     * @OA\Response(
     * response=200,
     * description="Success",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=200),
     * @OA\Property(property="ristorante", type="array", @OA\Items(ref="#/components/schemas/Ristorante")),
     * ),
     * ),
     * @OA\Response(
     * response=404,
     * description="Not Found",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="message", type="string", example="nessun ristorante trovato"),
     * ),
     * ),
     * )
     * 
     */
    //mostra tutti i ristoranti con un determinato nome
    public function showRestaurantsByName($regione_sociale)
    {

        $ristorante = Ristorante::select('regione_sociale', 'indirizzo', 'tipo_cucina')->where('regione_sociale', $regione_sociale)->get();
        if ($ristorante->count() > 0) {
            return response()->json([
                "status" => 200,
                "message" => $ristorante
            ], 200);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "nessun ristorante trovato"
            ], 404);

        }
    }
    /**
     * @OA\Post(
     *     path="/api/restaurant/details",
     *     summary="Show details of a restaurant by name and address",
     *     description="Retrieve details of a restaurant by providing its name and address",
     *     tags={"restaurant"},
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
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="ristorante", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="regione_sociale", type="string"),
     *                 @OA\Property(property="indirizzo", type="string"),
     *                 @OA\Property(property="tipo_cucina", type="string"),
     *                 @OA\Property(property="numero_posti", type="integer"),
     *             )),
     *             @OA\Property(property="fasce_disponibili", type="array", @OA\Items(
     *                 @OA\Property(property="inizio", type="string", format="H:i", example="12:00"),
     *                 @OA\Property(property="fine", type="string", format="H:i", example="13:00"),
     *                 @OA\Property(property="posti_disponibili", type="integer"),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="dati non validi"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="nessun ristorante trovato"),
     *         ),
     *     ),
     * )
     */

    //mostra un ristorante con un determinato nome e un determinato indirizzo
    public function showDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "regione_sociale" => "required | string | max:255",
            "indirizzo" => "required | string | max:255",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => $validator->errors()->first(),
            ], 400);
        } else {
            $fasce = app()->make(FasceController::class);
            $id_ristorante = Ristorante::select('id')
                ->where('regione_sociale', $request->regione_sociale)
                ->where('indirizzo', $request->indirizzo)->get();
            if ($id_ristorante->count() > 0) {
                $fasce_ristorante_json = $fasce->getAllSlotOfRestaurant($id_ristorante[0]['id']);
                $fasce_ristorante = json_decode($fasce_ristorante_json->content(), true);
                $fasce_orarie = $fasce_ristorante['fasce_orarie'];
                $ristorante = Ristorante::select('id', 'regione_sociale', 'indirizzo', 'tipo_cucina', 'numero_posti')
                    ->where('regione_sociale', Str::lower($request->regione_sociale))
                    ->where('indirizzo', Str::lower($request->indirizzo))
                    ->get();
                if ($ristorante->count() == 0) {
                    return response()->json([
                        "status" => 404,
                        "message" => "nessun ristorante trovato"
                    ], 404);
                } else {
                    $availableFasce = [];

                    foreach ($fasce_orarie as $fascia) {
                        $posti_disponibiliJSON = $fasce->getAvailableSeats($ristorante[0]["id"], $fascia['inizio'], $fascia['fine']);
                        $posti_disponibili = json_decode($posti_disponibiliJSON->content(), true);

                        // Aggiungi i risultati all'array $availableFasce
                        $availableFasce[] = [
                            'inizio' => $fascia['inizio'],
                            'fine' => $fascia['fine'],
                            'posti_disponibili' => $posti_disponibili["posti_disponibili"][0]['posti_disponibili'],
                        ];
                    }

                    // Restituisci l'array completo nella risposta JSON
                    return response()->json([
                        "status" => 200,
                        "ristorante" => $ristorante,
                        "fasce_disponibili" => $availableFasce,
                    ], 200);

                }
            }
        }
    }

    /**
     * @OA\Post(
     *    path="/api/restaurant/updatecucina",
     *  summary="Update the type of cuisine of a restaurant",
     * description="Update the type of cuisine of a restaurant",
     * operationId="updateCucina",
     * tags={"restaurant"},
     * @OA\RequestBody(
     *  required=true,
     * description="Update the type of cuisine of a restaurant",
     * @OA\JsonContent(
     * required={"regione_sociale","indirizzo","tipo_cucina"},
     * @OA\Property(property="regione_sociale", type="string", example="ristorante1"),
     * @OA\Property(property="indirizzo", type="string", example="via roma 1"),
     * @OA\Property(property="tipo_cucina", type="string", example="italiana"),
     * ),
     * ),
     * @OA\Response(
     * response=201,
     * description="Success",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=201),
     * @OA\Property(property="message", type="string", example="Il tipo di cucina è stato aggiornato"),
     * ),
     * ),
     * @OA\Response(
     * response=400,
     * description="Bad Request",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=400),
     * @OA\Property(property="message", type="string", example="tipo cucina non valido"),
     * ),
     * ),
     * )
     */
    //funzione per aggiornare SOLO il tipo di cucina
    public function updateCucina(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "regione_sociale" => "required | string | max:255",
            "indirizzo" => "required | string | max:255",
            "tipo_cucina" => "required | string | max:255",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => $validator->errors()->first(),
            ], 400);
        } else {
            if ($request->tipo_cucina == null) {
                return response()->json([
                    "status" => 400,
                    "message" => "tipo cucina non valido",
                ], 400);
            } else {
                $cucina = Ristorante::where("tipo_cucina", $request->tipo_cucina)->get();
                if ($cucina->count() > 0) {
                    return response()->json([
                        "status" => 400,
                        "message" => "Il ristorante ha già questo tipo di cucina",
                    ], 400);
                } else {
                    $ristorante = Ristorante::where("regione_sociale", $request->regione_sociale)
                        ->where("indirizzo", $request->indirizzo)
                        ->update(["tipo_cucina" => $request->tipo_cucina]);
                    if ($ristorante == 0) {
                        return response()->json([
                            "status" => 400,
                            "message" => "Non è stato possibile aggiornare il tipo di cucina",
                        ], 400);
                    } else {
                        return response()->json([
                            "status" => 201,
                            "messagge" => "Il tipo di cucina è stato aggiornato"
                        ], 201);
                    }
                }
            }

        }
    }
    /**
     * @OA\Post(
     *    path="/api/restaurant/updateaddress",
     *  summary="Update the address of a restaurant",
     * description="Update the address of a restaurant",
     * operationId="updateAddress",
     * tags={"restaurant"},
     * @OA\RequestBody(
     *  required=true,
     * description="Update the address of a restaurant",
     * @OA\JsonContent(
     * required={"regione_sociale","indirizzo","tipo_cucina"},
     * @OA\Property(property="regione_sociale", type="string", example="ristorante1"),
     * @OA\Property(property="indirizzo", type="string", example="via roma 1"),
     * @OA\Property(property="indirizzo_nuovo", type="string", example="via roma 2"),
     * ),
     * ),
     * @OA\Response(
     * response=201,
     * description="Success",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=201),
     * @OA\Property(property="message", type="string", example="L'indirizzo è stato aggiornato"),
     * ),
     * ),
     * @OA\Response(
     * response=400,
     * description="Bad Request",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=400),
     * @OA\Property(property="message", type="string", example="Indirizzo non valido"),
     * ),
     * ),
     * )
     */
    //funzione per aggiornare solo l'indirizzo
    public function updateIndirizzo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "regione_sociale" => "required | string | max:255",
            "indirizzo" => "required | string | max:255",
            "indirizzo_nuovo" => "required | string | max:255",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => $validator->errors()->first(),
            ], 400);
        } else {
            if ($request->indirizzo_nuovo == null) {
                return response()->json([
                    "status" => 400,
                    "message" => "Indirizzo non valido",
                ], 400);
            } else {
                $indirizzo_nuovo = Ristorante::where("indirizzo", $request->indirizzo_nuovo)->get();
                if ($indirizzo_nuovo->count() > 0) {
                    return response()->json([
                        "status" => 400,
                        "message" => "Il ristorante si trova già in questa posizione",
                    ], 400);
                } else {
                    $ristorante = Ristorante::where("regione_sociale", $request->regione_sociale)
                        ->where("indirizzo", $request->indirizzo)
                        ->update(["indirizzo" => $request->indirizzo_nuovo]);
                    if ($ristorante == 0) {
                        return response()->json([
                            "status" => 400,
                            "message" => "Non è stato possibile aggiornare l'indirizzo",
                        ], 400);
                    } else {
                        return response()->json([
                            "status" => 200,
                            "message" => "L'inidirizzo è stato aggiornato",
                        ]);
                    }
                }
            }
        }
    }
    /**
     * @OA\Post(
     *     path="/api/restaurant/updatename",
     *     summary="Update the regione_sociale of a restaurant",
     *     description="Update the regione_sociale of a restaurant identified by name and address",
     *     tags={"restaurant"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Restaurant details for update",
     *         @OA\JsonContent(
     *             required={"regione_sociale", "indirizzo", "regione_sociale_nuova"},
     *             @OA\Property(property="regione_sociale", type="string", example="ristorante1"),
     *             @OA\Property(property="indirizzo", type="string", example="via roma 1"),
     *             @OA\Property(property="regione_sociale_nuova", type="string", example="new_ristorante1"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="La regione sociale è stata aggiornata"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Validation error message or Non è stato possibile aggiornare la regione sociale"),
     *         ),
     *     ),
     * )
     */
    public function updateRegioneSociale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "regione_sociale" => "required | string | max:255",
            "indirizzo" => "required | string | max:255",
            "regione_sociale_nuova" => "required | string | max:255",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => $validator->errors()->first(),
            ], 400);
        } else {
            if ($request->regione_sociale_nuova == null) {
                return response()->json([
                    "status" => 400,
                    "message" => "Regione sociale non valida",
                ], 400);
            } else {
                $regione_sociale = Ristorante::where("regione_sociale", $request->regione_sociale_nuova)->get();
                if ($regione_sociale->count() > 0) {
                    return response()->json([
                        "status" => 400,
                        "message" => "Il ristorante ha già questa regione sociale",
                    ], 400);
                } else {
                    $ristorante = Ristorante::where("regione_sociale", $request->regione_sociale)
                        ->where("indirizzo", $request->indirizzo)
                        ->update(["regione_sociale" => $request->regione_sociale_nuova]);
                    if ($ristorante == 0) {
                        return response()->json([
                            "status" => 400,
                            "message" => "Non è stato possibile aggiornare la regione sociale",
                        ], 400);
                    } else {
                        return response()->json([
                            "status" => 200,
                            "message" => "La regione sociale è stata aggiornata",
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @OA\Post(
     *     path="/api/restaurant/updateseating",
     *     summary="Update the number of seats in a restaurant",
     *     description="Update the number of seats in a restaurant identified by name and address",
     *     tags={"restaurant"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Restaurant details for update",
     *         @OA\JsonContent(
     *             required={"regione_sociale", "indirizzo", "numero_posti"},
     *             @OA\Property(property="regione_sociale", type="string", example="ristorante1"),
     *             @OA\Property(property="indirizzo", type="string", example="via roma 1"),
     *             @OA\Property(property="numero_posti", type="integer", example=50),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Il numero di posti è stato aggiornato"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Validation error message or Non è stato possibile aggiornare il numero di posti"),
     *         ),
     *     ),
     * )
     */

    public function updateNumeroPosti(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "regione_sociale" => "required | string | max:255",
            "indirizzo" => "required | string | max:255",
            "numero_posti" => "required | integer",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => $validator->errors()->first(),
            ], 400);
        } else {
            if ($request->numero_posti == null || $request->numero_posti == 0) {
                return response()->json([
                    "status" => 400,
                    "message" => "Numero posti non valido",
                ], 400);
            } else {
                $numero_posti = Ristorante::where("numero_posti", $request->numero_posti)->get();
                if ($numero_posti->count() > 0) {
                    return response()->json([
                        "status" => 400,
                        "message" => "Il ristorante ha già questo numero di posti",
                    ], 400);
                } else {
                    $ristorante = Ristorante::where("regione_sociale", $request->regione_sociale)
                        ->where("indirizzo", $request->indirizzo)
                        ->update(["numero_posti" => $request->numero_posti]);
                    if ($ristorante == 0) {
                        return response()->json([
                            "status" => 400,
                            "message" => "Non è stato possibile aggiornare il numero di posti",
                        ], 400);
                    } else {
                        return response()->json([
                            "status" => 200,
                            "message" => "Il numero di posti è stato aggiornato",
                        ]);
                    }
                }
            }
        }
    }
    /**
     * @OA\Put(
     *     path="/api/restaurant",
     *     summary="Update restaurant details",
     *     description="Update restaurant details, including name, address, cuisine type, and seating capacity",
     *     tags={"restaurant"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Restaurant details for update",
     *         @OA\JsonContent(
     *             required={"regione_sociale", "indirizzo", "indirizzo_nuovo", "regione_sociale_nuova", "tipo_cucina", "numero_posti"},
     *             @OA\Property(property="regione_sociale", type="string", example="ristorante1"),
     *             @OA\Property(property="indirizzo", type="string", example="via roma 1"),
     *             @OA\Property(property="indirizzo_nuovo", type="string", example="via nuova 2"),
     *             @OA\Property(property="regione_sociale_nuova", type="string", example="ristorante_modificato"),
     *             @OA\Property(property="tipo_cucina", type="string", example="italiana"),
     *             @OA\Property(property="numero_posti", type="integer", example=50),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Dati del ristorante aggiornati con successo"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Validation error message or Non è stato possibile aggiornare i dati del ristorante"),
     *         ),
     *     ),
     * )
     */
    public function updateRestaurant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'regione_sociale' => 'required|string|max:255',
            'indirizzo' => 'required|string|max:255',
            'indirizzo_nuovo' => 'required|string|max:255',
            'regione_sociale_nuova' => 'required|string|max:255',
            'tipo_cucina' => 'required|string|max:255',
            'numero_posti' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $ristorante = Ristorante::where('regione_sociale', $request->regione_sociale)
            ->where('indirizzo', $request->indirizzo)
            ->update([
                'indirizzo' => $request->indirizzo_nuovo,
                'regione_sociale' => $request->regione_sociale_nuova,
                'tipo_cucina' => $request->tipo_cucina,
                'numero_posti' => $request->numero_posti,
            ]);

        if ($ristorante !== false) {
            return response()->json([
                'status' => 200,
                'message' => 'Dati del ristorante aggiornati con successo',
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Non è stato possibile aggiornare i dati del ristorante',
            ], 400);
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/restaurant",
     *     summary="Delete restaurant",
     *     description="Delete a restaurant based on the name and address",
     *     tags={"restaurant"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Restaurant details for deletion",
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
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Ristorante eliminato con successo"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Validation error message or Non è stato possibile eliminare il ristorante"),
     *         ),
     *     ),
     * )
     */
    public function deleterestaurant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'regione_sociale' => 'required|string|max:255',
            'indirizzo' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $fasce = app()->make(FasceController::class);
        $id_ristorante = Ristorante::select('id')->where('regione_sociale', $request->regione_sociale)
            ->where('indirizzo', $request->indirizzo)->get();

        if ($id_ristorante !== false) {
            $fascia_json = $fasce->deleteSlot($id_ristorante[0]->id);
            $fascia = json_decode($fascia_json->content(), true);

            if ($fascia['eliminato'] === true) {
                $ristorante = Ristorante::where('regione_sociale', $request->regione_sociale)
                    ->where('indirizzo', $request->indirizzo)
                    ->delete();

                if ($ristorante !== false) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'Ristorante eliminato con successo',
                    ]);
                } else {
                    return response()->json([

                        'message' => 'Non è stato possibile eliminare il ristorante',
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'Non è stato possibile eliminare il ristorante',
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'Non è stato possibile eliminare il ristorante',
            ], 400);
        }
    }

    public function getrestaurantId(Request $request)
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
            $ristorid = Ristorante::where('regione_sociale', $request->regione_sociale)
                ->where('indirizzo', $request->indirizzo)
                ->get();
            if ($ristorid->count() == 0) {
                return response()->json([
                    "error" => "Non esiste questo ristorante"
                ], 404);
            } else {
                return response()->json([
                    "id_ristorante" => $ristorid[0]['id']
                ], 200);
            }
        }
    }
    public function showDetailsBook(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "regione_sociale" => "required | string | max:255",
            "indirizzo" => "required | string | max:255",
            "fascia_prenotazione" => "required | string | max:255",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => $validator->errors()->first(),
            ], 400);
        } else {
            $fasce = app()->make(FasceController::class);
            $fascia_prenotazione = explode('/', $request->fascia_prenotazione);
            $ristorante = Ristorante::select('id', 'regione_sociale', 'indirizzo', 'tipo_cucina', 'numero_posti')
                //->selectRaw('numero_posti - posti_prenotati AS posti_disponibili')
                ->where('regione_sociale', Str::lower($request->regione_sociale))
                ->where('indirizzo', Str::lower($request->indirizzo))
                ->get();
            if ($ristorante->count() == 0) {
                return response()->json([
                    "status" => 404,
                    "message" => "nessun ristorante trovato"
                ], 404);
            } else {
                $posti_disponibiliJSON = $fasce->getAvailableSeats($ristorante[0]["id"], $fascia_prenotazione[0], $fascia_prenotazione[1]);
                $posti_disponibili = json_decode($posti_disponibiliJSON->content(), true);
                return response()->json([
                    "status" => 200,
                    "ristorante" => $ristorante,
                    "posti_disponibili" => $posti_disponibili["posti_disponibili"][0]['posti_disponibili'],
                    "message" => "nessuna prenotazione effettuata"
                ], 200);
            }
        }
    }
    public function getrestaurantById($id)
    {
        $ristorante = Ristorante::select('regione_sociale', 'indirizzo', 'tipo_cucina', 'numero_posti')->where('id', $id)->get();
        if ($ristorante->count() > 0) {
            return response()->json([
                'ristorante' => $ristorante
            ], 200);
        } else {
            return response()->json([
                'message' => 'Fascia non trovata'
            ], 404);
        }
    }
}
