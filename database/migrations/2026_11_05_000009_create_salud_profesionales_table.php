<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludProfesionalesTable extends Migration
{
    public function up()
    {
        Schema::create('salud_profesionales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('establecimiento_id');
            $table->unsignedInteger('servicio_id')->nullable();
            $table->string('nombre');
            $table->string('especialidad')->nullable();
            $table->date('fecha')->nullable();
            $table->enum('franja', ['08:00-20:00', '20:00-08:00', '08:00-08:00', 'otro'])->nullable();
            $table->string('franja_texto')->nullable()->comment('Texto libre si franja=otro');
            $table->timestamps();

            $table->foreign('establecimiento_id')
                  ->references('id')->on('salud_establecimientos')
                  ->onDelete('cascade');
            $table->foreign('servicio_id')
                  ->references('id')->on('salud_servicios')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_profesionales');
    }
}
