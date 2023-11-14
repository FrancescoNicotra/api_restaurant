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
        Schema::create('fasce', function (Blueprint $table) {
            $table->id();
            $table->time('inizio')->nullable(false);
            $table->time('fine')->nullable(false);
            $table->integer('posti_disponibili')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fasce');
    }
};
