<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTipoToSaludCronogramasMensualesTable extends Migration
{
    public function up()
    {
        Schema::table('salud_cronogramas_mensuales', function (Blueprint $table) {
            if (!Schema::hasColumn('salud_cronogramas_mensuales', 'tipo')) {
                $table->string('tipo', 50)->default('cronograma_atencion')->after('servicio_id');
            }
            if (!Schema::hasColumn('salud_cronogramas_mensuales', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable()->after('mes');
            }
        });

        DB::statement('ALTER TABLE salud_cronogramas_mensuales MODIFY archivo_id INT UNSIGNED NULL');
    }

    public function down()
    {
        Schema::table('salud_cronogramas_mensuales', function (Blueprint $table) {
            if (Schema::hasColumn('salud_cronogramas_mensuales', 'tipo')) {
                $table->dropColumn('tipo');
            }
        });
    }
}

