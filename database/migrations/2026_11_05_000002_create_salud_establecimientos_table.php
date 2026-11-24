<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludEstablecimientosTable extends Migration
{
    public function up()
    {
        Schema::create('salud_establecimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('nombre');
            $table->enum('tipo', ['hospital', 'centro_salud', 'rehabilitacion']);
            $table->string('nivel')->nullable()->comment('Ej: 2.º nivel, 1.er nivel');
            $table->string('zona')->nullable();
            $table->string('direccion')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->string('responsable')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('atiende_24h')->default(false);
            $table->unsignedInteger('foto_fachada_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('foto_fachada_id')
                  ->references('id')->on('salud_archivos')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_establecimientos');
    }
}
