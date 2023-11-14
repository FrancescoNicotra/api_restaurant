<?php

namespace App\Http\Controllers;

use App\Models\Ristorante;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\PrenotazioneController;
use App\Http\Controllers\FasceController;


class RistoranteController extends Controller
{
    //mostra tutti i ristoranti
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


    //crea un nuovo ristorante
    public function NewResturant(Request $request)
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
            $fasce->addNewSlot($request);
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
        }
    }
    //mostra tutti i ristoranti con un determinato nome
    public function showResturantsByName($regione_sociale)
    {
        $ristorante = Ristorante::where('regione_sociale', $regione_sociale)->get();
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
                $fasce_ristorante_json = $fasce->getAllSlotOfResturant($id_ristorante[0]['id']);
                $fasce_ristorante = json_decode($fasce_ristorante_json->content(), true);

                $fasce_orarie = $fasce_ristorante['fasce_orarie'];

                //$fascia_prenotazione = explode('/', $request->fascia_prenotazione);
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
    public function updateResturant(Request $request)
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

    public function deleteResturant(Request $request)
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
    }

    public function getResturantId(Request $request)
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
    { {
            $prenotazioni = app()->make(PrenotazioneController::class);
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

    }
    public function getResturantById($id)
    {
        $ristorante = Ristorante::select('regione_sociale', 'indirizzo', 'tipo_cucina')->where('id', $id)->get();
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
