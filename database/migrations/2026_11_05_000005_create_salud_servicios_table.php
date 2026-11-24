<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaludServiciosTable extends Migration
{
    public function up()
    {
        Schema::create('salud_servicios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('establecimiento_id');
            $table->string('nombre');
            $table->enum('categoria', [
                'medicina_general', 'odontologia', 'farmacia', 'vacunacion',
                'fisioterapia', 'laboratorio', 'especialidades', 'emergencias',
                'programas', 'fonoaudiologia', 'trabajo_social', 'enfermeria',
                'imagenologia', 'telesalud', 'otro'
            ])->default('otro');
            $table->text('descripcion')->nullable();
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();

            $table->foreign('establecimiento_id')
                  ->references('id')->on('salud_establecimientos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud_servicios');
    }
}
