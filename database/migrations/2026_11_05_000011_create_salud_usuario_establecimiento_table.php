<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludUsuarioEstablecimientoTable extends Migration
{
    public function up()
    {
        Schema::create('salud_usuario_establecimiento', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('establecimiento_id');

            $table->primary(['user_id', 'establecimiento_id']);

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
            $table->foreign('establecimiento_id')
                  ->references('id')->on('salud_establecimientos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_usuario_establecimiento');
    }
}
