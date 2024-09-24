<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clasificacions', function (Blueprint $table) {
            $table->id();
            $table->enum('emocion_audio', ['Asco','Felicidad','Ira','Miedo','Neutralidad','Tristeza']);
            $table->enum('emocion_foto', ['Asco','Felicidad','Ira','Miedo','Neutralidad','Tristeza']);
            $table->enum('emocion_audio_foto', ['Asco','Felicidad','Ira','Miedo','Neutralidad','Tristeza']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clasificacions');
    }
};
