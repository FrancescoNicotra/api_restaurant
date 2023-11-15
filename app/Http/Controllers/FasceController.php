<?php

namespace App\Http\Controllers;

use App\Models\Fasce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\RistoranteController;


class FasceController extends Controller
{

    public function index()
    {
        $response = Fasce::all();
        if ($response->count() == 0) {
            return response()->json([
                'message' => 'Non ci sono fasce'
            ], 404);
        } else {
            return response()->json([
                'status' => 200,
                'message' => Fasce::all()
            ], 200);
        }
    }

    public function getAllSlotOfRestaurant($id_ristorante)
    {
        $data = Fasce::select('inizio', 'fine', 'id_ristorante')->where('id_ristorante', $id_ristorante)->get();
        if ($data->count() == 0) {
            return response()->json([
                'message' => 'Non ci sono fasce per questo ristorante'
            ], 404);
        } else {
            return response()->json([
                'fasce_orarie' => $data
            ]);
        }
    }


    public function addNewSlot($id_ristorante, $inizio, $fine, $numero_posti)
    {


        if (!$id_ristorante) {
            return response()->json([
                "error" => "Non esiste questo ristorante"
            ], 404);
        } else {

            $data = Fasce::where("id_ristorante", $id_ristorante)->where("inizio", $inizio)->where("fine", $fine)->get();
            if ($data->count() > 0) {
                return response()->json([
                    "error" => "Fascia già presente per il ristorante"
                ], 404);
            } else {

                $exist_slot = Fasce::select('inizio', 'fine', 'id_ristorante')->where('id_ristorante', $id_ristorante)
                    ->where('inizio', $inizio)->where('fine', $fine)->get();
                if ($exist_slot->count() > 0) {
                    return response()->json([
                        'message' => 'fascia già presente per questo ristorante'
                    ], 404);
                } else {
                    $fasce = Fasce::create([
                        'inizio' => $inizio,
                        'fine' => $fine,
                        'id_ristorante' => $id_ristorante,
                        'posti_disponibili' => $numero_posti,
                    ]);
                    return response()->json([
                        'status' => 200,
                        'message' => 'fascia aggiunta con successo',
                        'fasce' => $fasce
                    ], 200);
                }

            }
        }

    }
    public function getAvailableSeats($id_ristorante, $inizio, $fine)
    {
        $posti_disponibili = Fasce::select('posti_disponibili')->where('id_ristorante', $id_ristorante)->where('inizio', $inizio)->where('fine', $fine)->get();
        return response()->json([
            'posti_disponibili' => $posti_disponibili
        ], 200);


    }
    public function subAvailableSeats($id_ristorante, $inizio, $fine, $numero_posti)
    {
        $fascia = Fasce::where('id_ristorante', $id_ristorante)
            ->where('inizio', $inizio)
            ->where('fine', $fine)
            ->first();
        if ($fascia) {
            $posti_disponibili_attuali = $fascia->posti_disponibili;
            $nuovi_posti_disponibili = $posti_disponibili_attuali - $numero_posti;
            $fascia->update(['posti_disponibili' => $nuovi_posti_disponibili]);
            return response()->json([
                'id_fascia' => $fascia->id,
                'posti_disponibili' => $nuovi_posti_disponibili
            ], 200);
        } else {
            return response()->json([
                'message' => 'Fascia non trovata'
            ], 404);
        }
    }
    public function addAvailableSeats($id_ristorante, $inizio, $fine, $numero_posti)
    {
        $fascia = Fasce::where('id_ristorante', $id_ristorante)
            ->where('inizio', $inizio)
            ->where('fine', $fine)
            ->first();
        if ($fascia) {
            $posti_disponibili_attuali = $fascia->posti_disponibili;
            $nuovi_posti_disponibili = $posti_disponibili_attuali + $numero_posti;
            $fascia->update(['posti_disponibili' => $nuovi_posti_disponibili]);
            return response()->json([
                'id_fascia' => $fascia->id,
                'posti_disponibili' => $nuovi_posti_disponibili
            ], 200);
        } else {
            return response()->json([
                'message' => 'Fascia non trovata'
            ], 404);
        }
    }

    public function getSlot($id_ristorante, $id)
    {
        $fascia = Fasce::select('inizio', 'fine')->where('id_ristorante', $id_ristorante)->where('id', $id)->get();
        if ($fascia) {
            return response()->json([
                'fascia' => $fascia
            ], 200);
        } else {
            return response()->json([
                'message' => 'Fascia non trovata'
            ], 404);
        }
    }
    public function getInfoFasciaById($id)
    {
        $fascia = Fasce::select('inizio', 'fine', 'posti_disponibili')->where('id', $id)->get();
        if ($fascia->count() > 0) {
            return response()->json([
                'fascia' => $fascia
            ], 200);
        } else {
            return response()->json([
                'message' => 'Fascia non trovata'
            ], 404);
        }
    }

    public function deleteSlot($id_ristorante)
    {
        $ristorante = Fasce::where('id_ristorante', $id_ristorante)->delete();
        if ($ristorante) {
            return response()->json([
                'eliminato' => true,
                'message' => 'Fasce cancellate con successo'
            ], 200);
        } else {
            return response()->json([
                'eliminato' => false,
                'message' => 'Fasce non trovate'
            ], 404);
        }
    }
}
