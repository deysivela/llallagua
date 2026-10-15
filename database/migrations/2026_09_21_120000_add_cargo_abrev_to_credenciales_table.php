<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCargoAbrevToCredencialesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credenciales', function (Blueprint $table) {
            // Abreviatura del cargo usada solo en la credencial impresa.
            $table->string('cargo_abrev', 100)->nullable()->after('hash');
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
            $table->dropColumn('cargo_abrev');
        });
    }
}
