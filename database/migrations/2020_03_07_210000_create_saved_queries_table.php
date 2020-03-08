<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavedQueriesTable extends Migration
{
    public function up()
    {
        Schema::create('saved_queries', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('title');
            $table->text('sql');
            $table->json('preview')->nullable();
            $table->boolean('public')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('saved_queries');
    }
}
