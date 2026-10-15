<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFacebookAndVideoToNewpagesTable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE newpages MODIFY contenido MEDIUMTEXT NULL');

        Schema::table('newpages', function (Blueprint $table) {
            $table->string('facebook_url', 500)->nullable()->after('contenido');
            $table->string('video_url', 500)->nullable()->after('facebook_url');
        });
    }

    public function down()
    {
        Schema::table('newpages', function (Blueprint $table) {
            $table->dropColumn(['facebook_url', 'video_url']);
        });
        DB::statement('ALTER TABLE newpages MODIFY contenido MEDIUMTEXT NOT NULL');
    }
}
