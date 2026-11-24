<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludCronogramasMensualesTable extends Migration
{
    public function up()
    {
        Schema::create('salud_cronogramas_mensuales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('establecimiento_id');
            $table->unsignedInteger('servicio_id')->nullable();
            $table->date('mes')->comment('Primer día del mes (YYYY-MM-01)');
            $table->unsignedInteger('archivo_id');
            $table->string('titulo')->nullable();
            $table->text('nota')->nullable();
            $table->boolean('publicado')->default(false);
            $table->unsignedInteger('subido_por')->nullable();
            $table->boolean('eliminado')->default(false)->comment('Borrado lógico');
            $table->timestamps();

            $table->foreign('establecimiento_id')
                  ->references('id')->on('salud_establecimientos')
                  ->onDelete('cascade');
            $table->foreign('servicio_id')
                  ->references('id')->on('salud_servicios')
                  ->onDelete('set null');
            $table->foreign('archivo_id')
                  ->references('id')->on('salud_archivos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_cronogramas_mensuales');
    }
}
