<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCredencialesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credenciales', function (Blueprint $table) {
            $table->increments('id');
            // ID del empleado para enlazar a la tabla employees
            $table->integer('employee_id')->unsigned(); 
            
            // Campos de la credencial
            $table->timestamp('fecha')->useCurrent();
            $table->string('hash', 128)->nullable();
            
            $table->timestamps();

            // Lógica de relación (enlace)
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credenciales');
    }
}
