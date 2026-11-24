<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludHorariosTable extends Migration
{
    public function up()
    {
        Schema::create('salud_horarios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('establecimiento_id');
            // dias: JSON array de ints 1=lun … 7=dom
            $table->json('dias');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            // Si hora_fin < hora_inicio, la franja cruza la medianoche
            $table->string('nota')->nullable();
            $table->timestamps();

            $table->foreign('establecimiento_id')
                  ->references('id')->on('salud_establecimientos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_horarios');
    }
}
