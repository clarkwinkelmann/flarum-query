<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRouteDefinitionsTable extends Migration
{
    public function up()
    {
        Schema::connection('query-write')->create('route_definitions', function (Blueprint $table) {
            $table->string('package')->index();
            $table->string('version')->index();
            $table->string('file')->index();
            $table->string('method')->index();
            $table->string('path')->index();
        });
    }

    public function down()
    {
        Schema::connection('query-write')->dropIfExists('route_definitions');
    }
}
