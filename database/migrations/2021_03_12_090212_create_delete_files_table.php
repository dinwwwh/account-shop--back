<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeleteFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delete_files', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('model_name')->nullable();
            $table->string('model_key')->nullable();
            $table->text('errors')->nullable();
            $table->json('successes')->nullable();
            $table->timestamp('delete_file_at');
            $table->timestamp('deleted_file_at')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->unsignedBigInteger('latest_updater_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delete_files');
    }
}
