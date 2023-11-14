<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fasce extends Model
{
    use HasFactory;

    protected $table = 'fasce';

    protected $fillable = [
        'inizio',
        'fine',
        'posti_disponibili',
        'id_ristorante',
    ];
}
