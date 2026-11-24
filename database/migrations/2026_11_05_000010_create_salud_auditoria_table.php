<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludAuditoriaTable extends Migration
{
    public function up()
    {
        Schema::create('salud_auditoria', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id')->nullable()->index();
            $table->string('accion', 30)->comment('crear|editar|eliminar|publicar');
            $table->string('entidad', 60)->comment('establecimiento|cronograma|archivo|etc');
            $table->unsignedInteger('entidad_id')->nullable();
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->timestamp('fecha')->useCurrent();

            $table->index(['entidad', 'entidad_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_auditoria');
    }
}
