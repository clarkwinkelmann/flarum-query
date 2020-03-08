<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueriesTable extends Migration
{
    public function up()
    {
        Schema::create('queries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saved_query_id')->nullable();
            $table->string('uid')->unique();
            $table->text('sql');
            $table->text('error')->nullable();
            $table->unsignedInteger('rows_count')->nullable();
            $table->json('preview')->nullable();
            $table->timestamps();

            $table->foreign('saved_query_id')->references('id')->on('saved_queries')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('queries');
    }
}
