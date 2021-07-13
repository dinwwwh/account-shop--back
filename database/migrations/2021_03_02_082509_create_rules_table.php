<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('datatype');
            $table->string('placeholder')->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('multiple')->default(false);
            $table->integer('min')->nullable();
            $table->integer('max')->nullable();
            $table->json('values')->nullable();

            $table->unsignedBigInteger('latest_updater_id')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
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
        Schema::dropIfExists('rules');
    }
}
