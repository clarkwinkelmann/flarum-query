<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationUsagesTable extends Migration
{
    public function up()
    {
        Schema::connection('query-write')->create('translation_usages', function (Blueprint $table) {
            $table->string('package')->index();
            $table->string('version')->index();
            $table->string('file')->index();
            $table->string('key')->index();
        });
    }

    public function down()
    {
        Schema::connection('query-write')->dropIfExists('translation_usages');
    }
}
