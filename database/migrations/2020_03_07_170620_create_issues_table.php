<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssuesTable extends Migration
{
    public function up()
    {
        Schema::connection('query-write')->create('issues', function (Blueprint $table) {
            $table->string('package')->index();
            $table->string('version')->index();
            $table->string('file')->index();
            $table->text('description');
        });
    }

    public function down()
    {
        Schema::connection('query-write')->dropIfExists('issues');
    }
}
