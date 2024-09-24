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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',55);
            $table->string('apellido',55);
            $table->char('ci',11);
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['Femenino','Masculino']);
            $table->string('direccion');
            $table->char('telefono',8);
            $table->string('correo');
            $table->string('usuario');
            $table->string('contrasena');
            $table->timestamps();

            //Constrains
            $table->unique('ci');
            $table->unique('correo');
            $table->unique('usuario');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
