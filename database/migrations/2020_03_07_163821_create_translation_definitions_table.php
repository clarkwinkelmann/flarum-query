<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationDefinitionsTable extends Migration
{
    public function up()
    {
        Schema::connection('query-write')->create('translation_definitions', function (Blueprint $table) {
            $table->string('package')->index();
            $table->string('version')->index();
            $table->string('file')->index();
            $table->string('locale')->index();
            $table->string('key')->index();
            $table->text('value')->nullable();
        });
    }

    public function down()
    {
        Schema::connection('query-write')->dropIfExists('translation_definitions');
    }
}
