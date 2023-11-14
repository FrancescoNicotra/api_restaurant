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
        Schema::table('prenotazione', function (Blueprint $table) {
            Schema::create('prenotazione', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_utente')->constrained('utente'); // foreign key
                $table->foreignId('id_ristorante')->constrained('ristorante'); // foreign key
                $table->foreignId('id_fascia')->constrained('fasce');
                $table->date('data_prenotazione');
                $table->integer('numero_persone');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prenotazione', function (Blueprint $table) {
            //
        });
    }
};
