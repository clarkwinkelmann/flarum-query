<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComposerRequirementsTable extends Migration
{
    public function up()
    {
        Schema::connection('query-write')->create('composer_requirements', function (Blueprint $table) {
            $table->string('package')->index();
            $table->string('version')->index();
            $table->string('other_package')->index();
            $table->string('constraint')->index();
        });
    }

    public function down()
    {
        Schema::connection('query-write')->dropIfExists('composer_requirements');
    }
}
