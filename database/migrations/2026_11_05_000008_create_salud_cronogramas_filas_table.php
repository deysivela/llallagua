<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludCronogramasFilasTable extends Migration
{
    public function up()
    {
        Schema::create('salud_cronogramas_filas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('establecimiento_id');
            $table->string('dia_o_fecha')->nullable()->comment('Ej: "Lunes a viernes" o "2026-11-15"');
            $table->string('servicio')->nullable();
            $table->string('profesional')->nullable();
            $table->string('horario')->nullable();
            $table->string('observaciones')->nullable();
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();

            $table->foreign('establecimiento_id')
                  ->references('id')->on('salud_establecimientos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_cronogramas_filas');
    }
}
