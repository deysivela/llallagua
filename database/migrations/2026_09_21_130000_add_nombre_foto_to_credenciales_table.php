<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNombreFotoToCredencialesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credenciales', function (Blueprint $table) {
            // Nombre y foto personalizados usados solo en la credencial impresa.
            $table->string('nombre_credencial', 255)->nullable()->after('cargo_abrev');
            $table->string('foto_credencial', 255)->nullable()->after('nombre_credencial');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credenciales', function (Blueprint $table) {
            $table->dropColumn(['nombre_credencial', 'foto_credencial']);
        });
    }
}