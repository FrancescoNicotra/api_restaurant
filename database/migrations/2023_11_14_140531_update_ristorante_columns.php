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
            $table->dropColumn('posti_prenotati');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ristorante', function (Blueprint $table) {
            $table->int('posti_prenotati');
        });
    }
};
