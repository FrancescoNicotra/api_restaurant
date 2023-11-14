<?php

namespace App\Http\Controllers;

use App\Models\Utente;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UtenteController extends Controller
{
    public function index()
    {
        $utenti = Utente::all();
        if ($utenti->count() > 0) {
            return response()->json([
                'status' => 200,
                'utenti' => $utenti
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'nessun utente'
            ], 404);
        }
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required | string | max:255',
            'cognome' => 'required | string | max:255',
            'numero_telefono' => 'required | string | max:255'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->messages()
            ], 400);
        } else {
            $existJSON = $this->checkPhoneNumber($request->numero_telefono, $request->nome, $request->cognome);
            $exist = json_decode($existJSON->content(), true);

            if ($exist['phone_number_exist']) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Questo numero appartiene ad un\'altra persona'
                ], 400);
            } else {
                $encryptedPhoneNumber = Hash::make($request->numero_telefono);
                $prenotati = Utente::create([
                    'nome' => Str::lower($request->nome),
                    'cognome' => Str::lower($request->cognome),
                    'numero_telefono' => $encryptedPhoneNumber,
                ]);
                if ($prenotati->count() > 0) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'prenotato con successo'
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 500,
                        'message' => 'errore nel prenotare'
                    ], 404);
                }
            }
        }
    }
    public function getUserId($user_id)
    {
        $user = Utente::find($user_id);
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'utente non trovato'
            ], 404);
        } else {
            return response()->json([
                'status' => 200,
                'user' => $user
            ], 200);
        }
    }
    protected function checkPhoneNumber($phone_number, $nome, $cognome)
    {
        $phone_numbers = Utente::select("numero_telefono")
            ->where("nome", Str::lower($nome))
            ->where('cognome', Str::lower($cognome))->get();
        if ($phone_numbers->count() == 0) {
            return response()->json([
                'phone_number_exist' => false,
                'utente' => "utente non trovato",
            ], 404);
        } else {
            foreach ($phone_numbers as $numero) {
                $correct = Hash::check($phone_number, $numero->numero_telefono);
                if ($correct) {
                    return response()->json([
                        'phone_number_exist' => true,
                        'phone_number' => $numero->numero_telefono,
                    ], 200);
                } else {
                    return response()->json([
                        'phone_number_exist' => false,
                    ], 200);
                }
            }
        }

    }
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'cognome' => 'required|string|max:255',
            'numero_telefono' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->messages()
            ], 400);
        } else {
            $correctJSON = $this->checkPhoneNumber($request->numero_telefono, $request->nome, $request->cognome);
            $correct = json_decode($correctJSON->content(), true);
            if ($correct['phone_number_exist']) {
                $request->numero_telefono = $correct['phone_number'];
                $utente = Utente::select('id', 'nome', 'cognome')
                    ->where('numero_telefono', $request->numero_telefono)->get();
                if ($utente->count() > 0) {
                    return response()->json([
                        'status' => 200,
                        'utente' => $utente
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 400,
                        'message' => 'nessun utente è stato trovato con questo numero di telefono/cognome'
                    ], 400);


                }
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'nessun utente è stato trovato con questo numero di telefono/cognome'
                ], 400);

            }
        }
    }

    public function getUtenteById($id)
    {
        $utente = Utente::select('id', 'nome', 'cognome', 'numero_chiaro')->where('id', $id)->get();
        if ($utente->count() > 0) {
            return response()->json([
                'utente' => $utente
            ], 200);
        } else {
            return response()->json([
                'message' => 'Fascia non trovata'
            ], 404);
        }
    }

}
