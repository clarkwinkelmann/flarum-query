<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReleasesTable extends Migration
{
    public function up()
    {
        Schema::connection('query-write')->create('releases', function (Blueprint $table) {
            $table->string('package')->index();
            $table->string('version')->index();
            $table->string('license')->index()->nullable();
            $table->string('title')->index()->nullable();
            $table->string('description')->index()->nullable();
            $table->string('icon_name')->index()->nullable();
            $table->string('icon_image')->index()->nullable();
            $table->string('icon_color')->index()->nullable();
            $table->string('icon_background')->index()->nullable();
            $table->string('discuss')->index()->nullable();
            $table->timestamp('date')->index();
        });
    }

    public function down()
    {
        Schema::connection('query-write')->dropIfExists('releases');
    }
}
