<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ristorante extends Model
{
    use HasFactory;

    protected $table = 'ristorante';

    protected $fillable = [
        'nome',
        'regione_sociale',
        'indirizzo',
        'tipo_cucina',
        'numero_posti',
    ];
}
