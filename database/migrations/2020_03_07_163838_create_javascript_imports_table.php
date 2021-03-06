<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJavascriptImportsTable extends Migration
{
    public function up()
    {
        Schema::connection('query-write')->create('javascript_imports', function (Blueprint $table) {
            $table->string('package')->index();
            $table->string('version')->index();
            $table->string('file')->index();
            $table->string('class')->index();
        });
    }

    public function down()
    {
        Schema::connection('query-write')->dropIfExists('javascript_imports');
    }
}
