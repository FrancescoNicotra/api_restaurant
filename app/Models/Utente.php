<?php

namespace App\Models;

use Illuminate\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Utente extends Model
{
    use HasFactory;

    protected $table = 'utente';


    protected $fillable = [
        'nome',
        'cognome',
        'numero_telefono',
    ];
}
