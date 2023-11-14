<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ristorante', function (Blueprint $table) {
            $table->id();
            $table->string('regione_sociale')->nullable(false)->unique();
            $table->string('indirizzo')->nullable(false)->unique();
            $table->string('tipo_cucina')->nullable(false);
            $table->integer('numero_posti')->nullable(false);
            $table->string('fascia_prenotazioni')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ristorante');
    }
};
