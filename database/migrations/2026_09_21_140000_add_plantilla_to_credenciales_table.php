<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlantillaToCredencialesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('credenciales', 'plantilla')) {
            Schema::table('credenciales', function (Blueprint $table) {
                $table->dropColumn('plantilla');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credenciales', function (Blueprint $table) {
            $table->string('plantilla', 20)->nullable()->after('foto_credencial');
        });
    }
}