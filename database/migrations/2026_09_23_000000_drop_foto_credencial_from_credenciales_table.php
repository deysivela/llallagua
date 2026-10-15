<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropFotoCredencialFromCredencialesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE credenciales DROP COLUMN cargo_credencial');
        DB::statement('ALTER TABLE credenciales DROP COLUMN foto_credencial');
        DB::statement('ALTER TABLE credenciales DROP COLUMN nombre_credencial');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE credenciales ADD nombre_credencial VARCHAR(255) DEFAULT NULL AFTER hash');
        DB::statement('ALTER TABLE credenciales ADD foto_credencial VARCHAR(255) DEFAULT NULL AFTER nombre_credencial');
        DB::statement('ALTER TABLE credenciales ADD cargo_credencial VARCHAR(255) DEFAULT NULL AFTER hash');
    }
}