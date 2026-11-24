<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludSeccionesDetalleTable extends Migration
{
    public function up()
    {
        Schema::create('salud_secciones_detalle', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('establecimiento_id');
            $table->string('titulo');
            $table->text('texto')->nullable();
            $table->json('items')->nullable()->comment('Lista de ítems como array JSON');
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();

            $table->foreign('establecimiento_id')
                  ->references('id')->on('salud_establecimientos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_secciones_detalle');
    }
}
