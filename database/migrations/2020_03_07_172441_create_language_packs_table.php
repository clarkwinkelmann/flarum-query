<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagePacksTable extends Migration
{
    public function up()
    {
        Schema::connection('query-write')->create('language_packs', function (Blueprint $table) {
            $table->string('package')->index();
            $table->string('version')->index();
            $table->string('locale')->index();
            $table->string('title')->index()->nullable();
        });
    }

    public function down()
    {
        Schema::connection('query-write')->dropIfExists('language_packs');
    }
}
