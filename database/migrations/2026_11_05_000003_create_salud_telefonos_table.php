<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludTelefonosTable extends Migration
{
    public function up()
    {
        Schema::create('salud_telefonos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('establecimiento_id');
            $table->string('numero', 30);
            $table->enum('etiqueta', ['fijo', 'celular', 'whatsapp'])->default('fijo');
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();

            $table->foreign('establecimiento_id')
                  ->references('id')->on('salud_establecimientos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_telefonos');
    }
}
