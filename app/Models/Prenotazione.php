<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prenotazione extends Model
{
    use HasFactory;

    protected $table = 'prenotazione';

    protected $fillable = [
        'id_utente',
        'id_ristorante',
        'id_fascia',
        'data_prenotazione',
        'numero_persone',
    ];

}
