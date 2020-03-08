<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtensionsTable extends Migration
{
    public function up()
    {
        Schema::connection('query-write')->create('extensions', function (Blueprint $table) {
            $table->string('package')->index();
            $table->string('flarumid')->index();
            $table->string('repository')->index()->nullable();
            $table->string('abandoned')->index()->nullable();
            $table->unsignedInteger('github_stars')->index()->nullable();
            $table->unsignedInteger('github_watchers')->index()->nullable();
            $table->unsignedInteger('github_forks')->index()->nullable();
            $table->unsignedInteger('github_open_issues')->index()->nullable();
            $table->unsignedInteger('downloads_total')->index()->nullable();
            $table->unsignedInteger('downloads_monthly')->index()->nullable();
            $table->unsignedInteger('downloads_daily')->index()->nullable();
        });
    }

    public function down()
    {
        Schema::connection('query-write')->dropIfExists('extensions');
    }
}
