<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludArchivosTable extends Migration
{
    public function up()
    {
        Schema::create('salud_archivos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('clave')->unique()->comment('Ruta relativa en disco public');
            $table->string('clave_thumb')->nullable()->comment('Ruta miniatura');
            $table->string('clave_opt')->nullable()->comment('Ruta versión optimizada max 1600px');
            $table->string('nombre_original', 512);
            $table->string('mime', 100);
            $table->unsignedBigInteger('tamano')->comment('Bytes');
            $table->unsignedSmallInteger('ancho')->nullable();
            $table->unsignedSmallInteger('alto')->nullable();
            $table->unsignedInteger('subido_por')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_archivos');
    }
}
