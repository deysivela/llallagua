<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCargoCredencialToCredencialesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credenciales', function (Blueprint $table) {
            $table->string('cargo_credencial', 255)->nullable()->after('foto_credencial');
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
            $table->dropColumn('cargo_credencial');
        });
    }
}