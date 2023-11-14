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
        Schema::table('ristorante', function (Blueprint $table) {
            $table->dropColumn('nome');
            $table->string('regione_sociale')->nullable(false)->change();
            $table->string('indirizzo')->nullable(false)->change();
            $table->string('tipo_cucina')->nullable(false)->change();
            ;
            $table->integer('numero_posti')->nullable(false)->change();
            ;
            $table->string('fascia_prenotazioni')->nullable(false)->change();
            ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ristorante', function (Blueprint $table) {
            $table->dropColumn('nome');
        });
    }
};
